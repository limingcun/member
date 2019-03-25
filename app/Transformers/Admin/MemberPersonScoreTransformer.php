<?php

namespace App\Transformers\Admin;

use App\Models\MemberScore;
use App\Models\Order;
use League\Fractal\TransformerAbstract;

class MemberPersonScoreTransformer extends TransformerAbstract
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
            'created_at' => (string) $member_score->created_at,
            'member_type' => $member_score->member_type ? '星球会员' : 'Go会员',
            'method' => $member_score->method,
            'method_text' => $member_score->method_text,
//            'desc' => $this->getDesc($member_score->method, $member_score->source),
            'desc' => $member_score->description,
            'score_change' => $this->changeScore($member_score->method, $member_score->score_change).$member_score->score_change,
            'origin' => $this->getOrigin($member_score->origin),
            'no' => $member_score->source->no ?? $member_score->source->order_no ?? '',
            'type_no' => $this->typeNoJump($member_score)
        ];
    }

    /*
     * 加减符号
     */
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

    /*
     * 获取描述
     */
    public function getDesc($method, $source) {
        switch($method) {
            case MemberScore::METHOD['cost']:
                if ($source) {
                    return '实付金额：'. $source->payment .'元';
                } else {
                    return '实付金额：0元';
                }
            case MemberScore::METHOD['active']:
                return '活动获得';
            case MemberScore::METHOD['mall_refund']:
                return '积分商城：退单';
            case MemberScore::METHOD['vka']:
                return '星球迁移好礼';
            case MemberScore::METHOD['star_update']:
                return '升级好礼';
            case MemberScore::METHOD['star_date']:
                return '会员日额外奉送';
            case MemberScore::METHOD['refund']:
                if ($source) {
                    return '退款金额：'. $source->payment .'元';
                } else {
                    return '退款金额：0元';
                }
            case MemberScore::METHOD['change']:
                if ($source) {
                    if ($source->mall_type == 1) {
                        $str = '优惠券兑换：';
                    } else {
                        $str = '商品兑换：';
                    }
                    return $str . $source->item->name ?? '';
                } else {
                    return '';
                }
            default:
                return '';
        }
    }

    public function getOrigin($origin) {
        if ($origin == 1) {
            return '喜茶APP';
        } else {
            return '喜茶Go小程序';
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
