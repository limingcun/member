<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CouponCodeRecord extends Model
{
    protected $table = 'coupon_code_records';
    
    protected $fillable = [
        'id',
        'grand_id',
        'outer_name',
        'outer_time'
    ];

    public function grand()
    {
        return $this->belongsTo(CouponGrand::class);
    }
}
