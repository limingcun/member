<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Auth;
use IQuery;

class JwtAuth extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->header('Authorization') == null) {
            return response()->json(['messages' => 'token is missing'], 402);
        }
        if ($this->auth->parser()->setRequest($request)->hasToken()) {
            try {
                $this->auth->parseToken()->authenticate();  //获取token
            } catch (TokenInvalidException $exception) {
                return response()->json(['messages' => 'Token Signature could not be verified'], 401);
            } catch (Exception $e) {
                return response()->json(['messages' => 'token error'], 401);
            }
//            $admin = $request->header('Authfeeman');
//            if (!auth()->guard($admin)->user()) {
//                if ($admin == 'admin') {
//                    auth()->guard('admin')->logout();
//                } else {
//                    auth()->guard('m_admin')->logout();
//                }
//                return response()->json(['messages' => 'admin change'], 401);
//            }
        } else {
            return response()->json(['messages' => 'token is missing'], 402);
        }
        return $next($request);
    }
}