<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\MallOrderLock
 *
 * @property int $id
 * @property int $user_id
 * @property int $mall_product_id
 * @property int $mall_sku_id
 * @property int $mall_order_id 0表示未被使用
 * @property string|null $expire_at 锁定过期时间
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderLock whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderLock whereExpireAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderLock whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderLock whereMallOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderLock whereMallProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderLock whereMallSkuId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderLock whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderLock whereUserId($value)
 * @mixin \Eloquent
 * @property int $status 1可使用2已使用3已失效
 * @property-read \App\Models\MallProduct $product
 * @property-read \App\Models\MallSku $sku
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderLock whereStatus($value)
 */
class MallOrderLock extends Model
{
    const STATUS_USEFUL = 1; //可用
    const STATUS_USED = 2; //已使用
    const STATUS_INVALID = 3; //失效
    const STATUS_CANCEL = 4; //失效
    protected $guarded = [
        'id'
    ];
    protected $dates=[
        'expire_at',
    ];

    public function sku(){
        return $this->belongsTo(MallSku::class,'mall_sku_id');
    }

    public function product(){
        return $this->belongsTo(MallProduct::class,'mall_product_id');
    }
}
