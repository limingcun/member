<?php
/**
 * Created by PhpStorm.
 * User: heyujia
 * Date: 2018/10/25
 * Time: 上午10:25
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\MRole
 *
 * @mixin \Eloquent
 */
class MRole extends Model
{
    use  SoftDeletes;
    protected $table='m_role';
    protected $guarded=[
        'id'
    ];
    protected $hidden=[
        'created_at',
        'updated_at',
    ];
    public function permission(){
        return $this->belongsToMany(MPermission::class,'m_role_permissions','role_id','permission_id');
    }
}