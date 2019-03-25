<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\MallOrderSku
 *
 * @property int $id
 * @property string|null $no 编号
 * @property int $mall_product_id
 * @property float $price 价格
 * @property int $stock 库存
 * @property string|null $specificationIds
 * @property string|null $deleted_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Models\MallProduct $product
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MallOrderSku onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderSku whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderSku whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderSku whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderSku whereMallProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderSku whereNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderSku wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderSku whereSpecificationIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderSku whereStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderSku whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MallOrderSku withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MallOrderSku withoutTrashed()
 * @mixin \Eloquent
 * @property int $store 库存
 * @property int $is_show
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallSku whereIsShow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallSku whereStore($value)
 * @property int $sort
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallSku whereSort($value)
 */
class MallSku extends Model
{
    use SoftDeletes;

    protected $guarded = [
        'id'
    ];
    protected $casts = [
        'is_show' => 'boolean',
    ];
    public function product()
    {
        return $this->belongsTo(MallProduct::class);
    }
}
