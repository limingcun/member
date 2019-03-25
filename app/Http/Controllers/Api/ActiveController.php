<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\Active\CancelRequest;
use App\Http\Requests\Api\Active\JoinRequest;
use App\Http\Requests\Api\Active\ShopActiveRequest;
use App\Models\Active;
use App\Models\ActiveJoin;
use App\Models\Coupon;
use App\Models\CouponLibrary;
use App\Models\User;
use App\Models\Shop;
use App\Transformers\Api\Active\ActiveTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DB;
use App\Models\Order;
use IQuery;

class ActiveController extends Controller
{
    // redis存储路径
    const REDIS_PATH = 'laravel:order:discount';
    
    /**
     * 参与活动
     */
    public function joinActive(JoinRequest $request)
    {
        ActiveJoin::create($request->all());
        return success_return('ok');
    }

    public function cancelActive(CancelRequest $request)
    {
        ActiveJoin::where([
            'order_id' => $request->get('order_id'),
        ])->delete();
        return success_return('ok');
    }


    /**
     * 门店可参与的活动
     */
    public function shopActive(ShopActiveRequest $request)
    {

        $shop = Shop::findOrFail($request->get('shop_id'));
        $user = User::findOrFail($request->get('user_id'));
        $actives = Active::where('status', 1)
            ->with(['shop', 'user'])
            ->where(function ($query) {
                $query->where(function ($query) {
                    $query->Where('period_type', Active::PERIOD['fixed'])->where('period_start', '<=', Carbon::now())
                        ->where('period_end', '>=', Carbon::now());
                })->orWhere('period_type', Active::PERIOD['relative']);
            })
            ->get();
        $actives = $actives->filter(function ($active) use ($shop, $user) {
            $policy = app($active->policy);
            return ($policy->shop($active, $shop)
                && $policy->user($active, $user)
                && $policy->freq($active, $user));
        });
        return $this->response->collection($actives, new ActiveTransformer());
    }

    /*
     * 返回活动数据信息
     */
    public function getActiveInfo(Request $request)
    {
        $arr = [];
        $order_arr = $request->order_id;
        if (count($order_arr) > 0) {
            $libraryArr = [];
            $activeArr = [];
            $order_ids = implode(',', $order_arr);
            $res = DB::select("select id, coupon_library_id, discount_fee from orders where id in (" .$order_ids. ") and (discount_fee > 0 or prior > 0)");
            foreach($res as $r) {
                if ($r->coupon_library_id) {
                    $carr_ids = explode(',', $r->coupon_library_id);
                    foreach($carr_ids as $carr_id) {
                        $libraryArr[] = $carr_id;
                    }
                } else {
                    $activeArr[] = $r->id;
                }
            }
            if (count($libraryArr) > 0) {
                $coupon_ids = implode(',', $libraryArr);
                $coupon_res = DB::select("select id, name, policy from coupon_librarys where id in (" .$coupon_ids. ")");
            } else {
                $coupon_res = [];
            }
            if (count($activeArr) > 0) {
                $active_ids = implode(',', $activeArr);
                $active_res = DB::select("select a.id, a.name, aj.order_id, aj.discount_fee from active_joins aj join actives a on aj.active_id = a.id where aj.order_id in (" .$active_ids. ")");
            } else {
                $active_res = [];
            }
        }
        foreach($order_arr as $order_id) {
            if (!in_array($order_id, array_column($res, 'id'))) {
                $arr[$order_id] = [];
            } else {
                $redis_order_value = IQuery::redisGet(self::REDIS_PATH.$order_id);
                if (!$redis_order_value) {
                    if (!in_array($order_id, array_column($active_res, 'order_id'))) {
                        $couponArr = [];
                        $order_column_id = array_column($res, 'id');
                        $order_key = array_search($order_id, $order_column_id);
                        $coupon_library_id = $res[$order_key]->coupon_library_id;
                        if ($coupon_library_id) {
                            $coupon_librarys = explode(',', $coupon_library_id);
                            foreach ($coupon_librarys as $library_id) {
                                $coupon_column = array_column($coupon_res, 'id');
                                $coupon_key = array_search($library_id, $coupon_column);
                                $couponPolicy = app($coupon_res[$coupon_key]->policy);
                                $couponArr['coupon_name'] = $coupon_res[$coupon_key]->name;
                                $couponArr['discount_fee'] = $res[$order_key]->discount_fee;
                                $couponArr['type_num'] = $couponPolicy->typeNum();
								$arr[$order_id][] = $couponArr;
                            }
                        } else {
							$arr[$order_id] = $couponArr;
						}
                    } else {
                        $activeArr = [];
                        $active_column = array_column($active_res, 'order_id');
                        $active_key = array_search($order_id, $active_column, true);
                        if ($active_key !== false) {
                            $activeArr['active_id'] = $active_res[$active_key]->id;
                            $activeArr['coupon_name'] = $active_res[$active_key]->name;
                            $activeArr['discount_fee'] = $active_res[$active_key]->discount_fee;
                            $activeArr['type_num'] = 100;
                            $arr[$order_id][] = $activeArr;
                        } else {
                            $arr[$order_id] = $activeArr;
                        }
                    }
                    IQuery::redisSet(self::REDIS_PATH.$order_id, $arr[$order_id], 3600 * 24);
                } else {
                    $arr[$order_id] = $redis_order_value;
                }
            }
        }
        return success_return($arr);
    }
}
