<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
/**
 * App\Models\CouponGrand
 *
 * @property int $id
 * @property int $coupon_template_id 对应的模板ID
 * @property string $time_grand 放发时间
 * @property int $status 发放状态(以发放时间界定，0-未发放、1-发放中、2-已截止)
 * @property int $coupon_draw_id 领取方式ID
 * @property int $count 发放数量(0-根据用户范围动态数量、N-指定数量)
 * @property string $member_range 用户范围JSON({"global":0}/{"global":1, "sex":1, "age":18})
 * @property string $draw_rules 领取规则({''days'': 1, ''counts'': 1}，表示每人每天最多领取一张)
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CouponTemplate[] $template
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\CouponGrand onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponGrand whereCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponGrand whereCouponDrawId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponGrand whereCouponTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponGrand whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponGrand whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponGrand whereDrawRules($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponGrand whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponGrand whereMemberRange($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponGrand whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponGrand whereTimeGrand($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponGrand whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\CouponGrand withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\CouponGrand withoutTrashed()
 * @mixin \Eloquent
 * @property int $coupon_id 优惠券id
 * @property string|null $grand_time 发券时间
 * @property int $grand_type 1为立即发券，2为指定时间发券
 * @property int $scence 使用场景
 * @property int $admin_id 发券人
 * @property int $chanel_type 触达渠道
 * @property int $range_type 0为全部用户,1为指定用户,2为导入excel
 * @property-read \App\Models\Admin $admin
 * @property-read \App\Models\Coupon $coupon
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponGrand whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponGrand whereChanelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponGrand whereCouponId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponGrand whereGrandTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponGrand whereGrandType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponGrand whereRangeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponGrand whereScence($value)
 * @property int $page 延迟发券页码
 * @property string|null $range_msg 线下指派对象
 * @property int|null $amount 线下派发优惠券数量
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponGrand whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponGrand wherePage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponGrand whereRangeMsg($value)
 * @property string|null $name 活动名称
 * @property string|null $no 发券活动id编码
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CouponLibrary[] $library
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponGrand whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponGrand whereNo($value)
 */
class CouponGrand extends Model
{
    const TIMEUNIT = [
        'day' => 0, //天
        'month' => 1, //月
        'year' => 2 //年
    ];

    const GRANDSTATUS = [
        'ungrand' => 0, //未发放
        'granding' => 1, //发放中
        'pause' => 2, //已暂停
        'finish' => 3, //已完成
        'period' => 4 //已过期
    ];

    const GRANDTYPE = [
        'once' => 1, //立即发放
        'spec' => 2 //指定时间发放
    ];

    const SCENCE = [
        'line' => 0, //线上
        'change' => 1,  //兑换
        'qrcode' => 2  //二维码
    ];

    const RANGETYPE = [
        'all' => 0,  //全部用户
        'spec' => 1, //输入框输入用户id
        'excel' => 2 //excel表导入用户id
    ];

    use SoftDeletes;
    protected $dates = [
        'deleted_at',
        'grand_time'
    ];
    protected $table = 'coupon_grands';
    protected $fillable = ['id', 'status', 'coupon_id', 'grand_time', 'grand_type', 'scence', 'admin_id', 'chanel_type',
                           'range_type', 'range_msg', 'amount', 'count', 'name', 'no', 'period_type', 'period_start',
                           'period_end', 'period_day', 'unit_time'];


    public function coupon() {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }

    public function admin() {
        return $this->belongsTo(istoreUpmsUser::class);
    }

    public function mAdmin() {
        return $this->belongsTo(istoreUpmsUser::class);
    }

    /*
     * 状态码文字
     */
    public function statusText($value) {
        switch($value) {
            case self::GRANDSTATUS['ungrand']:
                return '待发放';
            case self::GRANDSTATUS['granding']:
                return '发放中';
            case self::GRANDSTATUS['pause']:
                return '已暂停';
            case self::GRANDSTATUS['finish']:
                return '已发券';
            default:
                return '已过期';
        }
    }

    /*
     * 发放场景文字
     */
    public function scenceText($value) {
        switch($value) {
            case self::SCENCE['line']:
                return '小程序';
            case self::SCENCE['change']:
                return '兑换码';
            default:
                return '二维码';
        }
    }

    public function library() {
        return $this->hasMany(CouponLibrary::class, 'coupon_id', 'coupon_id');
    }

    public function order() {
        return $this->belongsToMany(Order::class, 'coupon_librarys', 'id', 'order_id');
    }

    /*
     * 时间维度有效期
     */
    public static function getTimePeriod(CouponGrand $grand, $time = null) {
        $time = $time ?? Carbon::now();
        switch($grand->unit_time) {
            case self::TIMEUNIT['day']:
                return $time->addDays($grand->period_day)->endOfDay();
            case self::TIMEUNIT['month']:
                return $time->addMonths($grand->period_day)->endOfDay();
            case self::TIMEUNIT['year']:
                return $time->addYears($grand->period_day)->endOfDay();
            default:
                return null;
        }
    }

    public function grandNum() {
        return $this->hasMany(CouponLibrary::class, 'coupon_id', 'coupon_id')->where('status', '!=', CouponLibrary::STATUS['unpick'])->whereNotNull('created_at');
    }

    public function useNum() {
        return $this->hasMany(CouponLibrary::class, 'coupon_id', 'coupon_id')->where('status', CouponLibrary::STATUS['used'])->whereNotNull('created_at');
    }

    public function unuseNum() {
        return $this->hasMany(CouponLibrary::class, 'coupon_id', 'coupon_id')->whereIn('status', [CouponLibrary::STATUS['surplus'], CouponLibrary::STATUS['period']])->whereNotNull('created_at');
    }

    public function orderCoupon() {
        return $this->hasMany(CouponLibrary::class, 'coupon_id', 'coupon_id')->where('order_id', '!=', 0);
    }
}
