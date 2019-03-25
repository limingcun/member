<?php
/**
 * Created by PhpStorm.
 * User: heyujia
 * Date: 2018/8/1
 * Time: 下午3:02
 */

namespace App\Http\Controllers\Api;


use App\Http\Requests\Api\Order\OrderDiscountRequest;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;

class OrderController extends Controller
{

    /**
     * 订单优惠信息（活动+优惠券）
     */
    public function orderDiscount(OrderDiscountRequest $request)
    {
        //从订单id去取券
        $collection = new Collection();
        foreach ($request->get('orderIds') as $orderId) {
            $order = new Order();
            $order->id = $orderId;
            $collection->add($order);
        }
        $collection->load([
            'library',
            'library.coupon',
            'active_join',
            'active_join.active',
        ]);
        return $collection;
        
        //备用：从订单coupon_library_id去取券
//        $collection = new Collection();
//        foreach ($request->get('orderIds') as $orderId) {
//            $order = Order::select('id', 'coupon_library_id', 'discount_fee')->find($orderId);
//            $library = CouponLibrary::find(explode(',', $order->coupon_library_id));
//            $order->library = $library;
//            if (count($library) > 0) {
//                $order->library->coupon = $library->load('coupon');
//            }
//            $order->load('active_join.active');
//            $collection->add($order);
//        }
//        return $collection;
    }
}