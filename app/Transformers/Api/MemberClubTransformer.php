<?php

namespace App\Transformers\Api;

use App\Models\Level;
use App\Models\StarLevel;
use App\Models\User;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class MemberClubTransformer extends TransformerAbstract
{

    const STAR_ID = [
        '' => 0,
        '白银' => 1,
        '黄金' => 2,
        '铂金' => 3,
        '钻石' => 4,
        '黑金' => 5,
        '黑钻' => 6,
    ];

    public function transform(User $user)
    {
        $member = $user->members()->first();
        $star_level = $member->starLevel()->select(['name', 'exp_max'])->first();
        $next_star_level = StarLevel::where('id', $member->star_level_id + 1)->select(['name', 'exp_min'])->first();
        $level = $member->level()->select(['name', 'exp_max'])->first();
        $next_level = Level::where('id', $member->level_id + 1)->select(['name', 'exp_min'])->first();
        $expire_time = Carbon::createFromTimestamp(strtotime($member->expire_time))->endOfDay();
        $is_renew = strtotime($expire_time) < strtotime(Carbon::today()->addDays(15));
        return [
            'avatar_url' => $user->avatar_id ? '' : $user->image_url,
            'QINIU_URL' => env('QINIU_URL'),
            'name' => $user->name,
            'is_star' => $expire_time > Carbon::now(),
            'is_renew' => $is_renew,
            'level' => $level->name,
            'level_exp' => $member->exp,
            'level_exp_max' => $level->exp_max,
            'level_next' => $next_level->name ?? null,
            'level_next_need' => isset($next_level->exp_min) ? ($next_level->exp_min - $member->exp) : 0,
            'star_level' => self::STAR_ID[$star_level->name ?? ''],
            'star_level_name' => isset($star_level->name) ? $star_level->name : null,
            'star_level_exp' => $member->star_exp,
            'star_level_exp_max' => isset($star_level->exp_max) ? $star_level->exp_max : null,
            'star_level_next' => $next_star_level->name ?? null,    // 星球会员下一等级
            'star_level_next_need' => isset($next_star_level->exp_min) ? ($next_star_level->exp_min - $member->star_exp) : 0,   // 星球会员升级到下一等级所需经验
            'star_expire_time' => $member->expire_time,   // 会员有效期
            'star_time' => $member->star_time,
            'red_dot' => '',    // 红点判断时间
        ];

    }
}
