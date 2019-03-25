<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

/**
 * App\Models\Active
 *
 * @property int $id
 * @property string $name 活动名
 * @property string|null $policy 优惠券领券策略
 * @property array $policy_rule 策略规则
 * @property int $shop_limit 门店限制
 * @property int $coupon_id 优惠券id
 * @property \Carbon\Carbon|null $period_start 有效期初始时间
 * @property \Carbon\Carbon|null $period_end 有效期结束时间
 * @property string|null $deleted_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Active onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active whereCouponId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active wherePeriodEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active wherePeriodStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active wherePolicy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active wherePolicyRule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active whereShopLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Active withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Active withoutTrashed()
 * @mixin \Eloquent
 * @property string|null $no
 * @property string|null $description
 * @property int $category_id
 * @property int $sold_count
 * @property int $is_listing
 * @property int $is_single
 * @property int $sort 排序
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereIsListing($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereIsSingle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereSoldCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereSort($value)
 * @property string $label 标签
 * @property int $support_takeaway 外卖状态0关1开
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereSupportTakeaway($value)
 * @property-read \App\Models\Category $category
 */
class Product extends Model
{
    use SoftDeletes;
    protected $table = 'products';

     public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
