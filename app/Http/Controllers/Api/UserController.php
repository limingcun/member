<?php

namespace App\Http\Controllers\Api;

use App\Models\Comment;
use App\Models\Coupon;
use App\Models\GiftRecord;
use App\Transformers\Api\MemberClubTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Transformers\Api\UserTransformer;
use App\Http\Requests\Api\User\UpdateRequest;
use App\Http\Requests\Api\User\PhoneRequest;
use Qiniu\Auth;
use App\Models\User;
use IQuery;
use DB;

class UserController extends Controller
{
    public function userData()
    {
        $user = $this->user();
        \IQuery::redisDelete('hint_'.$this->user()->id); // 消除我的页面小红点
        return $this->response->item($user, new UserTransformer());
    }

    // 跟小程序耦合了，不是太好
    public function update(UpdateRequest $request)
    {
        $user = $this->user();
        if (!$request->get('encryptedData')) {
            $birthday = $user->birthday;
            // 用户不允许修改生日
            if (isset($birthday) && ($birthday != $request['birthday'])) {
                abort(403, '生日只能设置一次哦');
            }
            $status = $user->update(array_filter($request->only('name', 'sex', 'birthday', 'avatar_id', 'phone')));
            // 只有首次设置生日时才发券
            if ($status && !isset($birthday)) {
                $this->sendBirthdayCoupon($user->id);
            }
        } else {
            // 根据用户姓名判断是否需要更新用户信息
            if (!$user->name) {
                $data = $this->decode($request, $user);
                // 应该不可能
                if ($user->wxlite_open_id != $data['openId']) {
                    abort(403, '用户数据不匹配');
                }
                $user->name = $data['nickName'];
                $user->sex = $data['gender'] ? ($data['gender'] == 1 ? 'male' : 'female') : 'unkown';
                $user->image_url = $data['avatarUrl'];
                $user->wx_union_id = $data['unionId'] ?? null;
            }
            //保存unionid
            if (!$user->wx_union_id) {
                $data = $this->decode($request, $user);
                $user->wx_union_id = $data['unionId'];
            }
            if (!$user->district) {
                $data = $this->decode($request, $user);
                if ($data['country'] == '中国') {
                    $user->district = $data['province'] . $data['city'];
                } else {
                    $user->district = $data['country'];
                }
            }
            $user->save();
        }
        return $this->response->item($user, new UserTransformer());
    }
    
    /**
     * 更换头像
     */
    public function imagePic(Request $request) {
        $user = $this->user();
        $data = $this->decode($request, $user);
        $image_url = $data['avatarUrl'];
        $user->image_url = $image_url;
        $user->save();
        return compact('image_url');
    }

    public function getPhone(PhoneRequest $request)
    {
        $user = $this->user();
        $data = $this->decode($request, $user);
        if (isset($data['purePhoneNumber'])) {
            $user->update(['phone' => $data['purePhoneNumber']]);
        }
        return $this->response->array([
            'phone' => $data['purePhoneNumber'],
        ]);
    }

    /*
     * 获取解密数据信息
     */
    public function decode(Request $request, User $user)
    {
        $miniProgram = \EasyWeChat::miniProgram();
        $sessionKey = $user->wxlite_session_key;
        if (!$sessionKey) {
            abort(403, 'session key 错误');
        }
        $encryptedData = $request->get('encryptedData');
        $iv = $request->get('iv');
        $data = $miniProgram->encryptor->decryptData($sessionKey, $iv, $encryptedData);
        return $data;
    }

    /*
     * 小程序加密备用方法
     */
    public function decryptData(string $sessionKey, string $iv, string $encrypted)
    {
        $decryptData = \openssl_decrypt(
            base64_decode($encryptedData),
            'AES-128-CBC',
            base64_decode($sessionKey),
            OPENSSL_RAW_DATA,
            base64_decode($iv)
        );
        $data = json_decode($decryptData, true); //解密后的用户数据
        return $data;
    }

    /*
     * 判断是否绑定手机
     * 2001手机已绑定
     * 2002手机没有绑定
     */
    public function isBindPhone()
    {
        $user = $this->user();
        if ($user->phone) {
            return response()->json(['code' => 2001, 'msg' => '手机已绑定']);
        }
        return response()->json(['code' => 2002, 'msg' => '手机没有绑定']);
    }

    /*
     * 绑定手机号码
     * phone
     */
    public function bindPhone(Request $request)
    {
        $this->validate($request, [
            'phone' => 'required|regex:/^1[34578][0-9]{9}$/'
        ]);
        $user = $this->user();
        $res = $user->update($request->all('phone'));
        if ($res) {
            return 1;
        }
        return 0;
    }

    /*
     * app更新用户数据信息
     * field属性字段
     * value属性值
     */
    public function appUpdateUser(Request $request)
    {
        $this->validate($request, [
            'field' => 'required',
            'value' => 'required'
        ]);
        $user = $this->user();
        $res = $user->update([$request->field => $request->value]);
        return success_return();
    }

    /*
     * 手机app更新用户头像
     * 上传头像图片到七牛云
     */
    public function appUploadImage(Request $request)
    {
        $this->validate($request, [
            'fsize' => 'required'
        ]);
        $user = $this->user();
        $userid = $user->id;

        $accessKey = env('QINIU_ACCESS_KEY');
        $secretKey = env('QINIU_SECRET_KEY');
        $bucket = env('QINIU_BUCKET');
        $expires = 3600;
        $key = $userid . 'istore.jpg';
        $etag = 'istore';
        $fsize = $request->fsize;

        // 初始化Auth状态
        $policy = array(
            'callbackUrl' => env('APP_URL') . '/api/save_avatar',
            'callbackBody' => '{"key": "$(key)", "hash": "$(etag)", "fsize": "$(fsize)", "bucket": "$(bucket)", "userid": "$(x:userid)"}',
            'callbackBodyType' => 'application/json'
        );
        $auth = new Auth($accessKey, $secretKey);
        $upToken = $auth->uploadToken($bucket, null, $expires, $policy, true);
        return $upToken;
    }

