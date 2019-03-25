<?php

/**
 * Created by netbeanIDE.
 * User: limingcun
 * Date: 2018/11/28
 * Time: 下午16:39
 * desc: 积分经验值补录
 */
namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\Member;
use App\Models\MemberScore;
use App\Models\MemberExp;
use App\Models\CouponLibrary;
use Log;
use DB;
use IQuery;

class AddRecord extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add:record';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '积分经验值补录';
    
    protected $redis_path = 'laravel:add_record:';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $redis_path = $this->redis_path.'score_star_exp';
        if (!IQuery::redisExists($redis_path)) {
            return;
        }
        DB::beginTransaction();
        try {
            $res = IQuery::redisGet($redis_path);
            $arrs = array_splice($res, 0, 500);
            foreach($arrs as $arr) {
                $order_id = $arr->id;
                $user_id = $arr->user_id;
                $payment = $arr->payment;
                $delivery_fee = $arr->delivery_fee;
                $fee = $payment - $delivery_fee;
                $created_at = $arr->created_at;
                $updated_at = $arr->updated_at;
                $member = Member::where('user_id', $arr->user_id)->first();
                $is_star = $this->isStarMember($member);
                if ($is_star) {
                    $method = 1;
                    $description = '消费补录积分';
                    $member_type = 1;
                    $score = floor($fee/1);
                    $this->saveMemberScore($user_id, $order_id, $method, $description, $member_type, $score, $created_at, $updated_at);
                    if (Carbon::parse($created_at)->format('d') == 12) {
                        $method = 6;
                        $description = '会员日消费补录积分';
                        $extra_score = $score * $member->star_level_id * 0.05;
                        $score += $extra_score;
                        $this->saveMemberScore($user_id, $order_id, $method, $description, $member_type, $extra_score, $created_at, $updated_at);
                    }
                    $cup = 0;
                    if ($member->star_level_id >= 4) {
                        $cup = $this->getMemberCup($order_id);
                    }
                    $this->saveMember($member, $is_star, $payment, $fee, $score, $cup);
                    $this->saveMemberExp($member, $order_id, $is_star, $fee);
                } else {
                    $method = 1;
                    $description = '消费补录积分';
                    $member_type = 0;
                    $score = floor($fee/2);
                    $cup = 0;
                    $this->saveMemberScore($user_id, $order_id, $method, $description, $member_type, $score, $created_at, $updated_at);
                    $this->saveMember($member, $is_star, $payment, $fee, $score, $cup);
                    $this->saveMemberExp($member, $order_id, $is_star, $fee);
                }
            }
            if (count($res) > 0) {
                IQuery::redisSet($redis_path, $res);
            } else {
                IQuery::redisDelete($redis_path);
            }
            DB::commit();
            Log::info('add:record_success', ['success']);
        } catch (\Exception $exception) {
            DB::rollback();
            Log::info('add:record_error', [$exception]);
        } 
    }
    
    /**
     * 判断是星球会员还是Go会员
     * $member会员
     */
    public function isStarMember(Member $member) {
        if (!$member->expire_time) {
            return false;
        }
        if (Carbon::parse($member->expire_time)->timestamp >= Carbon::today()->timestamp) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * 保存积分记录
     * $user_id用户id
     * $order_id订单id
     * $method来源
     * $description描述
     * $member_type会员状态
     * $score_change积分变动
     */
    public function saveMemberScore($user_id, $order_id, $method, $description, $member_type, $score_change, $created_at, $updated_at) {
        $member_score = new MemberScore;
        $member_score->user_id = $user_id;
        $member_score->source_id = $order_id;
        $member_score->source_type = Order::class;
        $member_score->method = $method;
        $member_score->description = $description;
        $member_score->origin = 0;
        $member_score->member_type = $member_type;
        $member_score->score_change = $score_change;
        $member_score->created_at = $created_at;
        $member_score->updated_at = $updated_at;
        $member_score->save();
    }
    
    /**
     * 保存会员信息
     * $member会员
     * $is_star判断是星球还是go
     * $payment支付金额
     * $fee产生经验值金额
     * $score积分
     */
    public function saveMember($member, $is_star, $payment, $fee, $score, $cup) {
        if ($is_star) {
            $go_exp = floor($fee/2);
            $star_exp = floor($fee/1);
        } else {
            $go_exp = floor($fee/2);
            $star_exp = 0;
        }
        $member->order_count += 1;
        $member->order_money += round($member->order_money + $payment, 2);
        $member->order_score += $score;
        $member->usable_score += $score;
        $member->star_exp += $star_exp;
        $member->exp += $go_exp;
        $member->member_cup += $cup;
        $member->save();
    }
    
    /**
     * 判断是否有效杯数
     * @param type $order_id
     */
    public function getMemberCup($order_id) {
        $order = Order::find($order_id);
        $cup = 0;
        $coupon_flag = 0;
        if ($order->coupon_library_id) {
            $lib_ids = explode(',', $order->coupon_library_id);
            $coupon_librarys = CouponLibrary::findOrFail($lib_ids);
            foreach($coupon_librarys as $coupon_library) {
                $libraryPolicy = app($coupon_library->policy);
                $type_num = $libraryPolicy->typeNum();
                if ($type_num == 1) {  //使用赠饮券不算有效单
                    $coupon_flag = 1;
                    break;
                }
            }
        }
        if ($coupon_flag) {
            return $cup;
        }
        $items = $order->item;
        $price_flag = 0;
        foreach($items as $item) {
            if ($item->price >= 11) {
                $price_flag = 1;
                break;
            }
        }
        if (!$price_flag) {
            return $cup;
        }
        return 1;
    }
    
   /**
     * 保存memberExp
     * $member会员
     * $order订单
     * point积分
     */
    public function saveMemberExp($member, $order_id, $is_star, $fee)
    {
        if ($is_star) {
            $go_exp = floor($fee/2);
            $star_exp = floor($fee/1);
        } else {
            $go_exp = floor($fee/2);
            $star_exp = 0;
        }
        $description = '消费补录经验值';
        MemberExp::createMemberExp($member, $member->user_id, $order_id, Order::class, MemberExp::METHOD['cost'], $go_exp, $star_exp, $description);
    }
}
