<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\MallProduct
 *
 * @property int $id
 * @property string $name 积分商城商品名称
 * @property int $score 所需兑换积分
 * @property int $store 库存
 * @property int $limit_purchase 限购数量
 * @property string $source_type 积分商城商品策略
 * @property int $source_id 积分商城商品策略规则
 * @property string|null $remark 商品说明
 * @property int $status 商品状态, 1代表已上架,2代表已下架
 * @property string|null $shelf_time 上架时间
 * @property string|null $no 商品编码id
 * @property int $sold_count 销量
 * @property int $mall_type 商品类型1为虚拟商品，2为实体商品
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Image[] $images
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $source
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MallProduct onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallProduct whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallProduct whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallProduct whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallProduct whereLimitPurchase($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallProduct whereMallType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallProduct whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallProduct whereNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallProduct whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallProduct whereScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallProduct whereShelfTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallProduct whereSoldCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallProduct whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallProduct whereSourceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallProduct whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallProduct whereStore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallProduct whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MallProduct withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MallProduct withoutTrashed()
 * @mixin \Eloquent
 * @property int $sort 产品排序
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallProduct whereSort($value)
 * @property-read \App\Models\CouponLibrary $library
 * @property int $is_specification 是否多规格
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\MallSku[] $skus
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\MallSpecification[] $specification
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallProduct whereIsSpecification($value)
 * @property string|null $no_code 商品编码
 * @property array $specification_sort 规格排序字段
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallProduct whereNoCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallProduct whereSpecificationSort($value)
 */
class MallProduct extends Model
{
    const STATUS = [
        'wait' => 0, //待上架
        'takeup' => 1, //上架
        'takedown' => 2 //下架
    ];
    const MALLTYPE = [
        'invent' => 1, //虚拟
        'real' => 2 //真实
    ];
    const PERIODTYPE = [
        'fixed' => 0, //固定过期
        'positon' => 1 //相对过期
    ];
    const SPCIFICATION = [
        'single' => 0, //单规格
        'more' => 1  //多规格 
    ];

    use SoftDeletes;
    protected $dates = ['deleted_at'];  //开启deleted_at
    protected $table = 'mall_products';
    protected $casts = [
        'policy_rule' => 'array',
        'specification_sort' => 'array',
    ];

    protected $fillable = [
        'no',
        'no_code',
        'name',
        'source_type',
        'source_id',
        'score',
        'store',
        'status',
        'limit_purchase',
        'mall_type',
        'remark',
        'is_specification',
        'specification_sort',
        'sold_count',
        'sort',
        'member_type'
    ];

    public function images()
    {
        return $this->belongsToMany(Image::class, 'mall_images', 'mall_product_id', 'image_id');
    }

    public function source()
    {
        return $this->morphTo();
    }

    public function specification()
    {
        return $this->hasMany(MallSpecification::class);
    }

    public function skus()
    {
        return $this->hasMany(MallSku::class);
    }

    public function library()
    {
        return $this->belongsTo(CouponLibrary::class, 'source_id', 'coupon_id');
    }

    /**
     * 判断是否达到限购数量
     * @param $userId
     * @return bool
     */
    public function limitPurchase($userId)
    {
        if (!$this->limit_purchase) {
            return true;
        }
        if ($this->limit_purchase <= MallOrderItem::join('mall_orders', 'mall_orders.id', '=', 'mall_order_items.mall_order_id')
                ->where('mall_product_id', $this->id)
                ->where('user_id', $userId)->count()) {
            return false;
        }
        return true;
    }
}
