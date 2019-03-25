<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
class RefundOrder extends Model
{
    use SoftDeletes;
}
