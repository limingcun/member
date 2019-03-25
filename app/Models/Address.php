<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Address
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $phone
 * @property string $sex
 * @property string $address
 * @property string $description
 * @property string $latitude
 * @property string $longitude
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\User $user
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Address onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Address whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Address whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Address whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Address whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Address whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Address whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Address whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Address whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Address wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Address whereSex($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Address whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Address whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Address withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Address withoutTrashed()
 * @mixin \Eloquent
 * @property string|null $complete_address 包含省市区详细地址
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Address whereCompleteAddress($value)
 */
class Address extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $hidden = ['deleted_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
