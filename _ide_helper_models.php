<?php
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * App\Models\Material
 *
 * @property int $id
 * @property string $no 编码
 * @property string $name 名称
 * @property float $price 价格
 * @property int $is_actived 判断是否启用加料
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Material onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Material whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Material whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Material whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Material whereIsActived($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Material whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Material whereNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Material wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Material whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Material withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Material withoutTrashed()
 * @mixin \Eloquent
 */
	class Material extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\MemberCardRecord
 *
 * @property int $id
 * @property int $user_id 用户ID
 * @property string $card_no 卡号
 * @property int $card_type 会员卡类型
 * @property float $price
 * @property string|null $period_start 有效期初始时间
 * @property string|null $period_end 有效期结束时间
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \App\Models\User $user
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MemberCardRecord onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberCardRecord whereCardNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberCardRecord whereCartType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberCardRecord whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberCardRecord whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberCardRecord whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberCardRecord wherePeriodEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberCardRecord wherePeriodStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberCardRecord wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberCardRecord whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberCardRecord whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MemberCardRecord withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MemberCardRecord withoutTrashed()
 * @mixin \Eloquent
 * @property int $cart_type 会员卡类型
 * @property string|null $prepay_id 支付平台订单号
 * @property string|null $order_no 会员卡购买订单号
 * @property string|null $trade_type 交易发起平台
 * @property int $status 支付状态 0待支付 1已支付 2已取消
 * @property int $paid_type 支付方式 1微信支付
 * @property string|null $paid_at 付款时间
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberCardRecord whereCardType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberCardRecord whereOrderNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberCardRecord wherePaidAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberCardRecord wherePaidType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberCardRecord wherePrepayId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberCardRecord whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberCardRecord whereTradeType($value)
 */
	class MemberCardRecord extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ScoreRule
 *
 * @property int $id
 * @property int $score 消费获得积分数[ 消费获得积分=floor(消费金额/rmb_base) ]
 * @property int $rmb_base 每消费RMB数
 * @property int $score_max_per_day 每人每天积分获取上限
 * @property int $months 积分有效月份（0-永久有效、N-N月后某个时间定期清0）
 * @property string $expiry_type 积分有效期类型(1-永不过期、2-定期清0)
 * @property string|null $expiry_time 指定N年后的具体失效日期(md格式，如1231表示，12月31日)
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ScoreRule whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ScoreRule whereExpiryTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ScoreRule whereExpiryType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ScoreRule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ScoreRule whereMonths($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ScoreRule whereRmbBase($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ScoreRule whereScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ScoreRule whereScoreMaxPerDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ScoreRule whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string|null $remind_type 默认0,1短信提醒，2微信服务通知提醒，两种类型用1,2逗号隔开
 * @property int $remind_time 过期前几天提醒
 * @property string|null $remind_msg 提醒文字
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ScoreRule whereRemindMsg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ScoreRule whereRemindTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ScoreRule whereRemindType($value)
 */
	class ScoreRule extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Category
 *
 * @property int $id
 * @property string $name
 * @property int $sort
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Category onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Category whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Category whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Category whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Category whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Category withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Category withoutTrashed()
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Product[] $products
 */
	class Category extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Member
 *
 * @property int $id
 * @property int $user_id 用户id
 * @property int $avatar_id 头像id
 * @property string $name 姓名
 * @property string|null $email 邮箱
 * @property string|null $phone 电话
 * @property int $points
 * @property string|null $position
 * @property string|null $birthday
 * @property string $type
 * @property string $status
 * @property string $sex
 * @property int $order_count
 * @property float $order_money
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereAvatarId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereBirthday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereOrderCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereOrderMoney($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member wherePoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereSex($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereUserId($value)
 * @mixin \Eloquent
 * @property int $exp_min 最低成长值
 * @property int $exp_max 最高成长值
 * @property int $exp_deduction 成长值扣除
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Level onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Level whereExpDeduction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Level whereExpMax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Level whereExpMin($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Level withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Level withoutTrashed()
 * @property string|null $expire_time 规则失效
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\LevelRule whereExpireTime($value)
 */
	class LevelRule extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\MemberScore
 *
 * @property int $id
 * @property int $user_id 用户ID
 * @property int $order_id 订单ID
 * @property int $order_score 订单积分(增加为+，减少为-)
 * @property int $method 积分方式（1-消费获得、2-活动获得、10-退款减少、11-兑换减少）
 * @property int $activity_id 如果积分获得方式为活动，则记录活动ID
 * @property int $usable_score 当前可用积分
 * @property int $total_score 当前总积分
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberScore ofWhen($id)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MemberScore onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberScore whereActivityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberScore whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberScore whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberScore whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberScore whereMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberScore whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberScore whereOrderScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberScore whereTotalScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberScore whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberScore whereUsableScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberScore whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MemberScore withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MemberScore withoutTrashed()
 * @mixin \Eloquent
 * @property int $source_id 关联id
 * @property string|null $source_type 订单类型
 * @property int $score_change 积分变动(增加为+，减少为-)
 * @property-read \App\Models\Order $order
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberScore whereScoreChange($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberScore whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberScore whereSourceType($value)
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $source
 * @property-read \App\Models\User $user
 * @property string|null $description
 * @property-read mixed $method_text
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberScore whereDescription($value)
 * @property int $origin 0表示小程序,1表示app
 * @property int $member_type 0表示go会员,1表示星球会员
 * @property-read \App\Models\Member $member
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberScore whereMemberType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MemberScore whereOrigin($value)
 */
	class MemberScore extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\MallOrderItem
 *
 * @property int $id
 * @property string $name 商品名称
 * @property int $mall_order_id 积分商城订单号id
 * @property int $mall_product_id 积分商城商品id
 * @property string $source_type 商品订单关联类型
 * @property string $source_id 商品订单关联id
 * @property string|null $remark 商品说明
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \App\Models\MallProduct $product
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MallOrderItem onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderItem whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderItem whereMallOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderItem whereMallProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderItem whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderItem whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderItem whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderItem whereSourceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MallOrderItem withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MallOrderItem withoutTrashed()
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $source
 * @property int $mall_sku_id sku_id
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderItem whereMallSkuId($value)
 */
	class MallOrderItem extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\VkaRecord
 *
 * @property int $id
 * @property int $user_id 用户ID
 * @property string $card_no 卡号
 * @property int $status 迁移状态
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VkaRecord whereCardNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VkaRecord whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VkaRecord whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VkaRecord whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VkaRecord whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VkaRecord whereUserId($value)
 * @mixin \Eloquent
 */
	class VkaRecord extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\MallOrderSku
 *
 * @property int $id
 * @property string|null $no 编号
 * @property int $mall_product_id
 * @property float $price 价格
 * @property int $stock 库存
 * @property string|null $specificationIds
 * @property string|null $deleted_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Models\MallProduct $product
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MallOrderSku onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderSku whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderSku whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderSku whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderSku whereMallProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderSku whereNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderSku wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderSku whereSpecificationIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderSku whereStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderSku whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MallOrderSku withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MallOrderSku withoutTrashed()
 * @mixin \Eloquent
 * @property int $store 库存
 * @property int $is_show
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallSku whereIsShow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallSku whereStore($value)
 * @property int $sort
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallSku whereSort($value)
 */
	class MallSku extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Wallet
 *
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Wallet onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Wallet withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Wallet withoutTrashed()
 */
	class Wallet extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\MPermission
 *
 * @property int $id
 * @property string $name
 * @property string $label
 * @property int $status
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MPermission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MPermission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MPermission whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MPermission whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MPermission whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MPermission whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class MPermission extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\MAdmin
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property int $sex
 * @property string $mobile
 * @property string $department
 * @property string $password
 * @property int $role_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MAdmin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MAdmin whereDepartment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MAdmin whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MAdmin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MAdmin whereMobile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MAdmin whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MAdmin wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MAdmin whereRoleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MAdmin whereSex($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MAdmin whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int $status
 * @property-read \App\Models\MRole $role
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MAdmin whereStatus($value)
 * @property string $no
 * @property string|null $deleted_at
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MAdmin onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MAdmin whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MAdmin whereNo($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MAdmin withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MAdmin withoutTrashed()
 */
	class MAdmin extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\RefundOrder
 *
 * @property int $id
 * @property int $admin_id
 * @property int $user_id 用户ID
 * @property int $order_id 订单ID
 * @property string $out_refund_no
 * @property string $refund_id 微信退款ID
 * @property float $payment 退款金额
 * @property string $status
 * @property string $reason
 * @property string|null $refunded_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property int $device_id 设备ID
 * @property string $operator_user_id 操作人ID
 * @property string $workname 操作人
 * @property string|null $expire_at 工单超时时间
 * @property string|null $refunding_at 退款发起时间
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\RefundOrder onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RefundOrder whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RefundOrder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RefundOrder whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RefundOrder whereDeviceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RefundOrder whereExpireAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RefundOrder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RefundOrder whereOperatorUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RefundOrder whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RefundOrder whereOutRefundNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RefundOrder wherePayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RefundOrder whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RefundOrder whereRefundId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RefundOrder whereRefundedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RefundOrder whereRefundingAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RefundOrder whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RefundOrder whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RefundOrder whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RefundOrder whereWorkname($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\RefundOrder withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\RefundOrder withoutTrashed()
 * @mixin \Eloquent
 */
	class RefundOrder extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\OrderItemMaterial
 *
 * @property int $id
 * @property int $order_item_id order_items 主键ID
 * @property int $shop_id 门店ID
 * @property int $material_id materials主键ID
 * @property int $no 加料编码
 * @property float $price 加料价格
 * @property string $name 加料名称
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Models\OrderItem $item
 * @property-read \App\Models\Material $material
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrderItemMaterial whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrderItemMaterial whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrderItemMaterial whereMaterialId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrderItemMaterial whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrderItemMaterial whereNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrderItemMaterial whereOrderItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrderItemMaterial wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrderItemMaterial whereShopId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrderItemMaterial whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class OrderItemMaterial extends \Eloquent {}
}

namespace App\Models{
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
	class MallOrder extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Active
 *
 * @property int $id
 * @property string $name 活动名
 * @property string|null $policy 优惠券领券策略
 * @property array $policy_rule 策略规则
 * @property int $shop_limit 门店限制
 * @property int $coupon_id 优惠券id
 * @property \Carbon\Carbon|null $period_start 有效期初始时间
 * @property \Carbon\Carbon|null $period_end 有效期结束时间
 * @property string|null $deleted_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Active onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active whereCouponId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active wherePeriodEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active wherePeriodStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active wherePolicy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active wherePolicyRule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active whereShopLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Active withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Active withoutTrashed()
 * @mixin \Eloquent
 * @property string|null $no
 * @property string|null $description
 * @property int $category_id
 * @property int $sold_count
 * @property int $is_listing
 * @property int $is_single
 * @property int $sort 排序
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereIsListing($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereIsSingle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereSoldCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereSort($value)
 * @property string $label 标签
 * @property int $support_takeaway 外卖状态0关1开
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereSupportTakeaway($value)
 * @property-read \App\Models\Category $category
 */
	class Product extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\CouponGrand
 *
 * @property int $id
 * @property int $coupon_template_id 对应的模板ID
 * @property string $time_grand 放发时间
 * @property int $status 发放状态(以发放时间界定，0-未发放、1-发放中、2-已截止)
 * @property int $coupon_draw_id 领取方式ID
 * @property int $count 发放数量(0-根据用户范围动态数量、N-指定数量)
 * @property string $member_range 用户范围JSON({"global":0}/{"global":1, "sex":1, "age":18})
 * @property string $draw_rules 领取规则({''days'': 1, ''counts'': 1}，表示每人每天最多领取一张)
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CouponTemplate[] $template
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\CouponGrand onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponGrand whereCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponGrand whereCouponDrawId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponGrand whereCouponTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponGrand whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponGrand whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponGrand whereDrawRules($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponGrand whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponGrand whereMemberRange($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponGrand whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponGrand whereTimeGrand($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponGrand whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\CouponGrand withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\CouponGrand withoutTrashed()
 * @mixin \Eloquent
 * @property int $coupon_id 优惠券id
 * @property string|null $grand_time 发券时间
 * @property int $grand_type 1为立即发券，2为指定时间发券
 * @property int $scence 使用场景
 * @property int $admin_id 发券人
 * @property int $chanel_type 触达渠道
 * @property int $range_type 0为全部用户,1为指定用户,2为导入excel
 * @property-read \App\Models\Admin $admin
 * @property-read \App\Models\Coupon $coupon
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponGrand whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponGrand whereChanelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponGrand whereCouponId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponGrand whereGrandTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponGrand whereGrandType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponGrand whereRangeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponGrand whereScence($value)
 * @property int $page 延迟发券页码
 * @property string|null $range_msg 线下指派对象
 * @property int|null $amount 线下派发优惠券数量
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponGrand whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponGrand wherePage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponGrand whereRangeMsg($value)
 * @property string|null $name 活动名称
 * @property string|null $no 发券活动id编码
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CouponLibrary[] $library
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponGrand whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponGrand whereNo($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Order[] $order
 */
	class CouponGrand extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Order
 *
 * @property int $id
 * @property string $no
 * @property string|null $outer_id
 * @property string $pickup_no
 * @property string $pickup_code
 * @property int $user_id
 * @property int $member_id
 * @property int $shop_id
 * @property bool $is_takeaway
 * @property float $total_fee 总金额
 * @property float $box_fee
 * @property float $delivery_fee
 * @property float $discount_fee 优惠金额
 * @property float $payment 实付金额
 * @property string $phone
 * @property string $paid_type
 * @property \Carbon\Carbon|null $paid_at
 * @property \Carbon\Carbon|null $printed_at
 * @property \Carbon\Carbon|null $dispatched_at
 * @property \Carbon\Carbon|null $closed_at
 * @property string|null $transaction_no
 * @property string $prepay_id
 * @property string $status
 * @property string $refund_status
 * @property \Carbon\Carbon|null $pickup_time 取餐时间
 * @property string|null $pickup_time_period 取餐时间段
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read mixed $paid_type_label
 * @property-read mixed $status_label
 * @property-read \App\Models\Member $member
 * @property-read \App\Models\Shop $shop
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order filter($input = array(), $filter = null)
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Order onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order paginateFilter($perPage = null, $columns = array(), $pageName = 'page', $page = null)
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order simplePaginateFilter($perPage = null, $columns = array(), $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereBoxFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereClosedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereDeliveryFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereDiscountFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereDispatchedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereIsTakeaway($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereOuterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order wherePaidAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order wherePaidType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order wherePayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order wherePickupCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order wherePickupNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order wherePickupTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order wherePickupTimePeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order wherePrepayId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order wherePrintedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereRefundStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereShopId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereTotalFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereTransactionNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Order withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Order withoutTrashed()
 * @mixin \Eloquent
 * @property string $location 用户下单经纬度
 * @property string $remarks 订单备注
 * @property string|null $latest_reufnd_at 最近申请退款时间
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CouponLibrary[] $library
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\MemberScore[] $member_score
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereLatestReufndAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereRemarks($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\OrderItem[] $item
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\MemberScore[] $scores
 * @property int|null $coupon_library_id 优惠券id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\RefundOrder[] $refund
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereCouponLibraryId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ActiveJoin[] $active_join
 * @property int $is_sub 服务商切换
 * @property string $trade_type 交易发起平台
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereIsSub($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereTradeType($value)
 * @property int $prior 插队数
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order wherePrior($value)
 */
	class Order extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\MiniUser
 *
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MiniUser onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MiniUser withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MiniUser withoutTrashed()
 * @mixin \Eloquent
 */
	class MiniUser extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Address
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $phone
 * @property string $sex
 * @property string $address
 * @property string $description
 * @property string $latitude
 * @property string $longitude
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\User $user
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Address onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Address whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Address whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Address whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Address whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Address whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Address whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Address whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Address whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Address wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Address whereSex($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Address whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Address whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Address withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Address withoutTrashed()
 * @mixin \Eloquent
 * @property string|null $complete_address 包含省市区详细地址
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Address whereCompleteAddress($value)
 */
	class Address extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\OrderItem
 *
 * @property int $id
 * @property int $order_id
 * @property int $shop_id
 * @property int $product_id
 * @property int $sku_id
 * @property int $quantity
 * @property int $category_id
 * @property float $price 商品价格
 * @property float $total_fee 总金额
 * @property string $name
 * @property string|null $description
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\OrderItemAttribute[] $attributes
 * @property-read \App\Models\Category $category
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\OrderItemMaterial[] $materials
 * @property-read \App\Models\Order $order
 * @property-read \App\Models\Product $product
 * @property-read \App\Models\Sku $sku
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\OrderItemSpecification[] $specifications
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\OrderItem onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrderItem whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrderItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrderItem whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrderItem whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrderItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrderItem whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrderItem whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrderItem wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrderItem whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrderItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrderItem whereShopId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrderItem whereSkuId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrderItem whereTotalFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OrderItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\OrderItem withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\OrderItem withoutTrashed()
 * @mixin \Eloquent
 */
	class OrderItem extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\User
 *
 * @property int $id
 * @property string $name 用户名
 * @property string|null $email 邮箱
 * @property string|null $phone
 * @property string|null $birthday
 * @property int $avatar_id 头像id
 * @property string|null $image_url 微信头像url
 * @property string $sex 性别
 * @property string|null $wxlite_session_key 小程序session
 * @property string|null $wxlite_open_id 小程序openid
 * @property string|null $wx_union_id 微信unionid
 * @property string $password 密码
 * @property \Carbon\Carbon|null $last_login_at 最后登录时间
 * @property string|null $remember_token
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Address[] $addresses
 * @property-read mixed $avatar
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Member[] $members
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Order[] $orders
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PointLog[] $pointLogs
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\MemberScore[] $score
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereAvatarId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereBirthday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereLastLoginAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereSex($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereWxUnionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereWxliteOpenId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereWxliteSessionKey($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User withoutTrashed()
 * @mixin \Eloquent
 * @property int $is_vip 测试用户字段
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CouponLibrary[] $library
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereIsVip($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\MallOrder[] $mallOrder
 * @property string|null $jpush_id 极光推送设备号id
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereJpushId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\GiftRecord[] $giftRecord
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\VkaRecord[] $vkaRecord
 * @property string|null $district 微信备注地区
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Comment[] $comment
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\MemberCardRecord[] $memberCardRecord
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereDistrict($value)
 */
	class User extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Member
 *
 * @property int $id
 * @property int $user_id 用户id
 * @property int $avatar_id 头像id
 * @property string $name 姓名
 * @property string|null $email 邮箱
 * @property string|null $phone 电话
 * @property int $points
 * @property string|null $position
 * @property string|null $birthday
 * @property string $type
 * @property string $status
 * @property string $sex
 * @property int $order_count
 * @property float $order_money
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereAvatarId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereBirthday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereOrderCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereOrderMoney($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member wherePoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereSex($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereUserId($value)
 * @mixin \Eloquent
 * @property int $order_score 累计总积分
 * @property int $used_score 已使用积分
 * @property int $usable_score 可用积分
 * @property int $level_id 会员等级
 * @property int $exp 成长值
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Order[] $order
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Member onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereExp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereLevelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereOrderScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereUsableScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereUsedScore($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Member withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Member withoutTrashed()
 * @property-read \App\Models\Level $level
 * @property int $new_coupon_tab 最新优惠券标记
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereNewCouponTab($value)
 * @property int $score_lock 会员锁定，0为未锁，1为锁定
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereScoreLock($value)
 * @property string|null $member_no 会员编号
 * @property string|null $expire_time 会员过期时间
 * @property string|null $star_time 注册星球会员时间
 * @property int $member_cup 星球等级购买杯数计算
 * @property int $member_type 0表示go会员,1表示星球会员,2表示vka迁移会员
 * @property int $star_level_id 星球会员等级id
 * @property int $star_exp 星球会员经验值
 * @property-read \App\Models\StarLevel $starLevel
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereExpireTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereMemberCup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereMemberNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereMemberType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereStarExp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereStarLevelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereStarTime($value)
 */
	class Member extends \Eloquent {}
}

namespace App\Models{
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
	class Shop extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\MallOrderEntity
 *
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MallOrderEntity onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MallOrderEntity withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MallOrderEntity withoutTrashed()
 * @mixin \Eloquent
 * @property int $id
 * @property string|null $specifications 规格json数据
 * @property string|null $deleted_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderEntity whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderEntity whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderEntity whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderEntity whereSpecifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderEntity whereUpdatedAt($value)
 */
	class MallOrderEntity extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\MRole
 *
 * @mixin \Eloquent
 * @property int $id
 * @property string $no
 * @property string $name
 * @property int $status
 * @property string|null $deleted_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\MPermission[] $permission
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MRole onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MRole whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MRole whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MRole whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MRole whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MRole whereNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MRole whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MRole whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MRole withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MRole withoutTrashed()
 */
	class MRole extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\MallOrderCoupon
 *
 * @property int $id
 * @property string|null $policy 优惠券领券策略
 * @property array $policy_rule 策略规则
 * @property int $period_type 过期类型（0绝对时间，1相对时间）
 * @property string|null $period_start 有效期初始时间
 * @property string|null $period_end 有效期结束时间
 * @property int|null $period_day 有效时间段
 * @property int $shop_limit 门店限制
 * @property int $product_limit 商品限制
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\MallOrderCoupon[] $mallcoupon
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MallOrderCoupon onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderCoupon whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderCoupon whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderCoupon whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderCoupon wherePeriodDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderCoupon wherePeriodEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderCoupon wherePeriodStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderCoupon wherePeriodType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderCoupon wherePolicy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderCoupon wherePolicyRule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderCoupon whereProductLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderCoupon whereShopLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderCoupon whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MallOrderCoupon withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MallOrderCoupon withoutTrashed()
 * @mixin \Eloquent
 * @property string $code_id 优惠券编码id
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderCoupon whereCodeId($value)
 * @property string|null $no
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderCoupon whereNo($value)
 * @property int $use_limit 0表示全部可用，1表示自取，2表示外卖
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallOrderCoupon whereUseLimit($value)
 */
	class MallOrderCoupon extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\MallSpecification
 *
 * @property int $id
 * @property int $mall_product_id
 * @property string|null $name 规格名
 * @property string|null $value 规格值
 * @property string|null $deleted_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Models\MallProduct $product
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MallSpecification onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallSpecification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallSpecification whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallSpecification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallSpecification whereMallProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallSpecification whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallSpecification whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallSpecification whereValue($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MallSpecification withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MallSpecification withoutTrashed()
 * @mixin \Eloquent
 * @property int $sort 规格排序字段
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MallSpecification whereSort($value)
 */
	class MallSpecification extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\CouponLibrary
 *
 * @property int $id
 * @property string $name 优惠券名称
 * @property int $user_id 用户id
 * @property int $order_id 订单id
 * @property int $coupon_id 优惠券id
 * @property string|null $policy 优惠券领券策略
 * @property string|null $policy_rule 策略规则
 * @property int $source_id 关联id
 * @property string|null $source_type 关联类型
 * @property string|null $period_start 有效期初始时间
 * @property string|null $period_end 有效期结束时间
 * @property string|null $used_at 使用时间
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \App\Models\CouponGrand $grand
 * @property-read \App\Models\Order $order
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $source
 * @property-read \App\Models\User $user
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponLibrary ofWhen($id)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\CouponLibrary onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponLibrary whereCouponId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponLibrary whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponLibrary whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponLibrary whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponLibrary whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponLibrary whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponLibrary wherePeriodEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponLibrary wherePeriodStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponLibrary wherePolicy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponLibrary wherePolicyRule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponLibrary whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponLibrary whereSourceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponLibrary whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponLibrary whereUsedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponLibrary whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\CouponLibrary withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\CouponLibrary withoutTrashed()
 * @mixin \Eloquent
 * @property-read \App\Models\Coupon $coupon
 * @property int $status 核销状态
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponLibrary whereStatus($value)
 * @property string|null $code 兑换码
 * @property string $code_id 券码id
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponLibrary whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponLibrary whereCodeId($value)
 * @property int $tab 新优惠券标识,默认0为未有新状态，1为新优惠券
 * @property float $discount_fee 优惠金额
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponLibrary whereDiscountFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponLibrary whereTab($value)
 * @property int $use_limit 0表示全部可用，1表示自取，2表示外卖
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponLibrary whereUseLimit($value)
 * @property string|null $serial_no 流水号
 * @property string|null $order_no 订单号
 * @property-read \App\Models\Member $member
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponLibrary whereOrderNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponLibrary whereSerialNo($value)
 */
	class CouponLibrary extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Member
 *
 * @property int $id
 * @property int $user_id 用户id
 * @property int $avatar_id 头像id
 * @property string $name 姓名
 * @property string|null $email 邮箱
 * @property string|null $phone 电话
 * @property int $points
 * @property string|null $position
 * @property string|null $birthday
 * @property string $type
 * @property string $status
 * @property string $sex
 * @property int $order_count
 * @property float $order_money
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereAvatarId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereBirthday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereOrderCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereOrderMoney($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member wherePoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereSex($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Member whereUserId($value)
 * @mixin \Eloquent
 * @property int $exp_min 最低成长值
 * @property int $exp_max 最高成长值
 * @property int $exp_deduction 成长值扣除
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Level onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Level whereExpDeduction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Level whereExpMax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Level whereExpMin($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Level withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Level withoutTrashed()
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Member[] $member
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\GiftRecord[] $giftRecord
 */
	class Level extends \Eloquent {}
}

namespace App\Models{
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
	class MallOrderLock extends \Eloquent {}
}

namespace App\Models{
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
	class MallProduct extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Comment
 *
 * @property int $id
 * @property int $user_id 用户ID
 * @property int $issue_type 问题类型(0表示功能异常,1表示体验问题,2表示新功能建议,3表示其他)
 * @property string $comment 反馈内容
 * @property string|null $reply_at 回复时间
 * @property string|null $reply_text 回复内容
 * @property int $admin_id 回复人id
 * @property int $status 状态(0表示未回复,1表示已回复)
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \App\Models\Admin $admin
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Image[] $images
 * @property-read \App\Models\Member $member
 * @property-read \App\Models\User $user
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Comment onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Comment whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Comment whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Comment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Comment whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Comment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Comment whereIssueType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Comment whereReplyAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Comment whereReplyText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Comment whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Comment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Comment whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Comment withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Comment withoutTrashed()
 */
	class Comment extends \Eloquent {}
}

namespace App\Models{
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
	class MallOrderExpress extends \Eloquent {}
}

namespace App\Models{
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
	class MallExpress extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\CashStorage
 *
 * @property-read \App\Models\Member $member
 * @property-read \App\Models\User $user
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\CashStorage onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\CashStorage withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\CashStorage withoutTrashed()
 */
	class CashStorage extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\StarLevel
 *
 * @property int $id
 * @property string $name 等级名称
 * @property int $exp_min 最低成长值
 * @property int $exp_max 最高成长值
 * @property int $exp_deduction 成长值扣除
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\GiftRecord[] $giftRecord
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Member[] $member
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $user
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\StarLevel onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StarLevel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StarLevel whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StarLevel whereExpDeduction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StarLevel whereExpMax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StarLevel whereExpMin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StarLevel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StarLevel whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StarLevel whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\StarLevel withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\StarLevel withoutTrashed()
 * @mixin \Eloquent
 */
	class StarLevel extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\GiftRecord
 *
 * @property int $id
 * @property int $user_id 用户ID
 * @property string $name 礼包名称
 * @property int $gift_type 礼包类型
 * @property int $level_id 升级时用户的会员等级ID
 * @property int $star_level_id 升级时用户的星球会员等级ID
 * @property string|null $pick_at 礼包领取时间
 * @property string $overdue_at 礼包过期时间
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property string $start_at 礼包开始时间(大于等于此日期才能领取该礼包)
 * @property-read \App\Models\Level $level
 * @property-read \App\Models\StarLevel $startLevel
 * @property-read \App\Models\User $user
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\GiftRecord onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GiftRecord whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GiftRecord whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GiftRecord whereGiftType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GiftRecord whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GiftRecord whereLevelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GiftRecord whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GiftRecord whereOverdueAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GiftRecord wherePickAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GiftRecord whereStarLevelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GiftRecord whereStartAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GiftRecord whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GiftRecord whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\GiftRecord withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\GiftRecord withoutTrashed()
 * @mixin \Eloquent
 * @property int $status 状态(0表示新礼包未被查看,1表示已被查看)
 * @property-read \App\Models\StarLevel $starLevel
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GiftRecord whereStatus($value)
 */
	class GiftRecord extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Admin
 *
 * @property int $id
 * @property int $user_id
 * @property int $avatar_id
 * @property string $wechat_userid
 * @property string $name
 * @property string|null $english_name
 * @property string|null $email
 * @property string $password
 * @property string|null $position
 * @property string $sex
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\Image $avatar
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Permission\Models\Permission[] $permissions
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Permission\Models\Role[] $roles
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Admin onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Admin permission($permissions)
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Admin role($roles)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Admin whereAvatarId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Admin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Admin whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Admin whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Admin whereEnglishName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Admin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Admin whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Admin wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Admin wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Admin whereSex($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Admin whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Admin whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Admin whereWechatUserid($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Admin withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Admin withoutTrashed()
 * @mixin \Eloquent
 * @property string|null $mobile
 * @property bool $can_scan
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Admin whereCanScan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Admin whereMobile($value)
 * @property string $images 用户头像
 * @property string $username 用户账号
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Admin whereImages($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Admin whereUsername($value)
 */
	class Admin extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\CouponType
 *
 * @property int $id
 * @property string $key 优惠劵类型标识
 * @property string $name 优惠劵类型名称
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponType whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponType whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CouponType whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class CouponType extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\CouponType
 *
 * @property int $id
 * @property string $key 优惠劵标识
 * @property string $name 优惠劵类型名称
 * @property string|null $policy 优惠券领券策略
 * @property array $policy_rule 策略规则
 * @property int $period_type 过期类型（1绝对时间，2相对时间）
 * @property string|null $period_start 有效期初始时间
 * @property string|null $period_end 有效期结束时间
 * @property int $period_day 有效时间段
 * @property int $count 发券数量
 * @property int $shop_limit 门店限制
 * @property int $product_limit 商品限制
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CouponGrand[] $grand
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CouponLibrary[] $library
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CouponLibrary[] $unuselib
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CouponLibrary[] $uselib
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Coupon onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Coupon whereCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Coupon whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Coupon whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Coupon whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Coupon whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Coupon wherePeriodDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Coupon wherePeriodEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Coupon wherePeriodStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Coupon wherePeriodType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Coupon wherePolicy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Coupon wherePolicyRule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Coupon whereProductLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Coupon whereShopLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Coupon whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Coupon withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Coupon withoutTrashed()
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Product[] $product
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Shop[] $shop
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CouponLibrary[] $picklib
 * @property string|null $no 优惠券编号
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Coupon whereNo($value)
 * @property int $flag 优惠券标识
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Coupon[] $mall
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Coupon whereFlag($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Order[] $order
 * @property int $use_limit 0表示全部可用，1表示自取，2表示外卖
 * @property int $unit_time 时间维度单位,0表示天，1表示月，2表示年
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Coupon whereUnitTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Coupon whereUseLimit($value)
 * @property int $status 模板状态0为已启动，1为已停用
 * @property string|null $image 优惠券模板图片
 * @property string|null $admin_name 创建人
 * @property int $category_limit 饮品类别限制
 * @property int $material_limit 加料限制
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Category[] $category
 * @property-read mixed $status_text
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\MallProduct[] $mallProduct
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Material[] $material
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Coupon whereAdminName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Coupon whereCategoryLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Coupon whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Coupon whereMaterialLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Coupon whereStatus($value)
 */
	class Coupon extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Image
 *
 * @property int $id
 * @property int $user_id
 * @property string $origin_name
 * @property string $path
 * @property string|null $width
 * @property string|null $height
 * @property string|null $size
 * @property string|null $content_type
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Image whereContentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Image whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Image whereHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Image whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Image whereOriginName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Image wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Image whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Image whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Image whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Image whereWidth($value)
 * @mixin \Eloquent
 */
	class Image extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ActiveJoin
 *
 * @property int $id
 * @property int $user_id 用户id
 * @property int $order_id 订单id
 * @property int $active_id 活动id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ActiveJoin whereActiveId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ActiveJoin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ActiveJoin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ActiveJoin whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ActiveJoin whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ActiveJoin whereUserId($value)
 * @mixin \Eloquent
 * @property float $discount_fee 优惠金额
 * @property string|null $deleted_at
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\ActiveJoin onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ActiveJoin whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ActiveJoin whereDiscountFee($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\ActiveJoin withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\ActiveJoin withoutTrashed()
 * @property-read \App\Models\Active $active
 */
	class ActiveJoin extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PointLog
 *
 * @property int $id
 * @property int $user_id
 * @property int $member_id
 * @property int $pointable_id
 * @property string $pointable_type
 * @property int $points
 * @property string|null $description
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $pointable
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PointLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PointLog whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PointLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PointLog whereMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PointLog wherePointableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PointLog wherePointableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PointLog wherePoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PointLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PointLog whereUserId($value)
 * @mixin \Eloquent
 * @property int $shop_id
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PointLog whereShopId($value)
 */
	class PointLog extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Active
 *
 * @property int $id
 * @property string $name 活动名
 * @property string|null $policy 优惠券领券策略
 * @property array $policy_rule 策略规则
 * @property int $shop_limit 门店限制
 * @property int $coupon_id 优惠券id
 * @property \Carbon\Carbon|null $period_start 有效期初始时间
 * @property \Carbon\Carbon|null $period_end 有效期结束时间
 * @property string|null $deleted_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Active onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active whereCouponId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active wherePeriodEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active wherePeriodStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active wherePolicy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active wherePolicyRule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active whereShopLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Active withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Active withoutTrashed()
 * @mixin \Eloquent
 * @property int $message 消息提醒(0无1微信消息)
 * @property int $status 活动状态（0关闭1开启）
 * @property string|null $remark 规则描述
 * @property-read \App\Models\Coupon $coupon
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Shop[] $shop
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active whereStatus($value)
 * @property int $user_limit 门店限制
 * @property int $total_freq 一共几次
 * @property int $day_freq 每天几次
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active whereDayFreq($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active whereTotalFreq($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active whereUserLimit($value)
 * @property string|null $no 活动编号
 * @property int $period_type 有效时间1为限定范围内有效，2为永久有效
 * @property int $admin_id 后台用户id
 * @property int $type 优惠类型（1优惠券2下单优惠）
 * @property-read \App\Models\Admin $admin
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ActiveJoin[] $join
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active whereNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active wherePeriodType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active whereType($value)
 * @property string|null $erp_no erp活动编码
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active whereErpNo($value)
 * @property int $use_limit 活动使用场景,0表示全部可用,1表示仅限自取,2表示仅限外卖
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Active whereUseLimit($value)
 */
	class Active extends \Eloquent {}
}

