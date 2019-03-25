<?php

namespace App\Transformers\Admin;

use App\Models\MemberScore;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use App\Models\Order;

class MemberScoreTransformer extends TransformerAbstract
{
    /**
     *
     * 会员数据获取和转化
     * @return array
     */
    public function transform(MemberScore $member_score)
    {
        return [
            'id' => $member_score->id,
            'name' => $member_score->user->name ?? '',
            'phone' => $member_score->user->phone ?? '',
            'method' => $member_score->method,
            'method_text' => $member_score->method_text,
            'description' => $member_score->description,
            'score_change' => $this->changeScore($member_score->method, $member_score->score_change).$member_score->score_change,
            'no' => $member_score->source->no ?? $member_score->source->order_no ?? '',
            'created_at' => (string) $member_score->created_at,
            'member_type' => $member_score->member_type ? '星球会员' : 'Go会员',
            'type_no' => $this->typeNoJump($member_score)
        ];
    }

    public function changeScore($method, $score) {
        switch($method) {
            case MemberScore::METHOD['cost']:
            case MemberScore::METHOD['active']:
            case MemberScore::METHOD['mall_refund']:
            case MemberScore::METHOD['vka']:
            case MemberScore::METHOD['star_update']:
            case MemberScore::METHOD['star_date']:
            case MemberScore::METHOD['task']:
            case MemberScore::METHOD['game']:
            case MemberScore::METHOD['custom']:
                return $score ? '+' : '';
            default:
                return '-';
        }
    }
    
    /**
     * 返回给前端跳转
     * 1消费饮品跳转
     * 2消费买卡跳转
     * 3消费退款
     * 4虚拟商品跳转
     * 5实体商品跳转
     * @param MemberScore $member_score
     */
    public function typeNoJump(MemberScore $member_score) {
        $method = $member_score->method;
        $go = 0;
        if ($method == 1) {
            if ($member_score->source_type == Order::class) {
                $go = 1;
            } else {
                $go = 2;
            }
        } else if ($method == 10) {
            $go = 3;
        } else if ($method == 11) {
            $mall_type = $member_score->source->mall_type ?? '';
            if ($mall_type == 1) {
                $go = 4;
            } else if ($mall_type == 2) {
                $go = 5;
            }
        } else if ($method == 6) {
            $go = 1;
        }
        return $go;
    }
}
