<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/30
 * Time: 14:57
 */

namespace App\Transformers\Api\Active;


use App\Models\Active;
use League\Fractal\TransformerAbstract;

class ActiveTransformer extends TransformerAbstract
{
    public function transform(Active $active)
    {
        return [
            'id' => $active->id,
            'name' => $active->name,
            'policy' => $active->policy,
            'policy_rule' => $active->policy_rule,
            'remark' => $active->remark,
            'use_limit' => $active->use_limit
        ];
    }
}