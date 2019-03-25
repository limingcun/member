<?php

namespace App\Transformers\Admin;

use App\Models\Coupon;
use App\Models\CouponLibrary;
use App\Models\CouponGrand;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class CouponPersonLibraryTransformer extends TransformerAbstract
{
    /**
     *
     * 会员数据获取和转化
     * @return array
     */
    public function transform(CouponLibrary $coupon_library)
    {
        return [
            'id' => $coupon_library->id,
            'code_id' => $coupon_library->code_id,
            'name' => $coupon_library->name,
            'status' => $this->statusText($coupon_library->status),
            'period_time' => $this->periodTime($coupon_library->period_start, $coupon_library->period_end),
            'created_at' => (string) $coupon_library->created_at,
            'way' => $this->getWay($coupon_library->coupon, $coupon_library->grand ?? '')
        ];
    }

    /**
     * 优惠券有效期时间
     * @param type $start有效期开始时间
     * @param type $end有效期结束时间
     * @return type
     */
    public function periodTime($start, $end) {
        return Carbon::parse($start)->format('Y-m-d').'至'.Carbon::parse($end)->format('Y-m-d');
    }

    /**
     * 优惠券状态显示
     * @param type $stauts
     */
    public function statusText($status) {
        switch($status) {
            case CouponLibrary::STATUS['unpick']:
                return '未领取';
            case CouponLibrary::STATUS['surplus']:
                return '未使用';
            case CouponLibrary::STATUS['used']:
                return '已使用';
            case CouponLibrary::STATUS['period']:
                return '已过期';
            default:
                return '';
        }
    }

    /**
     * 获取喜茶券类型途径
     * @param type $coupon模板
     * @param type $grand记录
     */
    public function getWay($coupon, $grand) {
        if ($grand != '') {
            $scence = $grand->scence;
            if ($scence == CouponGrand::SCENCE['line']) {
                $way = '线上发放';
            } else if ($scence == CouponGrand::SCENCE['change']) {
                $way = '兑换码兑换';
            } else if ($scence == CouponGrand::SCENCE['qrcode']) {
                $way = '二维码兑换';
            }
        } else {
            $flag = $coupon->flag;
            if ($flag >= 11 && $flag <= 30) {
                $way = 'Go会员升级';
            } else if ($flag == Coupon::FLAG['fee_star_20']) {
                $way = '星球会员福利（满20赠1）';
            } else if ($flag == Coupon::FLAG['fee_star_10']) {
                $way = '星球会员福利（满10赠1）';
            } else if ($flag == Coupon::FLAG['fee_star_5']) {
                $way = '星球会员福利（满5赠1）';
            } else if ($flag >= 31 && $flag <= 40) {
                $way = '星球会员购卡';
            } else if ($flag >= 41 && $flag <= 50) {
                $way = '星球会员首充';
            } else if ($flag >= 51 && $flag <= 70) {
                $way = '星球会员福利';
            } else if ($flag >= 71 && $flag <= 80) {
                $way = '星球会员升级';
            } else if ($flag >= 81 && $flag <= 90) {
                $way = 'vka星球移民返券';
            } else if ($flag == -127) {
                $way = '其他券(旧数据)';
            } else if ($flag >= 101 && $flag <= 109) {
                $way = '员工券';
            } else if ($flag >=110 && $flag <=120) {
                $way = '活动获取';
            } else if ($flag == 6) {
                $way = '喜茶金猪活动赠送';
            } else {
                $way = '其他券';
            }
        }
        return $way;
    }
}
