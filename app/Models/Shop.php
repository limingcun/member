<?php

namespace App\Models;

use Location\Coordinate;
use EloquentFilter\Filterable;
use Location\Distance\Vincenty;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * App\Models\Shop
 *
 * @property int $id
 * @property string|null $code
 * @property string $name
 * @property int $cover_pic_id
 * @property int|null $outer_id
 * @property string $no
 * @property bool $is_actived
 * @property string $contact_phone
 * @property string $contact_name
 * @property string $province
 * @property string $city
 * @property string $district
 * @property string $address
 * @property string $city_code
 * @property string $latitude
 * @property string $longitude
 * @property int $before_minutes
 * @property mixed $days_of_week
 * @property int $time_interval
 * @property int $unit_box_seconds
 * @property int $unit_box_shares
 * @property string $open_at
 * @property string $close_at
 * @property string|null $last_operated_at
 * @property bool $support_takeaway
 * @property string $scene_code 小程序码
 * @property string $qrcode 小程序二维码
 * @property string $tips 门店提示语
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\Image $cover
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Order[] $orders
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop filter($input = array(), $filter = null)
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Shop onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop paginateFilter($perPage = null, $columns = array(), $pageName = 'page', $page = null)
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop simplePaginateFilter($perPage = null, $columns = array(), $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereBeforeMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereCityCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereCloseAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereContactName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereContactPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereCoverPicId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereDaysOfWeek($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereDistrict($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereIsActived($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereLastOperatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereOpenAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereOuterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereProvince($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereQrcode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereSceneCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereSupportTakeaway($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereTimeInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereTips($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereUnitBoxSeconds($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereUnitBoxShares($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Shop withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Shop withoutTrashed()
 * @mixin \Eloquent
 * @property string|null $address_keyword 关键字
 * @property int $is_delivery 是否支持外卖
 * @property int $is_enable 门店启停1为启用
 * @property int $min_charge 起送价
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereAddressKeyword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereIsDelivery($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereIsEnable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereMinCharge($value)
 * @property int $is_open 门店状态标签0：敬请期待,1:已经开启门店
 * @property int $delivery_distance 外卖配送距离
 * @property float $delivery_fee 外卖配送费
 * @property string $delivery_close_at 外卖结束时间
 * @property string $delivery_open_at 外卖开始时间
 * @property int $support_mt_takeaway 是否支持美团外卖
 * @property int $support_sf_takeaway 是否支持顺丰外卖
 * @property int $takeaway_status 外卖状态0关1开
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereDeliveryCloseAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereDeliveryDistance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereDeliveryFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereDeliveryOpenAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereIsOpen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereSupportMtTakeaway($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereSupportSfTakeaway($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereTakeawayStatus($value)
 * @property int $policy_id 策略ID
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop wherePolicyId($value)
 */
class Shop extends Authenticatable implements JWTSubject
{
    use Filterable, SoftDeletes;
    
    const HK_CITY_CODE = 1852; // 香港城市编码

    public static $base=[
        'id',
        'name',
        'province',
        'city',
    ];
    protected $fillable = [
        'no', 'name', 'cover_pic_id', 'province', 'city', 'district', 'address',
        'city_code', 'latitude', 'longitude', 'open_at', 'close_at', 'contact_phone',
        'contact_name', 'code', 'days_of_week', 'time_interval', 'unit_box_seconds', 'min_charge',
        'unit_box_shares', 'support_takeaway', 'scene_code', 'qrcode', 'tips', 'address_keyword',
        'is_enable', 'is_actived','delivery_distance','delivery_fee','delivery_close_at','delivery_open_at',
        'support_mt_takeaway','support_sf_takeaway', 'is_open', 'takeaway_last_operate_at', 'takeaway_status', 'cup_limit'
    ];

    protected $casts = [
        'is_actived' => 'boolean',
        'support_takeaway' => 'boolean',
    ];


    protected $hidden = ['deleted_at'];

    public function cover()
    {
        return $this->belongsTo(Image::class, 'cover_pic_id', 'id');
    }

    public function getDistance($longitude, $latitude)
    {
        $location = new Coordinate($latitude, $longitude);

        $shopLocation = new Coordinate($this->latitude, $this->longitude);

        return (int)$location->getDistance($shopLocation, new Vincenty());
    }



    public function orders()
    {
        return $this->hasMany(Order::class);
    }


    /**
     * 门店与商品下架关联表
     */

    // jwt 需要实现的方法
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    // jwt 需要实现的方法, 一些自定义的参数
    public function getJWTCustomClaims()
    {
        return ['source' => 'shop'];
    }
}
