<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\MallOrderItem
 *
 * @property int $id
 * @property string $name 商品名称
 * @property int $mall_order_id 积分商城订单号id
 * @property int $mall_product_id 积分商城商品id
 * @property string $source_type 商品订单关联类型
 * @property string $source_id 商品订单关联id
 * @property string|null $remark 商品说明
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \App\Models\MallProduct $product
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MallOrderItem onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderItem whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderItem whereMallOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderItem whereMallProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderItem whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderItem whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderItem whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderItem whereSourceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MallOrderItem withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MallOrderItem withoutTrashed()
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $source
 * @property int $mall_sku_id sku_id
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderItem whereMallSkuId($value)
 */
class MallOrderItem extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];  //开启deleted_at
    protected $table = 'mall_order_items';
    protected $casts = [
        'policy_rule' => 'array'
    ];
    
    public function source() {
        return $this->morphTo();
    }
    
    public function product() {
        return $this->belongsTo(MallProduct::class, 'mall_product_id');
    }
}
