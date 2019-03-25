<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Wallet extends Model
{
    use SoftDeletes;
    protected $table = 'wallets';
    protected $dates = ['deleted_at'];  //开启deleted_at
    protected $fillable = ['cash', 'way', 'way_rech', 'way_free', 'way_limit', 'period_type', 'period_start', 'period_end', 'period_day'];
}
