<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\VkaRecord
 *
 * @property int $id
 * @property int $user_id 用户ID
 * @property string $card_no 卡号
 * @property int $status 迁移状态
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VkaRecord whereCardNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VkaRecord whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VkaRecord whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VkaRecord whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VkaRecord whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VkaRecord whereUserId($value)
 * @mixin \Eloquent
 */
class VkaRecord extends Model
{
    protected $table = 'vka_records';
    protected $fillable = ['user_id', 'card_no', 'status'];
}
