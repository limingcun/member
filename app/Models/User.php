<?php

namespace App\Models;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * App\Models\User
 *
 * @property int $id
 * @property string $name 用户名
 * @property string|null $email 邮箱
 * @property string|null $phone
 * @property string|null $birthday
 * @property int $avatar_id 头像id
 * @property string|null $image_url 微信头像url
 * @property string $sex 性别
 * @property string|null $wxlite_session_key 小程序session
 * @property string|null $wxlite_open_id 小程序openid
 * @property string|null $wx_union_id 微信unionid
 * @property string $password 密码
 * @property \Carbon\Carbon|null $last_login_at 最后登录时间
 * @property string|null $remember_token
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Address[] $addresses
 * @property-read mixed $avatar
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Member[] $members
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Order[] $orders
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PointLog[] $pointLogs
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\MemberScore[] $score
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereAvatarId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereBirthday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereLastLoginAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereSex($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereWxUnionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereWxliteOpenId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereWxliteSessionKey($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User withoutTrashed()
 * @mixin \Eloquent
 * @property int $is_vip 测试用户字段
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CouponLibrary[] $library
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereIsVip($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\MallOrder[] $mallOrder
 * @property string|null $jpush_id 极光推送设备号id
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereJpushId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\GiftRecord[] $giftRecord
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\VkaRecord[] $vkaRecord
 */
class User extends Authenticatable implements JWTSubject
{
    use Notifiable, SoftDeletes;

    protected $guarded = [
        'id'
    ];

    protected $dates = ['last_login_at'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'wxlite_open_id',
        'wx_union_id', 'image_url', 'deleted_at', 'wxlite_session_key'
    ];

    // jwt 需要实现的方法
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    // jwt 需要实现的方法, 一些自定义的参数
    public function getJWTCustomClaims()
    {
        return ['source' => 'api'];
    }

    public function getAvatarAttribute($value)
    {
        return $value ?: url('images/istore.jpg');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function members()
    {
        return $this->hasMany(Member::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function pointLogs()
    {
        return $this->hasMany(PointLog::class);
    }
    public function score()
    {
        return $this->hasMany(MemberScore::class);
    }

    /**
     * 优惠券
     */
    public function library(){
        return $this->hasMany(CouponLibrary::class);
    }

    /*
     * 兑换订单
     */
    public function mallOrder() {
        return $this->hasMany(MallOrder::class);
    }

    public function vkaRecord() {
        return $this->hasMany(VkaRecord::class);
    }

    /**
     * 礼包
    */
    public function giftRecord()
    {
        return $this->hasMany(GiftRecord::class);
    }

    /**
     * 会员卡
     */
    public function memberCardRecord()
    {
        return $this->hasMany(MemberCardRecord::class);
    }
    
    /**
     * 意见反馈
     */
    public function comment() {
        return $this->hasMany(Comment::class);
    }
    
    /**
     * 储值
     */
    public function storage() {
        return $this->hasOne(CashStorage::class);
    }

    /**
     * 用户储值明细情况
     */
    public function bill() {
        return $this->hasMany(CashFlowBill::class);
    }
    
    /**
     * 消息推送
     */
    public function message() {
        return $this->hasMany(Message::class);
    }
    
    public function wechatFormId() {
        return $this->hasMany(WechatFormId::class);
    }
}
