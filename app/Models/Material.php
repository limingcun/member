<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

/**
 * App\Models\Material
 *
 * @property int $id
 * @property string $no 编码
 * @property string $name 名称
 * @property float $price 价格
 * @property int $is_actived 判断是否启用加料
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Material onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Material whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Material whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Material whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Material whereIsActived($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Material whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Material whereNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Material wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Material whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Material withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Material withoutTrashed()
 * @mixin \Eloquent
 */
class Material extends Model
{
    use SoftDeletes;

    protected $table = 'materials';
}
