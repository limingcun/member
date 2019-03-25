<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/**
 * App\Models\OrderItemMaterial
 *
 * @property int $id
 * @property int $order_item_id order_items 主键ID
 * @property int $shop_id 门店ID
 * @property int $material_id materials主键ID
 * @property int $no 加料编码
 * @property float $price 加料价格
 * @property string $name 加料名称
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Models\OrderItem $item
 * @property-read \App\Models\Material $material
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrderItemMaterial whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrderItemMaterial whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrderItemMaterial whereMaterialId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrderItemMaterial whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrderItemMaterial whereNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrderItemMaterial whereOrderItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrderItemMaterial wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrderItemMaterial whereShopId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrderItemMaterial whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class OrderItemMaterial extends Model
{
    protected $guarded = ['id'];

    public function item()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}
