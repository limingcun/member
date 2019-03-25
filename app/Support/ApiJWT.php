<?php

namespace App\Support;

use Illuminate\Contracts\Auth\Factory as Auth;
use Dingo\Api\Auth\Provider\JWT;
use Tymon\JWTAuth\JWTAuth;
use Dingo\Api\Routing\Route;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class ApiJWT extends JWT
{
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    public function authenticate(Request $request, Route $route)
    {
        $token = $this->getToken($request);

        try {
            if (! $user = $this->auth->guard('api')->setToken($token)->authenticate()) {
                throw new UnauthorizedHttpException('JWTAuth', 'Unable to authenticate with invalid token.');
            }
        } catch (JWTException $exception) {
            throw new UnauthorizedHttpException('JWTAuth', $exception->getMessage(), $exception);
        } catch (\Exception $e) {
            throw new UnauthorizedHttpException('JWTAuth', 'Unable to authenticate with invalid token.');
        }

        return $user;
    }
}
