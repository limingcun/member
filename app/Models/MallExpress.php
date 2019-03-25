<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * App\Models\MallExpress
 *
 * @property int $id
 * @property string $shipper 配送公司
 * @property string $shipper_code 配送公司代码
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallExpress whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallExpress whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallExpress whereShipper($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallExpress whereShipperCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallExpress whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MallExpress extends Model
{
    protected $guarded=['id'];
}
