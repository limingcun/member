<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\MiniUser
 *
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MiniUser onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MiniUser withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MiniUser withoutTrashed()
 * @mixin \Eloquent
 */
class MiniUser extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];  //开启deleted_at
    protected $table = 'mini_users';
    protected $guarded = ['id'];
}
