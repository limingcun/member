<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\MemberScore;
use App\Models\Member;
use DB;
use Carbon\Carbon;
use App\Models\Coupon;
use App\Models\CouponLibrary;
use IQuery;
use App\Models\Order;
use App\Policies\CouponLibrary\CashCouponPolicy; //现金券
use App\Policies\CouponLibrary\DiscountCouponPolicy; //折扣券
use App\Policies\CouponLibrary\FeeCouponPolicy; //赠饮券
use App\Policies\CouponLibrary\BuyFeeCouponPolicy; //买N送M券
use App\Policies\CouponLibrary\QueueCouponPolicy; //免排队券

class GameCouponController extends Controller
{
    // redis存储路径
    protected $redis_path = 'laravel:spring:game:coupon:';
    
    /**
     * 路由中转
     */
    public function index(Request $request) {
        $result = $request->all();
        $keys = array_keys($result); 
        if (!in_array('funcode', $keys) || !in_array('contentData', $keys) || !in_array('cooperator', $keys) || !in_array('version', $keys)) {
            return response()->json(['errorCode' => '0003', 'errorMsg' => '传入参数错误']);
        }
        $fun = $result['funcode'];
        if ($fun == 'A1.getCoupon') {
            $contentData = json_decode($result['contentData'], true);
            $user_id = $contentData['userId'];
            $data = $this->getCoupon($user_id);
            if (array_key_exists('errorCode', $data)) {
                return response()->json($data);
            }
            $result['contentData'] = json_encode($data);
            return $result;
        }
        return response()->json(['errorCode' => '0003', 'errorMsg' => '传入参数错误']);
    }
    
    /**
     * 规则发券
     * @param type $user_id
     */
    public function getCoupon($user_id) {
        $spring_game = system_varable('spring_game');
        $coupon_arrs = explode(',', $spring_game); //0:两元现金券,1:3元现金券,2:5元现金券,3:8元现金券,4赠饮券,5:买1赠1,6:优先券
        $coupon_ids = Coupon::whereIn('flag', $coupon_arrs)->pluck('id');
        $library = CouponLibrary::where('user_id', $user_id)->whereIn('coupon_id', $coupon_ids)->first();
        if ($library) {
            $data = ['errorCode' => '0004', 'errorMsg' => '该用户已领取'];
            return $data;
        }
        $user = User::find($user_id);
        if (!$user) {
            $data = ['errorCode' => '0005', 'errorMsg' => '用户不存在'];
            return $data;
        }
        DB::beginTransaction();
        try {
            $count = IQuery::redisGet($this->redis_path.'count');
            if (!$count) {
                $count = 0;
                IQuery::redisSet($this->redis_path.'count', 1);
            } else {
                IQuery::redisIncr($this->redis_path.'count');
            }
            if ($count % 100 == 37) {
                $no = $coupon_arrs[4];
                $data = $this->randCashCoupon($no, $user, $count, 'fee');
                if (!$data) {
                    $data = ['errorCode' => '0002', 'errorMsg' => '库存不足'];
                    IQuery::redisDecr($this->redis_path.'count');
                    DB::rollBack();
                    return $data;
                }
                DB::commit();
                return $data;
            } else {
//                $order = Order::where('user_id', $user_id)->whereNotNull('paid_at')->where('refund_status', '!=', 'FULL_REFUND')->orderBy('paid_at', 'desc')->first();
                $order = $user->sum_trade_times;
                //无消费记录
                if (!$order) {
                    //根据用户注册时间间距发券
                    $data = $this->createdAtCoupon($user, $count, $coupon_arrs);
                    if (!$data) {
                        $data = ['errorCode' => '0002', 'errorMsg' => '库存不足'];
                        IQuery::redisDecr($this->redis_path.'count');
                        DB::rollBack();
                        return $data;
                    }
                    DB::commit();
                    return $data;
                } else {
                    //根据用户注册时间间距发券
                    $data = $this->PaidAtCoupon($user, $count, $coupon_arrs);
                    if (!$data) {
                        $data = ['errorCode' => '0002', 'errorMsg' => '库存不足'];
                        IQuery::redisDecr($this->redis_path.'count');
                        DB::rollBack();
                        return $data;
                    }
                    DB::commit();
                    return $data;
                }
            }
        } catch(\Exception $e) {
            \Log::error('GAME_COUPON_ERROR', [$e]);
            $data = ['errorCode' => '0001', 'errorMsg' => '发券失败'];
            IQuery::redisDecr($this->redis_path.'count');
            DB::rollBack();
            return $data;
        }
    }
    
