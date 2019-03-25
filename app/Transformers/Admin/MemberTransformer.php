<?php

namespace App\Transformers\Admin;

use Carbon\Carbon;
use App\Models\Member;
use League\Fractal\TransformerAbstract;

class MemberTransformer extends TransformerAbstract
{
    /**
     * 
     * 会员数据获取和转化
     * @return array
     */
    public function transform(Member $member)
    {
        return [
            'id' => $member->user->id ?? '',
            'name' => $member->user->name ?? '',
            'sex' => $this->sexChange($member->user->sex ?? ''),
            'usable_score' => $member->usable_score,
            'phone' => $member->user->phone ?? '',
            'birthday' => $member->user->birthday ?? '',
            'member_type' => $member->member_type,
            'is_vka' => $this->isVka($member),
            'level' => $member->level->name ?? '',
            'star_level' => $member->starLevel->name ?? '',
            'exp' => $member->exp,
            'is_star' => $this->isGoOrStar($member),
            'star_exp' => $member->star_exp,
            'score_lock' => $member->score_lock,
            'register' => (string) $member->created_at,
            'expire_time' => (string) $member->expire_time,
            'order_money' => $member->order_money,
            'order_score' => $member->order_score,
            'used_score' => $member->used_score,
            'free_money' => $member->storage->free_money ?? '—',
            'storage_status' => $member->storage->status ?? '',
            'storage_id' => $member->storage->id ?? ''
        ];
    }
    
    public function sexChange($sex) {
        switch($sex) {
            case 'male':
                return '男';
            case 'female':
                return '女';
            default:
                return '保密';
        }   
    }
    
    /*
     * 判断vka迁移后是go会员还是星球会员
     * $member_type会员的类型
     * $expire_time过期时间
     */
    public function isVka(Member $member) {
        if ($member->member_type == 2) {
            return 1;
        } else {
            return 0;
        }
    }
    
    /*
     * 判断是go会员还是星球会员
     * 0是纯go会员,1是星球会员过期会员,2是星球会员
     */
    public function isGoOrStar(Member $member) {
        $expire_time = $member->expire_time;
        if (!$expire_time) {
            return 0;
        }
        if (Carbon::today()->timestamp > Carbon::parse($expire_time)->timestamp) {
            return 0;
        } else {
            return 1;
        }
    }
}
