<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use Session;
use IQuery;

class ApiAuth
{
    protected $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if ($request->header('Client') == 2 && !system_varable('app_service_switch')) {
            return response()->json(['message' => '', 'code' => 1003], 200);
        }
        if (!$request->header('Authorization')) {
            return response()->json(['message' => '', 'code' => 1002], 200);
        }
        // 任意jwt guard 取出source
        try {
            $source = $this->auth->guard('api')->getPayload()->get('source');
            if ($guard) {
                if ($source != $guard) {
                    return response()->json(['message' => '', 'code' => 1002], 200);
                }
                $user = $this->auth->guard($guard)->authenticate();
            } else {
                if ($source == 'api') {
                    $user = $this->auth->guard('api')->authenticate();
                } else {
                    $user = $this->auth->guard('shop')->authenticate();
                }
            }
            app('Dingo\Api\Auth\Auth')->setUser($user);
            return $next($request);
        } catch (\Exception $e) {
            #todo 会捕获其他所有异常，如 模型未找到会返回token一次
            return response()->json(['message' => '', 'code' => 1002], 200);
        }
    }
}
