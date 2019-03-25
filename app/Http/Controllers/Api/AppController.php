<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Authorization;
use App\Transformers\Api\AuthorizationTransformer;
use App\Models\User;
use Carbon\Carbon;
use Qcloud\Sms\SmsSingleSender;
use IQuery;
use JWTAuth;

class AppController extends Controller
{
    // redis存储短信验证数字
    const REDIS_MSG = 'laravel:app:msg';
    /*
     * 3001手机号已存在
     * 3002发送短信验证码失败
     * 3003请输入短信验证码
     * 3004短信验证码错误
     * 3005用户不存在
     * 3006用户名或密码错误
     * 3007原始密码错误
     * 3008用户已存在
     * 3009验证报错
     * 3010两次密码输入不正确
     * 3011原始密码错误
     * 3012该微信号已经绑定
     * 4001注册
     * 4002密码登录
     * 4003请重新登录
     * 4004验证码登录
     */
    const CODE = [
        'isexist' => 3001,
        'sendfail' => 3002,
        'nocode' => 3003,
        'codeerror' => 3004,
        'nouser' => 3005,
        'fail' => 3006,
        'pwderr' => 3007,
        'existuser' => 3008,
        'valid' => 3009,
        'pwdagain' => 3010,
        'pwdold' => 3011,
        'is_bind_wx' => 3012,
        'register' => 4001,
        'plogin' => 4002,
        'relogin' => 4003,
        'clogin' => 4004
    ];
    /*
     * code为验证码登录
     * pwd为密码登录
     */
    const LOGINTYPE = [
        'code' => 1,
        'pwd' => 2
    ];
    
    //腾讯云短信服务
    private $appid;
    private $appkey;
    private $templateid;
    private $sessign;

    public function __construct() {
        $this->appid = env('QCLOUD_APPID');
        $this->appkey = env('QCLOUD_APPKEY');
        $this->templateid = env('QCLOUD_TEMPLATEID');
        $this->sessign = env('QCLOUD_SMSSIGN');
    }
    
    /*
     * 判断是否登录还是注册
     * @return 4001去注册
     * @return 4002密码登录
     * @return 4004验证码登录
     */
    public function isLoginOrRigester(Request $request) {
        $this->validate($request, [
            'phone' => 'required|digits_between:6,20'
        ]);
        $phone = $request->phone;
        $user = User::where('phone', $phone)->first();
        if (!$user) {
            return response()->json(['code' => self::CODE['register'], 'msg' => '去注册']);
        }
        if ($user->password) {
            $code = self::CODE['plogin'];
        } else {
            $code = self::CODE['clogin'];
        }
        return response()->json(['code' => $code, 'msg' => '去登录']);
    }
    
    /*
     * APP手机号注册
     * phone手机号码
     * code短信验证码
     * @return 3003请输入短信验证码
     * @return 3004短信验证码错误
     * @return 3008用户已存在
     */
    public function appPhoneRegister(Request $request) {
        $this->validate($request, [
            'phone' => 'required|digits_between:6,20',
            'code' => 'required'
        ]);
        $phone = $request->phone;
        $code = $request->code;
        if (!$code) {
            return response()->json(['code' => self::CODE['nocode'], 'msg' => '请输入验证码']);
        } else {
            $redis_code = IQuery::redisGet(self::REDIS_MSG.$phone);
            if ($code != $redis_code) {
                return response()->json(['code' => self::CODE['codeerror'], 'msg' => '短信验证码错误']);
            }
        }
        $user = User::where('phone', $phone)->first();
        if ($user) {
            return response()->json(['code' => 'existuser', 'msg' => '用户已存在']);
        }
        $user = User::create([
            'name' => $this->randName($phone),
            'sex' => 'unknow',
            'phone' => $phone,
            'image_url' => 'https://avatars2.githubusercontent.com/u/40728385?s=200&v=4',
            'last_login_at' => Carbon::now()
        ]);
        return $this->setUserToken($user);
    }
    
