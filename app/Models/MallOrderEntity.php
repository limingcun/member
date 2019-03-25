<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\MallOrderEntity
 *
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MallOrderEntity onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MallOrderEntity withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MallOrderEntity withoutTrashed()
 * @mixin \Eloquent
 * @property int $id
 * @property string|null $specifications 规格json数据
 * @property string|null $deleted_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderEntity whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderEntity whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderEntity whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderEntity whereSpecifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderEntity whereUpdatedAt($value)
 */
class MallOrderEntity extends Model
{
    use SoftDeletes;

    protected $guarded = [
        'id'
    ];

    protected $casts=[
        'specifications'=>'array'
    ];
}
