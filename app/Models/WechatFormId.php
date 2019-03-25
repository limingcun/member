<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WechatFormId extends Model
{
    use SoftDeletes;
    protected $table = 'mini_formids';
    protected $dates = ['deleted_at'];  //开启deleted_at

    protected $fillable = ['id', 'formid', 'open_id', 'user_id', 'is_used', 'used_at'];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
