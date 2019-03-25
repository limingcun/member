<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\CouponType
 *
 * @property int $id
 * @property string $key 优惠劵类型标识
 * @property string $name 优惠劵类型名称
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponType whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponType whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponType whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CouponType extends Model
{
    protected $table = 'coupon_types';
}
