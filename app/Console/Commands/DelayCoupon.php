<?php

/**
 * Created by netbeanIDE.
 * User: limingcun
 * Date: 2018/6/14
 * Time: 下午16:39
 * desc: 延迟给用户发券
 */
namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\CouponGrand;
use App\Models\CouponLibrary;
use App\Models\User;
use App\Models\Member;
use App\Models\Coupon;
use Illuminate\Console\Command;
use Redis;
use Log;
use DB;
use IQuery;
use App\Models\Message;

class DelayCoupon extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delay:coupon';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '延迟给用户发优惠券';

    protected $path = 'laravel:coupon:';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $res = CouponGrand::with(['coupon'])
                ->where('status', CouponGrand::GRANDSTATUS['granding'])
                ->where('page', '!=', 0)->get();
        if($res->isEmpty()) {
            return;
        }
        $coupon_library = new CouponLibrary;
        foreach($res as $r) {
            $coupon = $r->coupon;
            DB::beginTransaction();
            try {
                if ($r->scence) {
                    //线下延迟发券
                    $this->underLine($r, $coupon_library);
                } else {
                    //线上延迟发券
                    switch($r->range_type) {
                        case CouponGrand::RANGETYPE['all']:
                            request()->merge(['page' => $r->page]);
                            $arrUser = User::select('id')->paginate(1000);
                            $pageUser = $arrUser->lastPage();
                            $users = $this->getUserId($arrUser);
                        break;
                    }
                    $couponArr = [];
                    $user_tabs = $users;  //用户获取多少张（数组拼接）
                    for($i = 1;$i<$r->count;$i++) {
                        $users = array_merge_recursive($users, $user_tabs);
                    }
                    $code_id = IQuery::redisGet($this->path.'code'.$r->id);
                    foreach($users as $k=>$user) {
                        $couponArr = $this->showArr($r, $couponArr, $code_id, $coupon, $user);
                        $code_id++;
                        // 大数据分片执行(每1000条执行一次)
                        if (($k+1)%1000 == 0) {
                            $coupon_library->maxInsert($couponArr);
                            $couponArr = [];
                        }
                    }
                    if (count($couponArr) > 0) {
                        $coupon_library->maxInsert($couponArr);
                    }
                    if ($pageUser > $r->page) {
                        $r->page += 1;
                        IQuery::redisSet($this->path.'code'.$r->id, $code_id);  //将递增的code_id存进redis
                    } else {
                        $r->page = 0;
                        $r->status = CouponGrand::GRANDSTATUS['finish'];
                        if (Redis::exists($this->path.'code'.$r->id)) {
                            Redis::del($this->path.'code'.$r->id);
                        }
                    }
//                    Member::whereIn('user_id', $users)->update(['new_coupon_tab' => Member::NEWTAB['new']]);
                    Member::whereIn('user_id', array_unique($users))->chunk(200, function($members) {
                        foreach($members as $member) {
                            Message::couponsGetMsg($member->user_id);
                            $member->update(['new_coupon_tab' => Member::NEWTAB['new'], 'message_tab' => $member->message_tab + 1]);
                        }
                    });
                    $r->save();
                    if (count($users) > 0) {
                        Coupon::where('id', $coupon->id)->update(['count' => bcsub($coupon->count, count($users))]);  //更新喜茶券模板标志
                    }
                }
                DB::commit();
                Log::info('delay:coupon_success', ['success']);
            }
            catch (\Exception $exception) {
                DB::rollback();
                Log::info('delay:coupon_error', [$exception]);
                throw new \Exception($exception);
            }
        }
    }

    /*
     * 获取用户id
     */
    public function getUserId($users) {
        $userArr = [];
        foreach($users as $user) {
            $userArr[] = $user->id;
        }
        return $userArr;
    }

    /*
     * 线下发券
     */
    public function underLine($r, $obj) {
        $coupon = $r->coupon;
        $lineNumber = $r->scence == 1 ? $r->amount : bcmul($r->amount, $r->count); //线下（兑换或二维码）喜茶券数量
        $amount = $lineNumber - ($r->page - 1) * 1000;  //默认分发10000张券
        if ($amount > 1000) {
            $pageLage = floor($amount / 1000) + 1;
            $amount = 1000;
        } else {
            $pageLage = 1;
        }
        $couponArr = [];
        $code_id = IQuery::redisGet($this->path.'code'.$r->id);
        for($i = 1; $i <= $amount; $i++) {
            $couponArr = $this->showArr($r, $couponArr, $code_id, $coupon);
            $code_id++;
            // 大数据分片执行(每1000条执行一次)
            if (($i)%1000 == 0) {
                $obj->maxInsert($couponArr);
                $couponArr = [];
            }
        }
        if (count($couponArr) > 0) {
            $obj->maxInsert($couponArr);
        }
        if ($pageLage > 1) {
            $r->page += 1;
            IQuery::redisSet($this->path.'code'.$r->id, $code_id);  //将递增的code_id存进redis
        } else {
            $r->page = 0;
            $r->status = CouponGrand::GRANDSTATUS['finish'];
            if (Redis::exists($this->path.'code'.$r->id)) {
                Redis::del($this->path.'code'.$r->id);
            }
        }
        if ($lineNumber > 0) {
            Coupon::where('id', $coupon->id)->update(['flag' => Coupon::FLAG['coupon'], 'count' => bcsub($coupon->count, $lineNumber), 'status' => Coupon::STATUS['used']]);  //更新喜茶券模板标志
        }
        $r->save();
    }

    /*
     * 展示数组
     */
    public function showArr($grand, $couponArr, $code_id, $coupon, $user = 0) {
        if (!$grand->scence) {
            $period_start = !$coupon->period_type ? $coupon->period_start : Carbon::now()->startOfDay();
            $period_end = !$coupon->period_type ? $coupon->period_end : Coupon::getTimePeriod($coupon);
        } else {
            $period_start = !$grand->period_type ? $grand->period_start : Carbon::now()->startOfDay();
            $period_end = !$grand->period_type ? $grand->period_end : CouponGrand::getTimePeriod($grand);
        }
        $couponArr[] = [
            'code_id' => $coupon->no.IQuery::strPad($code_id),
            'user_id' => $user,
            'name' => $coupon->name,
            'coupon_id' => $coupon->id,
            'source_id' => $coupon->id,
            'source_type' => Coupon::class,
            'policy' => $coupon->policy,
            'policy_rule' => json_encode($coupon->policy_rule),
            'period_start' => $period_start,
            'period_end' => $period_end,
            'interval_time' => $coupon->interval_time,
            'use_limit' => $coupon->use_limit,
            'created_at' => !$grand->scence ? Carbon::now() : null,
            'updated_at' => !$grand->scence ? Carbon::now() : null,
            'status' => !$grand->scence ? CouponLibrary::STATUS['surplus'] : CouponLibrary::STATUS['unpick'],
            'tab' => !$grand->scence ? CouponLibrary::NEWTAB['new'] : CouponLibrary::NEWTAB['scan'],
            'code' => $grand->scence == CouponGrand::SCENCE['change'] ? IQuery::createCode(7) : null
        ];
        return $couponArr;
    }
}
