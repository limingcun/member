<?php

namespace App\Transformers\Admin;

use App\Models\Active;
use League\Fractal\TransformerAbstract;
use Carbon\Carbon;

class ActiveTransformer extends TransformerAbstract
{
    /**
     * 
     * 会员数据获取和转化
     * @return array
     */
    public function transform(Active $active)
    {
        return [
            'id' => $active->id,
            'no' => $active->no,
            'name' => $active->name,
            'policy' => $active->policy == 'CupFreeActivePolicy' ? '下单优惠' : '优惠券优惠',
            'admin' => $active->admin->name ?? '',
            'active_time' => $active->period_type == Active::PERIOD['fixed'] ? $active->period_start->format('Y-m-d').'至'.$active->period_end->format('Y-m-d') : '不限期',
            'created_at' => (string) ($active->created_at),
            'join_count' => $active->orderCount,
            'order_money' => !$active->total_fee ? '0.00元' : $active->total_fee.'元',
            'order_discount' => !$active->discount_fee ? '0.00元' : $active->discount_fee.'元',
            'status' => $this->getStatus($active->status),
            'state' => $active->status
        ];
    }
    
    public function getStatus($status) {
        switch($status) {
            case Active::STATUS['tostart']:
                return '待开始';
            case Active::STATUS['starting']:
                return '进行中';
            case Active::STATUS['pause']:
                return '已暂停';
            case Active::STATUS['finish']:
                return '已完成';
        }
    }
}