    /*
     * 短信验证发送
     * phone手机号
     * zone区号
     * @return 3001手机号已存在
     */
    public function sendPhoneMsg(Request $request) {
        $this->validate($request, [
            'phone' => 'required|digits_between:6,20',
            'zone' => 'required'
        ]);
        $phone = $request->phone;
        $zone = $request->zone;
        try {
            $ssender = new SmsSingleSender($this->appid, $this->appkey);
            $rand_num = rand(1000, 9999);
            IQuery::redisSet(self::REDIS_MSG.$phone, $rand_num, 300);
            $params = [$rand_num];
            if ($zone == '86') {
                $templateid = $this->templateid;
            } else if ($zone == '852') {
                $templateid = 250705;
            }
            $result = $ssender->sendWithParam($zone, $phone, $templateid,
                $params, $this->sessign, '', '');  // 签名参数未提供或者为空时，会使用默认签名发送短信
            $rsp = json_decode($result);
            if ($rsp->result != 0) {
                return response()->json(['code' => self::CODE['sendfail'], 'msg' => '短信号码发送失败']);
            }
            echo $result;
        } catch(\Exception $e) {
            \Log::info('SEND_FAIL', [$e]);
            return response()->json(['code' => self::CODE['sendfail'], 'msg' => '短信号码发送失败']);
        }
    }
    
    /*
     * 随机生成姓名
     * $phone手机号
     */
    public function randName($phone) {
        $name = 'ht'.date('ymd').substr($phone, strlen($phone)-4).rand(100, 999);
        return $name;
    }
    
    /*
     * APP手机号登录
     * login_type手机号登录方式（1为短信验证码登录，2位密码登录）
     * @return 3005用户不存在
     * @return 3003请输入短信验证码
     * @return 3004短信验证码错误
     * @return 3006用户名或密码错误
     * 成功返回token,crypt
     */
    public function appPhoneLogin(Request $request) {
        $this->validate($request, [
            'phone' => 'required|digits_between:6,20',
            'login_type' => 'required|integer',
            'password' => 'required_if:login_type,2',
            'code' => 'required_if:login_type,1'
        ]);
        $phone = $request->phone;
        $login_type = $request->login_type;
        $user = User::where('phone', $phone)->first();
        if ($login_type == self::LOGINTYPE['code']) {
            $code = $request->code;
            if (!$code) {
                return response()->json(['code' => self::CODE['nocode'], 'msg' => '请输入验证码']);
            } else {
                $redis_code = IQuery::redisGet(self::REDIS_MSG.$phone);
                if ($code != $redis_code) {
                    return response()->json(['code' => self::CODE['codeerror'], 'msg' => '短信验证码错误']);
                }
            }
        } else if ($login_type == self::LOGINTYPE['pwd']) {
            $password = $request->password;
            if (!\Hash::check($password, $user->password)) {
                return response()->json(['msg' => '用户名或密码错误', 'code' => self::CODE['fail']]);
            }
        }
        $user->save();
        return $this->setUserToken($user);
    }
    
    /*
     * 重置密码
     * @return 3005该手机号没有注册
     * @return 3009密码不能含有非空字符
     * @return 3004短信验证码错误
     * @return 0重置密码成功
     */
    public function resetPwd(Request $request) {
        $this->validate($request, [
            'phone' => 'required|digits_between:6,20',
            'password' => 'required|string|min:8|max:20',
            'code' => 'required'
        ]);
        $password = $request->password;
        if (preg_match('[\s]', $password)) {
            return response()->json(['code' => self::CODE['valid'], 'msg' => '密码不能含有非空字符']);
        }
        $phone = $request->phone;
        $redis_code = IQuery::redisGet(self::REDIS_MSG.$phone);
        if ($request->code != $redis_code) {
            return response()->json(['code' => self::CODE['codeerror'], 'msg' => '短信验证码错误']);
        }
        $user = User::where('phone', $phone)->first();
        if (!$user) {
            return response()->json(['code' => self::CODE['nouser'], 'msg' => '该手机号没有注册']);
        }
        $user->password = bcrypt($password);
        $user->save();
        return success_return();
    }
    
