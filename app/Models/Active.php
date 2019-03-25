<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use function time;

/**
 * App\Models\Active
 *
 * @property int $id
 * @property string $name 活动名
 * @property string|null $policy 优惠券领券策略
 * @property array $policy_rule 策略规则
 * @property int $shop_limit 门店限制
 * @property int $coupon_id 优惠券id
 * @property \Carbon\Carbon|null $period_start 有效期初始时间
 * @property \Carbon\Carbon|null $period_end 有效期结束时间
 * @property string|null $deleted_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Active onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active whereCouponId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active wherePeriodEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active wherePeriodStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active wherePolicy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active wherePolicyRule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active whereShopLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Active withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Active withoutTrashed()
 * @mixin \Eloquent
 * @property int $message 消息提醒(0无1微信消息)
 * @property int $status 活动状态（0关闭1开启）
 * @property string|null $remark 规则描述
 * @property-read \App\Models\Coupon $coupon
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Shop[] $shop
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active whereStatus($value)
 * @property int $user_limit 门店限制
 * @property int $total_freq 一共几次
 * @property int $day_freq 每天几次
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active whereDayFreq($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active whereTotalFreq($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active whereUserLimit($value)
 * @property string|null $no 活动编号
 * @property int $period_type 有效时间1为限定范围内有效，2为永久有效
 * @property int $admin_id 后台用户id
 * @property int $type 优惠类型（1优惠券2下单优惠）
 * @property-read \App\Models\Admin $admin
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ActiveJoin[] $join
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active whereNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active wherePeriodType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active whereType($value)
 * @property string|null $erp_no erp活动编码
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active whereErpNo($value)
 * @property int $use_limit 活动使用场景,0表示全部可用,1表示仅限自取,2表示仅限外卖
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active whereUseLimit($value)
 */
class Active extends Model
{
    const STATUS = [
        'tostart' => 0, //待开始
        'starting' => 1, //进行中
        'pause' => 2, //已暂停
        'finish' => 3  //已完成
    ];
    const PERIOD = [
        'fixed' => 1,  //活动固定失效
        'relative' => 2  //活动相对失效
    ];
    const USERLIMIT = [
        'all' => 0, //全部用户
        'spec' => 1, //输入框指定用户
        'excel' => 2 //excel表格导入指定用户
    ];

    use SoftDeletes;
    protected $table = 'actives';

    protected $hidden=[
        'deleted_at',
        'updated_at',
    ];
    protected $dates = [
        'period_start',
        'period_end',
    ];
    protected $casts = [
        'policy_rule' => 'array',
    ];
    protected $fillable = [
        'no',
        'name',
        'policy',
        'policy_rule',
        'shop_limit',
        'user_limit',
        'use_limit',
        'coupon_id',
        'period_type',
        'period_start',
        'period_end',
        'day_freq',
        'total_freq',
        'message',
        'remark',
        'status',
        'type',
        'admin_id',
        'erp_no',
    ];

//    public function getStatusAttribute($value)
//    {
////        Carbon::now()->timestamp
//        $period_end = $this->getAttribute('period_end');
//        if ($period_end->timestamp < time()) {
//            return 3;
//        }
//        return $value;
//    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function shop()
    {
        return $this->belongsToMany(Shop::class, 'active_shop');
    }

    public function user()
    {
        return $this->belongsToMany(User::class, 'active_user');
    }

    public function admin()
    {
        return $this->belongsTo(istoreUpmsUser::class);
    }

    public function join() {
        return $this->hasMany(ActiveJoin::class);
    }
}
