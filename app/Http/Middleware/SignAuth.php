<?php

namespace App\Http\Middleware;

use Closure;

class SignAuth
{
    private $secret = '';

    public function __construct()
    {
        $this->secret = env('CUSTOMER_SECRET');
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
        if ($request->header('X-Sign') != $this->sign($request->all())) {
            return [
                'code' => 400,
                'mes' => 'sign错误'
            ];
        }
        return $next($request);
    }

    public function sign($data)
    {
        $data = json_encode($data);
        $signChar = $data . $this->secret;
        $sign = base64_encode(MD5($signChar));
        return $sign;
    }
}
