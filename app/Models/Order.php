<?php

namespace App\Models;

use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Order
 *
 * @property int $id
 * @property string $no
 * @property string|null $outer_id
 * @property string $pickup_no
 * @property string $pickup_code
 * @property int $user_id
 * @property int $member_id
 * @property int $shop_id
 * @property bool $is_takeaway
 * @property float $total_fee 总金额
 * @property float $box_fee
 * @property float $delivery_fee
 * @property float $discount_fee 优惠金额
 * @property float $payment 实付金额
 * @property string $phone
 * @property string $paid_type
 * @property \Carbon\Carbon|null $paid_at
 * @property \Carbon\Carbon|null $printed_at
 * @property \Carbon\Carbon|null $dispatched_at
 * @property \Carbon\Carbon|null $closed_at
 * @property string|null $transaction_no
 * @property string $prepay_id
 * @property string $status
 * @property string $refund_status
 * @property \Carbon\Carbon|null $pickup_time 取餐时间
 * @property string|null $pickup_time_period 取餐时间段
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read mixed $paid_type_label
 * @property-read mixed $status_label
 * @property-read \App\Models\Member $member
 * @property-read \App\Models\Shop $shop
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order filter($input = array(), $filter = null)
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Order onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order paginateFilter($perPage = null, $columns = array(), $pageName = 'page', $page = null)
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order simplePaginateFilter($perPage = null, $columns = array(), $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereBoxFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereClosedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereDeliveryFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereDiscountFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereDispatchedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereIsTakeaway($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereOuterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order wherePaidAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order wherePaidType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order wherePayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order wherePickupCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order wherePickupNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order wherePickupTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order wherePickupTimePeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order wherePrepayId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order wherePrintedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereRefundStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereShopId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereTotalFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereTransactionNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Order withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Order withoutTrashed()
 * @mixin \Eloquent
 * @property string $location 用户下单经纬度
 * @property string $remarks 订单备注
 * @property string|null $latest_reufnd_at 最近申请退款时间
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CouponLibrary[] $library
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\MemberScore[] $member_score
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereLatestReufndAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereRemarks($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\OrderItem[] $item
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\MemberScore[] $scores
 * @property int|null $coupon_library_id 优惠券id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\RefundOrder[] $refund
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereCouponLibraryId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ActiveJoin[] $active_join
 * @property int $is_sub 服务商切换
 * @property string $trade_type 交易发起平台
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereIsSub($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereTradeType($value)
 */
class Order extends Model
{
    use Filterable;

    public function shop()
    {
        return $this->hasOne(Shop::class, 'id', 'shop_id');
    }

    public function item()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function refund()
    {
        return $this->hasMany(RefundOrder::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function active_join()
    {
        return $this->hasMany(ActiveJoin::class);
    }

    public function library()
    {
        return $this->hasMany(CouponLibrary::class);
    }

    public function scores()
    {
        return $this->morphMany(MemberScore::class, 'source');
    }
    
    public function delivery()
    {
        return $this->hasOne(OrderDelivery::class);
    }
}
