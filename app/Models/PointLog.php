<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PointLog
 *
 * @property int $id
 * @property int $user_id
 * @property int $member_id
 * @property int $pointable_id
 * @property string $pointable_type
 * @property int $points
 * @property string|null $description
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $pointable
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PointLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PointLog whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PointLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PointLog whereMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PointLog wherePointableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PointLog wherePointableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PointLog wherePoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PointLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PointLog whereUserId($value)
 * @mixin \Eloquent
 * @property int $shop_id
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PointLog whereShopId($value)
 */
class PointLog extends Model
{
    protected $guarded = ['id'];

    public function pointable()
    {
        return $this->morphTo();
    }
}
