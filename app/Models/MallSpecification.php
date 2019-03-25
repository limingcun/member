<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\MallSpecification
 *
 * @property int $id
 * @property int $mall_product_id
 * @property string|null $name 规格名
 * @property string|null $value 规格值
 * @property string|null $deleted_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Models\MallProduct $product
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MallSpecification onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallSpecification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallSpecification whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallSpecification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallSpecification whereMallProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallSpecification whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallSpecification whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallSpecification whereValue($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MallSpecification withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MallSpecification withoutTrashed()
 * @mixin \Eloquent
 * @property int $sort 规格排序字段
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallSpecification whereSort($value)
 */
class MallSpecification extends Model
{
    use SoftDeletes;

    protected $guarded = [
        'id'
    ];
    protected $hidden=[
        'deleted_at','created_at','updated_at'
    ];

    public function product()
    {
        return $this->belongsTo(MallProduct::class);
    }
}
