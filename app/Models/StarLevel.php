<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


/**
 * App\Models\StarLevel
 *
 * @property int $id
 * @property string $name 等级名称
 * @property int $exp_min 最低成长值
 * @property int $exp_max 最高成长值
 * @property int $exp_deduction 成长值扣除
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\GiftRecord[] $giftRecord
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Member[] $member
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $user
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\StarLevel onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StarLevel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StarLevel whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StarLevel whereExpDeduction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StarLevel whereExpMax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StarLevel whereExpMin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StarLevel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StarLevel whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StarLevel whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\StarLevel withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\StarLevel withoutTrashed()
 * @mixin \Eloquent
 */
class StarLevel extends Model
{
    use SoftDeletes;
    protected $table = 'star_levels';
    protected $dates = ['deleted_at'];  //开启deleted_at
    protected $fillable = ['name', 'exp_min', 'exp_max', 'exp_deduction'];

    public function member() {
        return $this->hasMany(Member::class);
    }

    public function user() {
        return $this->belongsToMany(User::class, 'members', 'star_level_id', 'user_id');
    }

    public function giftRecord()
    {
        return $this->hasMany(GiftRecord::class);
    }
}
