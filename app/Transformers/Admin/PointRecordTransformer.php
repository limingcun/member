<?php

namespace App\Transformers\Admin;

use App\Models\Member;
use League\Fractal\TransformerAbstract;

class PointRecordTransformer extends TransformerAbstract
{
    /**
     * 
     * 会员数据获取和转化
     * @return array
     */
    public function transform(Member $member)
    {
        return [
            'id' => $member->id,
            'user_id' => $member->user_id,
            'name' => $member->name,
            'phone' => $member->phone,
            'order_money' => $member->order_money,
            'order_score' => $member->order_score,
            'used_score' => $member->used_score,
            'usable_score' => $member->usable_score
        ];
    }
}