    /**
     * 根据注册时间发券
     * @param type $user
     * $coupon_arrs 0:两元现金券,1:3元现金券,2:5元现金券,3:8元现金券,4赠饮券,5:买1赠1,6:优先券
     */
    public function createdAtCoupon($user, $count, $coupon_arrs) {
        $created_at = $user->created_at;
        $created_timestamp = Carbon::parse($created_at)->timestamp;
        if ($created_timestamp > Carbon::today()->subDays(30)->timestamp && $created_timestamp <= Carbon::now()->timestamp) {
            $array = [$coupon_arrs[0], $coupon_arrs[1]];
            $no = $array[array_rand($array)];
            return $this->randCashCoupon($no, $user, $count);
        } else if ($created_timestamp > Carbon::today()->subDays(60)->timestamp && $created_timestamp <= Carbon::today()->subDays(30)->timestamp) {
            $no = $coupon_arrs[2];
            return $this->randCashCoupon($no, $user, $count);
        } else if ($created_timestamp > Carbon::today()->subDays(90)->timestamp && $created_timestamp <= Carbon::today()->subDays(60)->timestamp) {
            $no = $coupon_arrs[3];
            return $this->randCashCoupon($no, $user, $count);
        } else {
            $no = $coupon_arrs[3];
            $gift = $this->giftCouponTicket($user, $coupon_arrs[6], 1);
            $rand = $this->randCashCoupon($no, $user, $count, 'queue');
            if ($gift && $rand) {
                return $rand;
            }
            return false;
        }
    }
    
    /**
     * 根据支付时间发券
     * $coupon_arrs 0:两元现金券,1:3元现金券,2:5元现金券,3:8元现金券,4赠饮券,5:买1赠1,6:优先券
     */
    public function PaidAtCoupon($user, $count, $coupon_arrs) {
        $paid_at = $user->last_trade_at;
        $paid_timestamp = Carbon::parse($paid_at)->timestamp;
        if ($paid_timestamp > Carbon::today()->subDays(30)->timestamp && $paid_timestamp <= Carbon::now()->timestamp) {
            $created_at = $user->created_at;
            $created_timestamp = Carbon::parse($created_at)->timestamp;
            if ($created_timestamp > Carbon::today()->subDays(30)->timestamp && $created_timestamp <= Carbon::now()->timestamp) {
                $no = $coupon_arrs[2];
                return $this->randCashCoupon($no, $user, $count);
            } else {
                $array = [$coupon_arrs[0], $coupon_arrs[1]];
                $no = $array[array_rand($array)];
                return $this->randCashCoupon($no, $user, $count);
            }
        } else if ($paid_timestamp > Carbon::today()->subDays(90)->timestamp && $paid_timestamp <= Carbon::today()->subDays(30)->timestamp) {
//            $payment = Order::where('user_id', $user_id)->whereNotNull('paid_at')->where('refund_status', '!=', 'FULL_REFUND')->sum('payment');
            $payment = $user->sum_trade_fee / $user->sum_trade_times;
            if ($payment > 0 && $payment < 50) {
                $array = [$coupon_arrs[0], $coupon_arrs[1]];
                $no = $array[array_rand($array)];
                return $this->randCashCoupon($no, $user, $count);
            } else if ($payment >= 50 && $payment < 100) {
                $no = $coupon_arrs[2];
                return $this->randCashCoupon($no, $user, $count);
            } else {
                $no = $coupon_arrs[3];
                return $this->randCashCoupon($no, $user, $count);
            }
        } else {
            $no = $coupon_arrs[5];
            $gift = $this->giftCouponTicket($user, $coupon_arrs[6], 1);
            $rand = $this->randCashCoupon($no, $user, $count, 'buyfee');
            if ($gift && $rand) {
                return $rand;
            }
            return false;
        }
    }

        /**
     * 创建随机现金券
     * @return string
     */
    public function randCashCoupon($no, $user, $count, $coupon = 'cash') {
        $gift = $this->giftCouponTicket($user, $no, 1);
        if (!$gift) {
            return $gift;
        }
        $data['userId'] = $user->id;
        $data['currentFlag'] = $count + 1;
        if ($coupon == 'cash') {
            $data['couponTitle'] = '现金券';
        } else if ($coupon == 'queue'){
            $data['couponTitle'] = '现金券、优先券';
        } else if ('fee') {
            $data['couponTitle'] = '赠饮券';
        } else {
            $data['couponTitle'] = '买赠券、优先券';
        }
        return $data;
    }


    /*
     * 赠券
     * $user用户
     * $name券名称
     * $policy策略类
     * $rule策略规则
     * $count数量
     */
    public function giftCouponTicket($user, $no, $num) {
        $coupon = Coupon::where('no', $no)->first();
        if ($coupon->count <= 0) {
            return false;
        }
        //新建模板个人库
        for($i = 0; $i< $num; $i++) {
            $libraryArr[] = [
                'name' => $coupon->name,
                'user_id' => $user->id,
                'order_id' => 0,
                'coupon_id' => $coupon->id,
                'policy' => $coupon->policy,
                'policy_rule' => json_encode($coupon->policy_rule),
                'source_id' => $coupon->id,
                'source_type' => Coupon::class,
                'period_start' => !$coupon->period_type ? $coupon->period_start : Carbon::now()->format('Y-m-d'),  //period_type为固定时间和相对时间
                'period_end' => !$coupon->period_type ? $coupon->period_end : Coupon::getTimePeriod($coupon),
                'use_limit' => $coupon->use_limit,
                'status' => CouponLibrary::STATUS['surplus'],
                'code_id' => $coupon->no.IQuery::strPad(date('ymd').rand(100, 999)),
                'tab' => CouponLibrary::NEWTAB['new'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];
        }
        DB::table('coupon_librarys')->insert($libraryArr);
        $coupon->update([
            'count' => $coupon->count - $num
        ]);
        $user->members->first()->update(['new_coupon_tab' => Member::NEWTAB['new']]);
        return true;
    }
}
