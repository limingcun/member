<?php

namespace App\Models;

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
 * @property int $exp_min 最低成长值
 * @property int $exp_max 最高成长值
 * @property int $exp_deduction 成长值扣除
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Level onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Level whereExpDeduction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Level whereExpMax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Level whereExpMin($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Level withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Level withoutTrashed()
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Member[] $member
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\GiftRecord[] $giftRecord
 */
class Level extends Model
{
    use SoftDeletes;
    protected $table = 'levels';
    protected $dates = ['deleted_at'];  //开启deleted_at
    protected $fillable = ['name', 'exp_min', 'exp_max', 'exp_deduction'];

    public function member() {
        return $this->hasMany(Member::class);
    }

    public function giftRecord()
    {
        return $this->hasMany(GiftRecord::class);
    }
}
