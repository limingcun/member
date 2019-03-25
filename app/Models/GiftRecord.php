<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\GiftRecord
 *
 * @property int $id
 * @property int $user_id 用户ID
 * @property string $name 礼包名称
 * @property int $gift_type 礼包类型
 * @property int $level_id 升级时用户的会员等级ID
 * @property int $star_level_id 升级时用户的星球会员等级ID
 * @property string|null $pick_at 礼包领取时间
 * @property string $overdue_at 礼包过期时间
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property string $start_at 礼包开始时间(大于等于此日期才能领取该礼包)
 * @property-read \App\Models\Level $level
 * @property-read \App\Models\StarLevel $startLevel
 * @property-read \App\Models\User $user
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\GiftRecord onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GiftRecord whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GiftRecord whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GiftRecord whereGiftType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GiftRecord whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GiftRecord whereLevelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GiftRecord whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GiftRecord whereOverdueAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GiftRecord wherePickAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GiftRecord whereStarLevelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GiftRecord whereStartAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GiftRecord whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GiftRecord whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\GiftRecord withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\GiftRecord withoutTrashed()
 * @mixin \Eloquent
 */
class GiftRecord extends Model
{
    const GIFT_TYPE = [
        'go_update_cash' => 1,           // go会员升级礼包 现金券
        'go_update_buy_fee' => 2,        // go会员升级礼包 满赠券
        'star_update' => 3,         // 星球会员升级瞬间礼包
        'star_monthly_welfare' => 4,    // 星球会员每月福利
//        'star_first_charge' => 5,   // 星球会员首充礼包
//        'star_birthday' => 6,       // 星球会员生日好礼
//        'star_prime_day' => 7,      // 星球会员会员日礼包
    ];
    const STATUS = [
        'new' => 0,
        'read' => 1
    ];

    use SoftDeletes;
    protected $table = 'gift_records';
    protected $dates = ['deleted_at'];
    protected $fillable = ['id', 'user_id', 'name', 'gift_type', 'level_id', 'star_level_id', 'pick_at', 'overdue_at', 'start_at', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function starLevel()
    {
        return $this->belongsTo(StarLevel::class, 'star_level_id');
    }
}
