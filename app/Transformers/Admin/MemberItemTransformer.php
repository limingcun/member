<?php

namespace App\Transformers\Admin;

use Carbon\Carbon;
use App\Models\Member;
use App\Models\MemberScore;
use App\Models\CouponLibrary;
use League\Fractal\TransformerAbstract;

class MemberItemTransformer extends TransformerAbstract
{
    /**
     * 
     * 会员数据获取和转化
     * @return array
     */
    public function transform(Member $member)
    {
        return [
            'order_score' => $member->order_score,
            'used_score' => $member->used_score,
            'usable_score' => $member->usable_score,
            'deduction_score' => $member->user->score()->whereIn('method', [MemberScore::METHOD['refund'], MemberScore::METHOD['change'], MemberScore::METHOD['expire']])->sum('score_change'),
            'refund_score' => $member->user->score()->where('method', MemberScore::METHOD['refund'])->sum('score_change'),
            'convert_score' => $member->user->score()->where('method', MemberScore::METHOD['change'])->sum('score_change'),
            'expire_score' => $member->user->score()->where('method', MemberScore::METHOD['expire'])->sum('score_change'),
            'total_coupon' => $member->user->library()->whereIn('status', [CouponLibrary::STATUS['surplus'], CouponLibrary::STATUS['used'], CouponLibrary::STATUS['period']])->count(),
            'surplus_coupon' => $member->user->library()->where('status', CouponLibrary::STATUS['surplus'])->count(),
            'used_coupon' => $member->user->library()->where('status', CouponLibrary::STATUS['used'])->count(),
            'period_coupon' => $member->user->library()->where('status', CouponLibrary::STATUS['period'])->count()
        ];
    }
}
