<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\MallOrderCoupon
 *
 * @property int $id
 * @property string|null $policy 优惠券领券策略
 * @property array $policy_rule 策略规则
 * @property int $period_type 过期类型（0绝对时间，1相对时间）
 * @property string|null $period_start 有效期初始时间
 * @property string|null $period_end 有效期结束时间
 * @property int|null $period_day 有效时间段
 * @property int $shop_limit 门店限制
 * @property int $product_limit 商品限制
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\MallOrderCoupon[] $mallcoupon
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MallOrderCoupon onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderCoupon whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderCoupon whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderCoupon whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderCoupon wherePeriodDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderCoupon wherePeriodEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderCoupon wherePeriodStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderCoupon wherePeriodType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderCoupon wherePolicy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderCoupon wherePolicyRule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderCoupon whereProductLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderCoupon whereShopLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderCoupon whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MallOrderCoupon withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MallOrderCoupon withoutTrashed()
 * @mixin \Eloquent
 * @property string $code_id 优惠券编码id
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderCoupon whereCodeId($value)
 * @property string|null $no
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderCoupon whereNo($value)
 * @property int $use_limit 0表示全部可用，1表示自取，2表示外卖
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderCoupon whereUseLimit($value)
 */
class MallOrderCoupon extends Model
{
    use SoftDeletes;
    protected $dates = [
        'deleted_at',
        'period_start',
        'period_end'
    ];
    protected $table = 'mall_order_coupons';
    protected $casts = [
        'policy_rule' => 'array'
    ];
    
    public function mallcoupon() {
        return $this->morphMany(MallOrderCoupon::class, 'source');
    }
}
