<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CardCodeOrder extends Model
{
    const STATUS = [
        'cancel' => 0,
        'unused' => 1,  // 未被导出过
        'used' => 2 // 已导出过
    ];

    use SoftDeletes;
    protected $table = 'card_code_orders';
    protected $dates = ['deleted_at'];
    protected $fillable = ['id', 'user_id', 'name', 'phone', 'email', 'address', 'price', 'count',
        'card_type', 'period_start', 'period_end', 'status', 'admin_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
