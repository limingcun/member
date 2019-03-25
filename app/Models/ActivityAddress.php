<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActivityAddress extends Model
{
    use SoftDeletes;
    protected $table = 'activity_addresses';
    protected $dates = ['deleted_at'];
    protected $fillable = ['id', 'user_id', 'name', 'phone', 'address', 'type', 'status', 'remarks'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
