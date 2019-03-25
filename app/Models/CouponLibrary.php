<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

/**
 * App\Models\CouponLibrary
 *
 * @property int $id
 * @property string $name 优惠券名称
 * @property int $user_id 用户id
 * @property int $order_id 订单id
 * @property int $coupon_id 优惠券id
 * @property string|null $policy 优惠券领券策略
 * @property string|null $policy_rule 策略规则
 * @property int $source_id 关联id
 * @property string|null $source_type 关联类型
 * @property string|null $period_start 有效期初始时间
 * @property string|null $period_end 有效期结束时间
 * @property string|null $used_at 使用时间
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \App\Models\CouponGrand $grand
 * @property-read \App\Models\Order $order
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $source
 * @property-read \App\Models\User $user
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponLibrary ofWhen($id)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\CouponLibrary onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponLibrary whereCouponId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponLibrary whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponLibrary whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponLibrary whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponLibrary whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponLibrary whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponLibrary wherePeriodEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponLibrary wherePeriodStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponLibrary wherePolicy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponLibrary wherePolicyRule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponLibrary whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponLibrary whereSourceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponLibrary whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponLibrary whereUsedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponLibrary whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\CouponLibrary withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\CouponLibrary withoutTrashed()
 * @mixin \Eloquent
 * @property-read \App\Models\Coupon $coupon
 * @property int $status 核销状态
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponLibrary whereStatus($value)
 * @property string|null $code 兑换码
 * @property string $code_id 券码id
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponLibrary whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponLibrary whereCodeId($value)
 * @property int $tab 新优惠券标识,默认0为未有新状态，1为新优惠券
 * @property float $discount_fee 优惠金额
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponLibrary whereDiscountFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponLibrary whereTab($value)
 * @property int $use_limit 0表示全部可用，1表示自取，2表示外卖
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponLibrary whereUseLimit($value)
 */
class CouponLibrary extends Model
{
    use SoftDeletes;
    const STATUS = [
        'unpick' => 0, //未领取
        'surplus' => 1, //领取未使用优惠券
        'used' => 2,  //已使用优惠券
        'period' => 3  //已过期优惠券
    ];

    const NEWTAB = [
        'scan' => 0, //查看过后新标志
        'new' => 1 //优惠券新标志
    ];

    const USELIMIT = [
        'all' => 0, //全部可用
        'pick' => 1, //自取
        'take' => 2 //外卖
    ];

    const CUPTYPE = [
        'category' => 0, //类别   非折扣券的类别
        'order' => 0, //订单
        'product' => 1,  //饮品
        'take' => 2, //外卖
        'material' => 3, //指定加料
        'discount_category' => 4 // 折扣券的类别
    ];

    const VALEN = [
        'highest' => 0, //最高
        'higher' => 1, //次高
        'lower' => 2, //次低
        'lowest' => 3 //最低
    ];

    protected $dates = [
        'deleted_at',
        'period_start',
        'period_end'
    ];
    protected $table = 'coupon_librarys';
    protected $guarded = ['id'];
    protected $casts = [
        'policy_rule' => 'array',
    ];

    protected $fillable = [
        'name',
        'user_id',
        'order_id',
        'coupon_id',
        'policy',
        'policy_rule',
        'period_type',
        'period_start',
        'period_end',
        'period_day',
        'status',
        'code_id',
        'source_id',
        'source_type',
        'tab',
        'use_limit',
        'updated_at',
        'created_at'
    ];

    public function scopeOfWhen($query, $id)
    {
        return $query->where('user_id', $id);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->select(array('id', 'name', 'phone'));
    }

    public function usecount() {
        return $this->where('time_used', '!=', null)->whereRaw('time_used < period_end')->count();
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function source()
    {
        return $this->morphTo();
    }

    //批量插入数据
    public function maxInsert(Array $data)
    {
        $rs = DB::table($this->getTable())->insert($data);
    }

    public function grand() {
        return $this->belongsTo(CouponGrand::class, 'coupon_id', 'coupon_id');
    }

    public function coupon() {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }

    public function member() {
        return $this->belongsTo(Member::class, 'user_id', 'user_id');
    }
}
