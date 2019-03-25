<?php

namespace App\Models;

use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject as AuthenticatableUserContract;

/**
 * App\Models\Admin
 *
 * @property int $id
 * @property int $user_id
 * @property int $avatar_id
 * @property string $wechat_userid
 * @property string $name
 * @property string|null $english_name
 * @property string|null $email
 * @property string $password
 * @property string|null $position
 * @property string $sex
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\Image $avatar
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Permission\Models\Permission[] $permissions
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Permission\Models\Role[] $roles
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Admin onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Admin permission($permissions)
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Admin role($roles)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Admin whereAvatarId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Admin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Admin whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Admin whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Admin whereEnglishName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Admin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Admin whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Admin wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Admin wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Admin whereSex($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Admin whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Admin whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Admin whereWechatUserid($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Admin withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Admin withoutTrashed()
 * @mixin \Eloquent
 * @property string|null $mobile
 * @property bool $can_scan
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Admin whereCanScan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Admin whereMobile($value)
 * @property string $images 用户头像
 * @property string $username 用户账号
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Admin whereImages($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Admin whereUsername($value)
 */
class Admin extends Authenticatable implements AuthenticatableUserContract
{
    use HasRoles;
//    , SoftDeletes;
    protected $table = 'istore_upms_user_t';
    protected $guard_name = 'admin';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'english_name', 'avatar_id', 'wechat_userid', 'email', 'password',
        'wechat_userid', 'mobile', 'can_scan',
    ];
    public static $base = [
        'admins.id',
        'admins.name',
    ];

    protected $casts = [
        'can_scan' => 'boolean',
    ];

    /**
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();  // Eloquent model method
    }

    /**
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'user' => [
                'id' => $this->id,
                'wechat_userid' => $this->wechat_userid,
            ]
        ];
    }

    public function avatar()
    {
        return $this->belongsTo(Image::class, 'avatar_id', 'id');
    }

    public function isSuperAdmin()
    {
        return $this->id == config('app.super_admin');
    }
}
