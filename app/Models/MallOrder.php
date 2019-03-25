<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\MallOrder
 *
 * @property int $id
 * @property string $no 积分商城订单号
 * @property int $user_id 用户id
 * @property int $score 兑换所需要积分
 * @property int $status 订单状态,1代表兑换成功，2代表兑换失败
 * @property string $exchange_time 兑换时间
 * @property string $remark 备注
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \App\Models\MallOrderItem $item
 * @property-read \App\Models\User $user
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MallOrder onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrder whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrder whereExchangeTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrder whereNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrder whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrder whereScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrder whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrder whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrder whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MallOrder withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MallOrder withoutTrashed()
 * @mixin \Eloquent
 * @property string|null $form_id
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrder whereFormId($value)
 * @property-read \App\Models\Member $member
 * @property int $is_express 是否有配送信息
 * @property-read \App\Models\MallOrderExpress $express
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrder whereIsExpress($value)
 * @property int $mall_type 商品类型1为虚拟商品，2为实体商品
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrder whereMallType($value)
 * @property string|null $refund_reason 退单原因
 * @property string|null $origin_from 商城订单来源，IOS是苹果，MINI是小程序
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrder whereOriginFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrder whereRefundReason($value)
 */
class MallOrder extends Model
{
    const STATUS = [
        'success' => 1,  //兑换成功
        'fail' => 2,  //兑换失败
        'wait_dispatch' => 3,  //待发货
        'dispatching' => 4,  //已发货
        'finish' => 5,  //已签收（完成）
        'refund' => 6,  //已退单
    ];
    const MALLTYPE = [
        'invent' => 1, //虚拟
        'real' => 2 //真实
    ];
    use SoftDeletes;
    protected $dates = [
        'deleted_at'
    ];
    protected $guarded=[
        'id'
    ];
    protected $table = 'mall_orders';
    
    public function item() {
        return $this->hasOne(MallOrderItem::class);
    }
    
    public function user() {
        return $this->belongsTo(User::class);
    }
    public function member(){
        return $this->belongsTo(Member::class,'user_id','user_id');
    }
    public function express(){
        return $this->hasOne(MallOrderExpress::class);
    }
}
