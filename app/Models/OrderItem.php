<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\OrderItem
 *
 * @property int $id
 * @property int $order_id
 * @property int $shop_id
 * @property int $product_id
 * @property int $sku_id
 * @property int $quantity
 * @property int $category_id
 * @property float $price 商品价格
 * @property float $total_fee 总金额
 * @property string $name
 * @property string|null $description
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\OrderItemAttribute[] $attributes
 * @property-read \App\Models\Category $category
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\OrderItemMaterial[] $materials
 * @property-read \App\Models\Order $order
 * @property-read \App\Models\Product $product
 * @property-read \App\Models\Sku $sku
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\OrderItemSpecification[] $specifications
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\OrderItem onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrderItem whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrderItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrderItem whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrderItem whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrderItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrderItem whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrderItem whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrderItem wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrderItem whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrderItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrderItem whereShopId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrderItem whereSkuId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrderItem whereTotalFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrderItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\OrderItem withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\OrderItem withoutTrashed()
 * @mixin \Eloquent
 */
class OrderItem extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];


    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    
    public function materials()
    {
        return $this->hasMany(OrderItemMaterial::class);
    }
}
