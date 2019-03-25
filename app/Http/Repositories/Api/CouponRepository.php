<?php
namespace App\Http\Repositories\Api;

use Illuminate\Http\Request;
use App\Models\CouponLibrary;
use App\Http\Repositories\BaseRepository;
use Carbon\Carbon;
use App\Models\Member;
use App\Policies\Policy;
use App\Models\User;
use DB;
use App\Transformers\Api\CouponLibraryTransformer;

class CouponRepository extends BaseRepository
{
    public function __construct() {
    }

    /**
     * 个人可用优惠券
     * @param User $user
     * @return type
     */
    public function usableCoupon(User $user) {
        $libarr = [];
        $user->members()->where('new_coupon_tab', Member::NEWTAB['new'])->update(['new_coupon_tab' => Member::NEWTAB['scan']]);  //会员总新优惠券状态变更
        $library = $user->library()->where('period_end', '>=', Carbon::today())
            ->with('coupon', 'coupon.shop', 'coupon.product', 'coupon.category', 'coupon.material', 'coupon.category.products')
            ->where('status', CouponLibrary::STATUS['surplus'])
            ->select('id', 'user_id', 'name', 'order_id', 'coupon_id', 'policy', 'policy_rule', 'period_start', 'period_end', 'status', 'tab', 'use_limit', 'interval_time')
            ->orderBy('created_at', 'desc')->orderBy('id', 'desc')->paginate(config('app.page'));
        foreach($library as $lib) {
            $libarr[] = $lib->id;
        }
        $user->library()->whereIn('id', $libarr)->where('tab', CouponLibrary::NEWTAB['new'])->update(['tab' => CouponLibrary::NEWTAB['scan']]); //单条新优惠券状态变更
        return $library;
    }

    /**
     * 个人已使用优惠券
     * @param User $user
     * @return type
     */
    public function usedAndPeriodCoupon(User $user, $type) {
        $library = $user->library()
            ->with('coupon', 'coupon.shop', 'coupon.product', 'coupon.category', 'coupon.material', 'coupon.category.products')
            ->where('status', CouponLibrary::STATUS[$type])
            ->select('id', 'user_id', 'name', 'order_id', 'coupon_id', 'policy', 'policy_rule', 'period_start', 'period_end', 'status', 'tab', 'use_limit', 'interval_time');
        if ($type == 'used') {
            $library = $library->orderBy('used_at', 'desc');
        } else {
            $library = $library->orderBy('id', 'desc');
        }
        return $library->paginate(config('app.page'));
    }

    /**
     * 订单使用优惠券
     * @param User $user
     * @param Request $request
     */
    public function orderCoupon(User $user, Request $request) {
        $page = (request('page', 1) - 1) * config('app.page');
        $items = $request->get('items');
        $shop_id = $request->get('shop_id');
        $is_take = $request->get('is_take');
        $is_usable = $request->get('is_usable') ?? true;
        $delivery_fee = $request->get('delivery_fee') ?? 0;
        $couponLibrarys = $user->library()->where('status', CouponLibrary::STATUS['surplus'])
            ->where('period_end', '>=', Carbon::today())
            ->whereNull('used_at')
            ->orderBy('period_end', 'asc')
            ->with('coupon', 'coupon.shop', 'coupon.product', 'coupon.category', 'coupon.material', 'coupon.category.products')
            ->get();
        if (!Policy::verifyItems($items)) return error_return(9706); //item数据格式错误
        $couponLibrarys = $couponLibrarys->filter(function ($couponLibrary) use ($items, $shop_id, $is_take, $is_usable, $delivery_fee) {
            $libraryPolicy = app($couponLibrary->policy);
            $usable = $libraryPolicy->usable($couponLibrary, $shop_id, $items, $is_take);
            $couponLibrary->discount = $libraryPolicy->discount($couponLibrary, $items, $shop_id,  $delivery_fee);
            if (!$is_usable) {
                $couponLibrary->unuse_text = $libraryPolicy->unUseText($couponLibrary, $shop_id, $items, $is_take);
            }
            if ($is_usable == $usable) {
                $couponLibrary->usable = $usable;
                return true;
            } else {
                return false;
            }
        });
        $libArr = [];
        foreach($couponLibrarys as $libs) {
            $libArr[] = $libs;
        }
        $librarys = $this->pageSize($page, $libArr, 10);
        return ['librarys' => $librarys, 'total' => count($couponLibrarys)];
    }
}
