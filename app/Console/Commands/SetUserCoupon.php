<?php

/**
 * Created by netbeanIDE.
 * User: limingcun
 * Date: 2018/4/08
 * Time: 下午16:39
 * desc: 定时给用户发券
 */

namespace App\Console\Commands;

use App\Models\Coupon;
use Carbon\Carbon;
use App\Models\CouponGrand;
use App\Models\CouponLibrary;
use App\Models\User;
use App\Models\Member;
use Illuminate\Console\Command;
use Redis;
use Log;
use DB;
use IQuery;
use App\Models\Message;

class SetUserCoupon extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'set:coupon {grand_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '定时给用户发优惠券';

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
        $grand_id = $this->argument('grand_id');  //获取立即发券的id
        $res = CouponGrand::with(['coupon'])->where('status', CouponGrand::GRANDSTATUS['ungrand']);
//        $res = !$grand_id ? $res->whereBetween('grand_time', [Carbon::now(), Carbon::now()->subSecond(-90)]) : $res->where('id', $grand_id);
        $res = !$grand_id ? $res->where('grand_time', '<=', Carbon::now()) : $res->where('id', $grand_id);
        $res = $res->get();
        if ($res->isEmpty()) {
            return;
        }
        $coupon_library = new CouponLibrary;
        foreach ($res as $r) {
            $coupon = $r->coupon;
            DB::beginTransaction();
            try {
                if ($r->scence) {
                    //线下发券(包含二维码)
                    $this->underLine($r, $coupon_library);
                } else {
                    //线上发券
                    switch ($r->range_type) {
                        case 0:
                            $arrUser = User::select('id')->paginate(1000);
                            $pageUser = $arrUser->lastPage();
                            $users = $this->getUserId($arrUser);
                            break;
                        default:
                            $users = IQuery::redisGet($this->path . $r->id);
                            $pageUser = 0;
                            break;
                    }
                    $couponArr = [];
                    $user_tabs = $users;  //用户获取多少张（数组拼接）
                    for ($i = 1; $i < $r->count; $i++) {
                        $users = array_merge_recursive($users, $user_tabs);
                    }
                    $code_id = 1;
                    foreach ($users as $k => $user) {
                        $couponArr = $this->showArr($r, $couponArr, $code_id, $coupon, $user);
                        $code_id++;
                        // 大数据分片执行(每1000条执行一次)
                        if (($k + 1) % 1000 == 0) {
                            $coupon_library->maxInsert($couponArr);
                            $couponArr = [];
                        }
                    }
                    if (count($couponArr) > 0) {
                        $coupon_library->maxInsert($couponArr);
                    }
                    if ($pageUser && $pageUser > 1) {
                        $r->page = 2;
                        $r->status = CouponGrand::GRANDSTATUS['granding'];
                        IQuery::redisSet($this->path . 'code' . $r->id, $code_id);  //将code_id存储临时redis
                    } else {
                        $r->status = CouponGrand::GRANDSTATUS['finish'];  //更改发券状态(已完成)
                    }
                    $sql = 'update members';
//                    Member::whereIn('user_id', $users)->update(['new_coupon_tab' => Member::NEWTAB['new'], 'message_tab' => ]);
                    Member::whereIn('user_id', array_unique($users))->chunk(200, function($members) {
                        foreach($members as $member) {
                            Message::couponsGetMsg($member->user_id);
                            $member->update(['new_coupon_tab' => Member::NEWTAB['new'], 'message_tab' => $member->message_tab + 1]);
                        }
                    });
                    $r->save();
                    //完成后清除redis缓存
                    if (Redis::exists($this->path . $r->id)) {
                        Redis::del($this->path . $r->id);
                    }
                    //更新喜茶券模板标志
                    if (count($users) > 0) {
                        Coupon::where('id', $coupon->id)->update([
                            'count' => bcsub($coupon->count, count($users))
                        ]);
                    }
                }
                DB::commit();
                Log::info('set:coupon_success', ['success']);
            } catch (\Exception $exception) {
                DB::rollback();
                Log::info('set:coupon_error', [$exception]);
                throw new \Exception($exception);
            }
        }
    }

    /*
     * 获取用户id
     */
    public function getUserId($users)
    {
        $userArr = [];
        foreach ($users as $user) {
            $userArr[] = $user->id;
        }
        return $userArr;
    }

    /*
     * 线下发券(包含二维码)
     */
    public function underLine($r, $obj)
    {
        $coupon = $r->coupon;
        $lineNumber = $r->scence == 1 ? $r->amount : bcmul($r->amount, $r->count); //线下（兑换或二维码）喜茶券数量
        if ($lineNumber > 1000) {
            $pageLage = floor($lineNumber / 1000) + 1;   //默认分发10000张券
            $amount = 1000;
        } else {
            $amount = $lineNumber;
            $pageLage = 1;
        }
        $code_id = 1;
        $couponArr = [];
        for ($i = 1; $i <= $amount; $i++) {
            $couponArr = $this->showArr($r, $couponArr, $code_id, $coupon);
            $code_id++;
            // 大数据分片执行(每1000条执行一次)
            if (($i) % 1000 == 0) {
                $obj->maxInsert($couponArr);
                $couponArr = [];
            }
        }
        if (count($couponArr) > 0) {
            $obj->maxInsert($couponArr);
        }
        if ($pageLage > 1) {
            $r->page = 2;
            $r->status = CouponGrand::GRANDSTATUS['granding'];
            IQuery::redisSet($this->path . 'code' . $r->id, $code_id);  //将code_id存储临时redis
        } else {
            $r->status = CouponGrand::GRANDSTATUS['finish'];  //更改发券状态(已完成)
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
