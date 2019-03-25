<?php

namespace App\Transformers\Admin;

use App\Models\MemberExp;
use App\Models\Order;
use App\Models\Level;
use App\Models\StarLevel;
use League\Fractal\TransformerAbstract;

class MemberExpTransformer extends TransformerAbstract
{
    /**
     *
     * 会员数据获取和转化
     * @return array
     */
    public function transform(MemberExp $member_exp)
    {
        return [
            'id' => $member_exp->id,
            'created_at' => (string) $member_exp->created_at,
            'member_type' => $member_exp->member_type ? '星球会员' : 'Go会员',
            'method' => $member_exp->method,
            'method_text' => $member_exp->method_text,
            'desc' => $member_exp->description,
            'go_exp_change' => $this->changeExp($member_exp->method, $member_exp->go_exp_change),
            'star_exp_change' => $this->changeExp($member_exp->method, $member_exp->star_exp_change, $member_exp->star_level_id),
            'level_degree' => $this->levelDegree($member_exp->level_id, $member_exp->star_level_id, $member_exp->member_type),
            'no' => $member_exp->source->no ?? $member_exp->source->order_no ?? '—',
            'type_no' => $this->typeNoJump($member_exp)
        ];
    }
    
    /*
     * 加减符号
     * $method方式
     * $exp经验值
     * $member_type会员类型
     */
    public function changeExp($method, $exp, $star_level_id = 1) {
        if (!$star_level_id) {
            return '—';
        }
        switch($method) {
            case MemberExp::METHOD['init']:
            case MemberExp::METHOD['cost']:
            case MemberExp::METHOD['active']:
            case MemberExp::METHOD['vka']:
            case MemberExp::METHOD['task']:
            case MemberExp::METHOD['game']:
            case MemberExp::METHOD['custom']:
                $f = $exp ? '+' : '';
                break;
            default:
                $f = '-';
                break;
        }
        if ($f != '') {
            return $f.$exp;
        }
        return $exp;
    }
    
    /**
     * 返回给前端跳转
     * 1消费饮品跳转
     * 2消费买卡跳转
     * 3消费退款
     * @param MemberScore $member_score
     */
    public function typeNoJump(MemberExp $member_exp) {
        $method = $member_exp->method;
        $go = 0;
        if ($method == 1) {
            if ($member_exp->source_type == Order::class) {
                $go = 1;
            } else {
                $go = 2;
            }
        } else if ($method == 10) {
            $go = 3;
        }
        return $go;
    }
    
    /**
     * 等级身份
     * $level_id Go等级
     * $star_level_id 星球等级
     * $member_type会员等级
     */
    public function levelDegree($level_id, $star_level_id, $member_type) {
        $level = Level::find($level_id);
        $level_name = $level->name;
        $star_level = StarLevel::find($star_level_id);
        if ($star_level) {
            $star_level_name = $star_level->name;
        } else {
            $star_level_name = '';
        }
        if ($star_level_name == '') {
            $level_degree = $level_name;
        } else {
            if (!$member_type) {
                $star_level_name.='(身份保留)';
            }
            $level_degree = $level_name.",".$star_level_name;
        }
        return $level_degree;
    }
}
