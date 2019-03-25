<?php

namespace App\Http\Middleware;

use Closure;

class StorageAuth
{
    private $secret = '';

    public function __construct()
    {
        $this->secret = 'B77A242PLA36PRK0230A49F925CBNL3A';
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        \Log::info('wwwwww', [$request->data]);
        \Log::info('wwwwwwsign', [$request->dataSign]);
        if (!$request->dataSign) {
            $arr = ['code' => 401, 'msg' => 'sign不存在'];
            return $arr;
        }
        if ($request->dataSign != $this->sign($request->data)) {
            $arr = ['code' => 400, 'msg' => 'sign错误'];
            return $arr;
        }
        return $next($request);
    }

    public function sign($data)
    {
        $signChar = $data. '&' .$this->secret;
        $sign = md5($signChar);
        return $sign;
    }
}
