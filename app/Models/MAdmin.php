<?php
/**
 * Created by PhpStorm.
 * User: heyujia
 * Date: 2018/10/25
 * Time: 上午10:25
 */

namespace App\Models;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject as AuthenticatableUserContract;

/**
 * App\Models\MAdmin
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property int $sex
 * @property string $mobile
 * @property string $department
 * @property string $password
 * @property int $role_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MAdmin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MAdmin whereDepartment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MAdmin whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MAdmin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MAdmin whereMobile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MAdmin whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MAdmin wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MAdmin whereRoleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MAdmin whereSex($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MAdmin whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int $status
 * @property-read \App\Models\MRole $role
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MAdmin whereStatus($value)
 */
class MAdmin extends Authenticatable implements AuthenticatableUserContract
{

    use  SoftDeletes;
    protected $guarded=[
        'id'
    ];
    protected $hidden=[
        'password',
    ];
    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();  // Eloquent model method
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'user' => [
                'id' => $this->id,
            ]
        ];
    }
    public function role(){
        return $this->belongsTo(MRole::class,'role_id');
    }
}