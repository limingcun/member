<?php

namespace App\Transformers\Admin;

use App\Models\Admin;
use App\Models\Member;
use App\Models\MemberCardRecord;
use App\Models\User;
use League\Fractal\TransformerAbstract;
use DB;

class StarConfigRecordsTransformer extends TransformerAbstract
{


    public function transform(MemberCardRecord $record)
    {
        $user = User::where('id', $record->user_id)->select(['id', 'name', 'phone', 'sex', 'birthday'])->first();
        $rule = json_decode($record->level_change, true);
        return [
            'id' => $user->id,
            'name' => $user->name,
            'phone' => $user->phone,
            'sex' => $user->sex,
            'birthday' => $user->birthday,
            'before' => [
                'star' => $rule['star'][0],
                'go' => $rule['go'][0]
            ],
            'after' => [
                'star' => $rule['star'][1],
                'go' => $rule['go'][1]
            ],
            'create_time' => $record->created_at->toDateTimeString(),
            'cfg_time' => $this->getCfgTime($record->card_type),
            'admin' => DB::table('istore_upms_user_t')->where('id', $record->admin_id)->value('name')
        ];
    }

    // 得到调配期限
    public function getCfgTime($type)
    {
        switch ($type) {
            case MemberCardRecord::CARD_TYPE['season']:
                return '3个月';
            case MemberCardRecord::CARD_TYPE['half_year']:
                return '6个月';
            case MemberCardRecord::CARD_TYPE['annual']:
                return '12个月';
        }
    }
}
