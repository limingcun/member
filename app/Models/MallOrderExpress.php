<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * App\Models\MallOrderExpress
 *
 * @property int $id
 * @property int $mall_order_id
 * @property string $shipper 配送公司
 * @property string $shipper_code 配送公司代码
 * @property string $no 订单号
 * @property string $name
 * @property string $phone
 * @property string $address
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderExpress whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderExpress whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderExpress whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderExpress whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderExpress whereMallOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderExpress whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderExpress whereNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderExpress wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderExpress whereShipper($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderExpress whereShipperCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderExpress whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int $address_id
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderExpress whereAddressId($value)
 * @property array $trace 快递路由
 * @property-read \App\Models\MallOrder $order
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderExpress whereTrace($value)
 */
class MallOrderExpress extends Model
{
    protected $guarded=['id'];
    protected $casts=[
        'trace'=>'array'
    ];
    protected $hidden=[
        'trace',
        'updated_at',
        'deleted_at',
    ];
    public function order(){
        return $this->belongsTo(MallOrder::class,'mall_order_id');
    }
}
