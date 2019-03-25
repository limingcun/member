<?php

namespace App\Transformers\Api;

use App\Models\Comment;
use App\Models\User;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use App\Services\CashStorage\Request as Srequest;

class UserTransformer extends TransformerAbstract
{
    const FOUNCODE = [
        'funcode' => 'A1.AC002'
    ];

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
        $member = $user->members()->select(['id', 'level_id', 'star_level_id', 'star_exp', 'expire_time', 'score_lock', 'message_tab'])->first();
        $star_level = $member->starLevel()->select(['name', 'exp_max'])->first();
        $star_level_name = $star_level->name ?? '';
        $exp_need = isset($star_level->exp_max) ? ($star_level->exp_max - $member->star_exp + 1) : 0;
        $expire_time = Carbon::createFromTimestamp(strtotime($member->expire_time))->endOfDay();
        $is_renew = strtotime($expire_time) < strtotime(Carbon::today()->addDays(15));
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'birthday' => $user->birthday,
            'sex' => $user->sex,
            'level' => $member->level->name,
            'star_level' => strtotime(Carbon::today()) > strtotime($expire_time)? 0 : self::STAR_ID[$star_level_name],
            'star_exp_need' => $exp_need,
            'jpush_id' => $user->jpush_id,
            'is_pwd' => !$user->password ? 0 : 1,
            'last_login_at' => (string) $user->last_login_at,
            'is_vip' => $user->is_vip,
            'created_at' => (string) $user->created_at,
            'updated_at' => (string) $user->updated_at,
            'avatar_url' => $user->avatar_id ? '' : $user->image_url,
            'uid' => $user->wx_union_id ? 1 : 0,
            'district' => $user->district,
            'has_comment' => $this->existsComment($user->id) ? true : false,
            'score_lock' => $member->score_lock,
            'QINIU_URL' => env('QINIU_URL'),
            'cash_storage_password' => $this->passwordStatus($user),
            'is_renew' => $is_renew,
            'expire_time' => $member->expire_time,
            'free_money' => $user->storage->free_money ?? 0,
            'message_tab' => $member->message_tab
            //'cash_storage_password' => $user->storage->password_status ?? 2
        ];
    }

    /**
     * 用户是否有反馈提交记录
     */
    public function existsComment($user_id)
    {
        return Comment::where('user_id', $user_id)->select('id')->exists();
    }

    /**
     * 判断钱包是否开户,是否设置密码
     * user_id用户id
     */
    public function passwordStatus($user) {
        if (!system_variable('istore_pay_on')) {
            return null;
        }
//        $password_status = $user->storage->password_status ?? 2;
//        if ($password_status == 1 || $password_status == 2) {
//            return $password_status;
//        }
        $data['contentData'] = json_encode(array('cooperator' => 'istore', 'userId' => $user->id));
        $data['funcode'] = self::FOUNCODE['funcode'];
        $data['cooperator'] = 'istore';
        $data['version'] = '1.0.0';
        $sre = new Srequest();
        $result = $sre->resultFunction($data);
        $contentData = $result['contentData'] ?? '';
        if ($contentData == '') {
            return null;
        }
        $contentData = json_decode($contentData, true);
        $secretStatus = $contentData['secretStatus'] ?? '';
        if ($secretStatus == 'SS00') {
            $password_status = 0;
        } else if ($secretStatus == 'SS01') {
            $password_status = 1;
        } else {
            $password_status = 2;
        }
        return $password_status;
    }
}
