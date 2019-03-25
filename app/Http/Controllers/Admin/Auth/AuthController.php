<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Models\MPermission;
use App\Models\User;
use Lang;
use JWTAuth;
use EasyWeChat;
use App\Models\Admin;
use App\Services\Jwt;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use App\Http\Controllers\ApiController;
use Carbon\Carbon;
use IQuery;

class AuthController extends ApiController
{
    use ThrottlesLogins;

    /**
     * Issue a JWT token when valid login credentials are
     * presented.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function issueToken(Request $request)
    {

        $credentials = $request->only('name', 'password');
        $credentials['email'] = $credentials['name'];
        unset($credentials['name']);
        $token = auth()->guard('admin')->attempt($credentials);
        $user = auth()->guard('admin')->user();
        // Attempt to verify the credentials and create a token for the user.
//        if ($token && Admin::whereNull('deleted_at')->where('email', $credentials['email'])->count()) {
            // Clear the login locks for the given user credentials.
            $this->clearLoginAttempts($request);
            // All good so return the json with token and user.
            return $this->sendLoginResponse($user, $token);
//        }

        // Increments login attempts.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Return the token and current user authenticated.
     *
     * @param App\Admin $user
     * @param $token
     * @return \Illuminate\Http\JsonResponse
     */
    protected function sendLoginResponse(Admin $user, $token)
    {
        $user = $this->response->transform->item($user);
        // get time to live of token form JWT service.
        $token_ttl = (new Jwt($token))->getTokenTTL();

        return $this->response->json(compact('token', 'token_ttl', 'user'));
    }

    /*
     * 会员后台重新登录
     * 加密密文
     */
    public function reLoginAdmin(Request $request)
    {
        $this->validate($request, [
            'crypt' => 'required'
        ]);
        $crypt = $request->crypt;
        $admin = IQuery::redisGet($crypt);  //获取缓存管理员数据信息
        if ($admin) {
            IQuery::redisDelete($crypt);
            $token = \Auth::guard('admin')->fromUser($admin);
            return $this->sendLoginResponse($admin, $token);
        }
        return response()->json(['code' => 4004, 'msg' => '请重新登录']);
    }

    /**
     * Return error message after determining invalid credentials.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        $message = Lang::get('auth.failed');

        return $this->response->withUnauthorized($message);
    }

    /**
     * Redirect the user after determining they are locked out.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendLockoutResponse(Request $request)
    {
        $seconds = $this->limiter()->availableIn(
            $this->throttleKey($request)
        );

        $message = Lang::get('auth.throttle', ['seconds' => $seconds]);

        return $this->response->withTooManyRequests($message);
    }

    /**
     * Revoke the user's token.
     *
     * @return \Illuminate\Http\Response
     */
    public function revokeToken()
    {
        if (auth()->guard('admin')->user()) {
            auth()->guard('admin')->logout();
        }
        return $this->response->withNoContent();
    }

    /**
     * Refresh the user's token.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function refreshToken(Request $request)
    {
        $token = auth()->guard('api')->refresh();

        // get time to live of token form JWT service.
        $token_ttl = (new Jwt($token))->getTokenTTL();

        return $this->response->json(compact('token', 'token_ttl'));
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'code';
    }

    public function login()
    {
        $token = auth()->guard('m_admin')->attempt([
            'email' => \request('email'),
            'password' => \request('password')
        ]);
        $user = auth()->guard('m_admin')->user();
        if(!$user->status){
            return error_return(403, '被禁用');
        }
        if ($token) {
            // get time to live of token form JWT service.
            $token_ttl = (new Jwt($token))->getTokenTTL();
            if ($user->id == 1) {
                $permission = MPermission::all();
            } else {
                $permission = $user->role&&$user->role->status ? $user->role->permission : [];
            }
            return $this->response->json(compact('token', 'token_ttl', 'user', 'permission'));
        }
        $message = Lang::get('auth.failed');
        return error_return(-1, $message);
//        return $this->response->withUnauthorized($message);
    }

    public function logout()
    {
        if (auth()->guard('m_admin')->user()) {
            auth()->guard('m_admin')->logout();
        }
        return $this->response->withNoContent();
    }
}
