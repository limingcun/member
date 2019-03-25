<?php

namespace App\Transformers\Admin;

use App\Models\Member;
use App\Models\MemberCardRecord;
use App\Models\User;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class StarConfigTransformer extends TransformerAbstract
{


    public function transform(Member $member)
    {
        $user = User::where('id', $member->user_id)->select(['id', 'name', 'phone', 'sex', 'birthday'])->first();
        // 查找用户是否有调整记录
        $status = MemberCardRecord::where('user_id', $user->id)->where('card_no', 0)->where('paid_type', 3)
            ->where('price', 0)->where('status', MemberCardRecord::STATUS['is_pay'])->select('id')->exists();
        return [
            'id' => $user->id,
            'name' => $user->name,
            'phone' => $user->phone,
            'sex' => $user->sex,
            'birthday' => $user->birthday,
            'member_type' => strtotime($member->expire_time) > strtotime(Carbon::now()->startOfDay()) ? 1 : 0,
            'star_level' => $member->star_level_id,
            'level' => $member->level_id,
            'is_config' => $status,
            'usable_score' => $member->usable_score
        ];
    }
}
