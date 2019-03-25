<?php
/**
 * Created by PhpStorm.
 * User: heyujia
 * Date: 2018/10/25
 * Time: 上午10:25
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\MPermission
 *
 * @property int $id
 * @property string $name
 * @property string $label
 * @property int $status
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MPermission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MPermission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MPermission whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MPermission whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MPermission whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MPermission whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MPermission extends Model
{
    protected $hidden=[
        'created_at',
        'updated_at',
    ];
}