    /*
     * 过期重新登陆
     * code
     * @return 4003重新登录
     * 成功返回token,crypt
     */
    public function loginAgain(Request $request) {
        $this->validate($request, [
            'token' => 'required'
        ]);
        try {
            $newToken = auth()->guard('api')->refresh();
        } catch (\Exception $e) {
            return response()->json(['code' => self::CODE['relogin'], 'msg' => '请重新登录']);
        }
        return $this->setUserToken($newToken, true);
    }
    
    /*
     * 设置token
     * $n为true是直接将$user改为token
     */
    public function setUserToken($user, $n = false) {
        if ($n) {
            $token = $user;
        } else {
            $token = \Auth::guard('api')->fromUser($user);
        }
        $authorization = new Authorization($token);
        return $this->response->item($authorization, new AuthorizationTransformer());
    }
    
    /*
     * 设置密码（用户没密码的情况）
     * code
     * @return 0设置密码成功
     * @return 3009密码不能含有非空字符
     * @return 3010两次密码输入不正确
     */
    public function storePwd(Request $request) {
        $this->validate($request, [
            'password' => 'required|string|min:8|max:20',
            'password_again' => 'required'
        ]);
        $user = $this->user();
        $pwd = $request->password;
        $pwd_again = $request->password_again;
        if ($pwd != $pwd_again) {
            return response()->json(['msg' => '两次密码输入不正确', 'code' => self::CODE['pwdagain']]);
        } else if (preg_match('[\s]', $pwd)) {
            return response()->json(['code' => self::CODE['valid'], 'msg' => '密码不能含有非空字符']);
        } else { 
            $user->password = bcrypt($pwd);
        }
        $user->save();
        return success_return();
    }
    
    /*
     * 修改密码（用户有密码的情况）
     * code
     * @return 0修改密码成功
     * @return 3009密码不能含有非空字符
     * @return 3010两次密码输入不正确
     * @return 3011原始密码输入错误
     */
    public function updatePwd(Request $request) {
        $this->validate($request, [
            'password_old' => 'required',
            'password' => 'required|string|min:8|max:20',
            'password_again' => 'required'
        ]);
        $user = $this->user();
        $pwd_old = $request->password_old;
        $pwd = $request->password;
        $pwd_again = $request->password_again;
         if (!\Hash::check($pwd_old, $user->password)) {
            return response()->json(['msg' => '原始密码输入错误', 'code' => self::CODE['pwdold']]);
        } else if ($pwd != $pwd_again){
            return response()->json(['msg' => '两次密码输入不正确', 'code' => self::CODE['pwdagain']]);
        } else if (preg_match('[\s]', $pwd)) {
            return response()->json(['code' => self::CODE['valid'], 'msg' => '密码不能含有非空字符']);
        } else {
            $user->password = bcrypt($pwd);
        }
        $user->save();
        return success_return();
    }
    
    /*
     * 退出账号
     * code
     * @return 0返回密码成功
     */
    public function appLogout() {
        if (auth()->guard('api')->user()) {
            auth()->guard('api')->logout();
        }
        return success_return();
    }
    
    /*
     * app绑定微信
     * @return code
     * 3012该微信号已经绑定
     * 0表示绑定成功
     */
    public function appBindWxchat(Request $request) {
        $this->validate($request, [
            'unionid' => 'required'
        ]);
        $unionid = $request->unionid;
        $is_user = User::where('wx_union_id', $unionid)->first();
        if ($is_user) {
            return response()->json(['code' => self::CODE['is_bind_wx'], 'msg' => '该微信号已经绑定']);
        }
        $user = $this->user();
        $user->update(['wx_union_id' => $unionid]);
        return success_return();
    }
}
