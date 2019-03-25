<?php

namespace App\Http\Controllers\Api;

use App\Models\PosMemberCode;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Authorization;
use Illuminate\Http\Request;
use App\Transformers\Api\AuthorizationTransformer;
use App\Http\Requests\Api\Authorization\StoreRequest;
use Qcloud\Sms\SmsSingleSender;
use App\Http\Requests\Api\User\AppRequest;
use Session;
use JWTAuth;
use IQuery;
use App\Http\Controllers\Api\AppController;

class AuthController extends Controller
{
    // redis存储短信验证数字
    const REDIS_MSG = 'laravel:app:msg';
    /*
     * 3001会员码成功
     * 3002会员码失败
     * 2001手机号码已绑定
     * 2002手机号码未绑定
     * 1101短信验证码错误
     * 1102手机号码已绑定
     * 1103短信发送失败
     */
    const CODE = [
        'bind' => 2001,
        'notbind' => 2002,
        'error' => 1101,
        'isbind' => 1102,
        'sendfail' => 1103,
        'code_success' => 3001,
        'code_error' => 3002,
    ];

    /**
     * 接收储值session_key
     * @param StoreRequest $request
     * @return type
     */
    public function updateSessionKey(Request $request)
    {
        $data = $request->data;
        $data = json_decode(urldecode($data), true);
        $user_id = $data['userId'];
        $code = $data['code'];
        $miniProgram = \EasyWeChat::miniProgram();
        $sessionAuth = $miniProgram->auth->session($code);
        $user = User::find($user_id);
        $user->update([
            'wxlite_session_key' => $sessionAuth['sessionKey']
        ]);
    }

    /**
     * 接收储值短信通知
     * @param Request $request
     */
    public function receivePhoneMsg(Request $request)
    {
        $data = $request->data;
        $data = json_decode(urldecode($data), true);
        $phone = $data['phone'];
        $zone = $data['zone'];
        $rand_num = $data['randNum'];
        try {
            $ssender = new SmsSingleSender(env('QCLOUD_APPID'), env('QCLOUD_APPKEY'));
            $params = [$rand_num];
            if ($zone == '86') {
                $templateid = env('QCLOUD_TEMPLATEID');
            } else if ($zone == '852') {
                $templateid = 250705;
            }
            $result = $ssender->sendWithParam($zone, $phone, $templateid,
                $params, env('QCLOUD_SMSSIGN'), '', '');  // 签名参数未提供或者为空时，会使用默认签名发送短信
            $rsp = json_decode($result);
            if ($rsp->result != 0) {
                return response()->json(['code' => self::CODE['sendfail'], 'msg' => '短信号码发送失败']);
            }
            return response()->json(['code' => 0, 'msg' => '短信号码发送成功']);
        } catch (\Exception $e) {
            return response()->json(['code' => self::CODE['sendfail'], 'msg' => '短信号码发送失败']);
        }
    }

    public function store(StoreRequest $request)
    {
        $miniProgram = \EasyWeChat::miniProgram();
        $data = $miniProgram->auth->session($request->get('code'));

        if (isset($data['errcode'])) {
            abort(403, $data['errmsg']);
        }

        $user = User::updateOrCreate(['wxlite_open_id' => $data['openid']], [
            'wxlite_open_id' => $data['openid'],
            'wxlite_session_key' => $data['session_key'],
            'last_login_at' => Carbon::now(),
        ]);

        $token = \Auth::guard('api')->fromUser($user);
        $authorization = new Authorization($token);
        return $this->response->item($authorization, new AuthorizationTransformer())
            ->setStatusCode(201);
    }

    /*
     * app端登录接口
     */
    public function appLogin(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'unionid' => 'required',
            'openid' => 'required'
        ]);

        $openid = $request->openid;
        if ($request->code) {
            $code = IQuery::redisGet(self::REDIS_MSG . $openid);
            if ($code != $request->code) {
                return response()->json(['code' => self::CODE['error'], 'msg' => '短信验证码错误']);
            }
        }
        $user = User::where('wx_union_id', $request->unionid)->first();
        $dataArr = [
            'name' => $request->name,
            'sex' => $request->sex,
            'wx_union_id' => $request->unionid ?? null,
            'phone' => $request->phone,
            'last_login_at' => Carbon::now()
        ];
        if (!$user) {
            $dataArr = array_merge($dataArr, [
                'image_url' => $request->image_url,
            ]);
            $user = User::create($dataArr);
        } else {
            if (!$user->image_url) {
                $dataArr = array_merge($dataArr, [
                    'image_url' => $request->image_url,
                ]);
            }
            $user->update($dataArr);
        }
        $app = new AppController();
        return $app->setUserToken($user);
    }

    /*
     * 保存手机号码
     */
    public function savePhone()
    {
    }


    /*
     * 短信验证发送
     */
    public function sendMsg(AppRequest $request)
    {
        $zone = $request->zone;
        $openid = $request->openid;
        $phone = $request->phone;
        $isBindPhone = User::where('phone', $phone)->first();
        if ($isBindPhone) {
            return response()->json(['code' => self::CODE['isbind']]);
        }
        try {
            $ssender = new SmsSingleSender(env('QCLOUD_APPID'), env('QCLOUD_APPKEY'));
            $rand_num = rand(1000, 9999);
            IQuery::redisSet(self::REDIS_MSG . $openid, $rand_num, 300);
            $params = [$rand_num];
            if ($zone == '86') {
                $templateid = env('QCLOUD_TEMPLATEID');
            } else if ($zone == '852') {
                $templateid = 250705;
            }
            $result = $ssender->sendWithParam($zone, $phone, $templateid,
                $params, env('QCLOUD_SMSSIGN'), '', '');  // 签名参数未提供或者为空时，会使用默认签名发送短信
            $rsp = json_decode($result);
            if ($rsp->result != 0) {
                return response()->json(['code' => self::CODE['sendfail'], 'msg' => '短信号码发送失败']);
            }
            echo $result;
        } catch (\Exception $e) {
            return response()->json(['code' => self::CODE['sendfail'], 'msg' => '短信号码发送失败']);
        }
    }

    /*
     * app登录判断是否绑定手机号
     * 2001手机号码已绑定
     * 2002手机号码未绑定
     */
    public function appBind(Request $request)
    {
        $this->validate($request, [
            'unionid' => 'required'
        ]);
        $user = User::where('wx_union_id', $request->unionid)->first();
        if ($user) {
            if ($user->phone) {
                return response()->json(['code' => self::CODE['bind'], 'msg' => '手机号码已绑定', 'phone' => $user->phone]);
            }
        }
        return response()->json(['code' => self::CODE['notbind'], 'msg' => '手机号码未绑定']);
    }

    public function checkCode()
    {
        $code = \request('code');
        $code = PosMemberCode::where('code', $code)->first();
        if ($code) {
            return response()->json([
                'code' => self::CODE['code_success'],
                'data' => [
                    'is_pay' => $code->is_pay,
                    'expire_at' => $code->expire_at,
                ]
            ]);
        } else {
            return response()->json([
                'code' => self::CODE['code_error'],
            ]);
        }

    }
}
