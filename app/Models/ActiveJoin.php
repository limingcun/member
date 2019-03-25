<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\ActiveJoin
 *
 * @property int $id
 * @property int $user_id 用户id
 * @property int $order_id 订单id
 * @property int $active_id 活动id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ActiveJoin whereActiveId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ActiveJoin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ActiveJoin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ActiveJoin whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ActiveJoin whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ActiveJoin whereUserId($value)
 * @mixin \Eloquent
 * @property float $discount_fee 优惠金额
 * @property string|null $deleted_at
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\ActiveJoin onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ActiveJoin whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ActiveJoin whereDiscountFee($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\ActiveJoin withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\ActiveJoin withoutTrashed()
 * @property-read \App\Models\Active $active
 */
class ActiveJoin extends Model
{
    use SoftDeletes;
    protected $table = 'active_joins';
    protected $fillable = [
        'user_id',
        'order_id',
        'active_id',
        'discount_fee',
    ];
    public function active(){
        return $this->belongsTo(Active::class);
    }
}