    /*
     * 回调函数保存app头像进数据库
     */
    public function saveAvatar(Request $request)
    {
        $this->validate($request, [
            'userid' => 'required',
            'key' => 'required'
        ]);
        $user = User::findOrFail($request->userid);
        if ($user) {
            $user->update(['image_url' => $request->key]);
            return response()->json(['code' => 0, 'url' => $user->image_url]);
        }
        return response()->json(['code' => 2003]);
    }


    /**
     * 用户设置生日
     */
    public function setBirthday(Request $request)
    {
        $birthday = $this->user()->birthday;
        $method = $request->method();
        if ($method == 'GET') {
            // GET 查询用户是否设置生日
            $status = isset($birthday) ? true : false;
            return response()->json(['birthday' => $status]);
        } else if ($method == 'PUT') {
            // POST 给用户设置生日
            if (!isset($birthday) && isset($request['birthday'])) {
                $flag = IQuery::redisGet('set_birthday_'.$this->user()->id);
                // 处理时加锁
                if ($flag) {
                    return response()->json(['code' => 2001, 'msg' => '正在处理中...']);
                }
                IQuery::redisSet('set_birthday_'.$this->user()->id, 1,30);
                DB::beginTransaction();
                try {
                    if ($this->user()->update(['birthday' => $request['birthday']])) {
                        $this->sendBirthdayCoupon($this->user()->id);
                    }
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    \Log::error('SEND_BIRTHDAY_COUPON_ERROR', [$e]);
                    IQuery::redisDelete('set_birthday_'.$this->user()->id);
                    abort(500, '服务器出小差了，等会再来吧~');
                }
                IQuery::redisDelete('set_birthday_'.$this->user()->id);
                return success_return('设置成功');
            }
        }
        abort(403, '数据有问题哦，请稍后再试~');
    }

    // 设置生日后判断是否要发放生日券
    public function sendBirthdayCoupon($user_id)
    {
//        $is_birthday = $this->user()->select(['id', 'birthday'])->whereMonth('birthday', date('m'))->whereDay('birthday', '>=', date('d'))->first();
        $is_birthday = User::where('id', $user_id)->select(['id', 'birthday'])->whereMonth('birthday', date('m'))
            ->whereDay('birthday', '>=', date('d'))->first();
        if ($is_birthday) {
            // 生日在本月 且在今天之后
            $member = $this->user()->members()->select(['id', 'expire_time'])->first();
            $expire_time = Carbon::createFromTimestamp(strtotime($member->expire_time))->endOfDay();
            if ($expire_time >= Carbon::today()) {
                // 星球会员有效期内
                $coupon = Coupon::where('flag', Coupon::FLAG['fee_star_birthday'])->select('id')->first();
                $is_send = $this->user()->library()->select('id')->where('coupon_id', $coupon->id)->where('period_start', '>=', Carbon::today())->exists();
                if (!$is_send) {
                    // 不存在已发放的生日券
                    $birth = strtotime($is_birthday->birthday);
                    createCoupon('fee_star_birthday', $is_birthday->id, 1,
                        Carbon::create(date('Y'), date('m', $birth), date('d', $birth))->endOfDay(),
                        Carbon::create(date('Y'), date('m', $birth), date('d', $birth))->startOfDay());
                }
            }
        }
    }

    /**
     * 会员俱乐部、星球俱乐部接口
     * 获取用户go会员、星球会员的等级、经验、权益等
     */
    public function memberClub()
    {
        $user = $this->user();
        return $this->response->item($user, new MemberClubTransformer());
    }

    /**
     * 小程序页面引导提示
     */
    public function hint(Request $request)
    {
        $data = [];
        $type = $request['type'] ?? '';
        if ($type == 'user') {
            // 意见反馈回复提示
            $data['comment'] = $this->existsComment();
            $data['club'] = $this->existsBonus();
        } elseif ($type == 'bonus') {
            // 奖励中心新礼包提示
            $data['bonus'] = $this->existsBonus();
        } elseif ($type == 'index') {
            // 小程序首页 tab我的小红点引导提示
            $user_id = $this->user()->id;
            $red_hot = IQuery::redisGet('hint_'.$user_id);
            if ($red_hot) { // 因为升级之后发放的奖励礼包是在升级后12小时才会生效 故此处跟当前时间判断
                $data['index'] = strtotime($red_hot) < strtotime(Carbon::now()) ? true : false;
            } else {
                $data['index'] = false;
            }
        }
        return $data;
    }

//    /**
//     * 消除我的页面小红点
//     */
//    public function delHint(Request $request)
//    {
//        \IQuery::redisDelete('hint_'.$this->user()->id);
//        return success_return('success');
//    }


    // 奖励中心新记录
    public function existsBonus()
    {
        return $this->user()->giftRecord()->whereIn('gift_type', [GiftRecord::GIFT_TYPE['go_update_cash'],
            GiftRecord::GIFT_TYPE['go_update_buy_fee']])->where('start_at', '<=', Carbon::now())
            ->where('status', GiftRecord::STATUS['new'])->select('id')->exists();
    }

    // 反馈消息新回复
    public function existsComment()
    {
        return $this->user()->comment()->where('status', 1)->select('id')->exists();   // 1标识 为已回复
    }
}
