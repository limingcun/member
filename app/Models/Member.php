<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
/**
 * App\Models\Member
 *
 * @property int $id
 * @property int $user_id 用户id
 * @property int $avatar_id 头像id
 * @property string $name 姓名
 * @property string|null $email 邮箱
 * @property string|null $phone 电话
 * @property int $points
 * @property string|null $position
 * @property string|null $birthday
 * @property string $type
 * @property string $status
 * @property string $sex
 * @property int $order_count
 * @property float $order_money
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereAvatarId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereBirthday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereOrderCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereOrderMoney($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member wherePoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereSex($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereUserId($value)
 * @mixin \Eloquent
 * @property int $order_score 累计总积分
 * @property int $used_score 已使用积分
 * @property int $usable_score 可用积分
 * @property int $level_id 会员等级
 * @property int $exp 成长值
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Order[] $order
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Member onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereExp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereLevelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereOrderScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereUsableScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereUsedScore($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Member withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Member withoutTrashed()
 * @property-read \App\Models\Level $level
 * @property int $new_coupon_tab 最新优惠券标记
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereNewCouponTab($value)
 * @property int $score_lock 会员锁定，0为未锁，1为锁定
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereScoreLock($value)
 * @property string|null $member_no 会员编号
 * @property string|null $expire_time 会员过期时间
 * @property string|null $star_time 注册星球会员时间
 * @property int $member_cup 星球等级购买杯数计算
 * @property int $member_type 0表示go会员,1表示星球会员,2表示vka迁移会员
 * @property int $star_level_id 星球会员等级id
 * @property int $star_exp 星球会员经验值
 * @property-read \App\Models\StarLevel $starLevel
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereExpireTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereMemberCup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereMemberNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereMemberType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereStarExp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereStarLevelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereStarTime($value)
 */
class Member extends Model
{
    const SCORELOCT = [
        'unlock' => 0, //不锁定
        'lock' => 1  //锁定
    ];
    const NEWTAB = [
        'scan' => 0, //查看过后新标志
        'new' => 1 //优惠券新标志
    ];
    const MEMBERTYPE = [
        'go' => 0, //go会员
        'star' => 1, //星球会员
        'vka' => 2 //vka会员
    ];
    

    use SoftDeletes;
    protected $table = 'members';
    public static $score=[
        'id','user_id', 'name', 'order_money', 'phone',
        'order_score', 'used_score', 'usable_score'
    ];
    protected $dates = ['deleted_at'];  //开启deleted_at

    protected $fillable = ['user_id', 'avatar_id', 'name', 'email', 'phone', 'points', 'position', 'birthday', 'type', 'star_time',
        'level_id', 'exp', 'member_no', 'expire_time', 'status', 'sex', 'order_count', 'order_money', 'order_score',
        'member_type', 'used_score', 'usable_score', 'new_coupon_tab', 'score_lock', 'star_level_id', 'star_exp', 'message_tab'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function order() {
        return $this->hasMany(Order::class)->select(array('member_id', 'paid_at'))->whereIn('status',['TRADE_CLOSED', 'BUYER_PAY', 'WAIT_PRINT']);
    }

    public function level() {
        return $this->belongsTo(Level::class);
    }

    public function starLevel() {
        return $this->belongsTo(StarLevel::class, 'star_level_id');
    }
    
    public function storage(){
        return $this->hasOne(CashStorage::class,'user_id','user_id');
    }
    /**
     * 判断是星球会员还是Go会员
     * $member会员
     */
    public static function isStarMember(Member $member) {
        if (!$member->expire_time) {
            return false;
        } else {
            if (Carbon::parse($member->expire_time)->timestamp >= Carbon::today()->timestamp) {
                return true;
            } else {
                return false;
            }
        }
    }
}
