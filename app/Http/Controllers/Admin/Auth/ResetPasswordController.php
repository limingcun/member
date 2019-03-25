<?php

namespace App\Http\Controllers\Admin\Auth;

use Hash;
use Closure;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use Illuminate\Validation\ValidationException;

class ResetPasswordController extends ApiController
{
    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function reset(Request $request)
    {
        $this->validate($request, $this->rules(), $this->validationErrorMessages());


        $result = $this->checkAndReset($this->credentials($request), function ($user, $password) {
            $this->resetPassword($user, $password);
        });

        return $result ? $this->response->withNoContent()
                       : $this->response->withUnprocessableEntity('输入的原密码与用户原密码不一致，请重新输入！');
    }

    protected function checkAndReset($credentials, Closure $callback)
    {
        $user = auth()->guard('admin')->user();

        $token = auth()->guard('admin')->attempt([
            'name' => $user->name,
            'password' => array_get($credentials, 'old_password'),
        ]);

        if ($token) {
            $callback(auth()->guard('admin')->user(), array_get($credentials, 'password'));

            return true;
        }

        return false;
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @param  string  $password
     * @return void
     */
    protected function resetPassword($user, $password)
    {
        $user->password = Hash::make($password);

        $user->save();
    }

    /**
     * Get the password reset validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        return [
            'old_password' => 'required',
            'password' => 'required|confirmed|min:6',
        ];
    }

    /**
     * Get the password reset validation error messages.
     *
     * @return array
     */
    protected function validationErrorMessages()
    {
        return [];
    }

    /**
     * Get the password reset credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        return $request->only(
            'old_password',
            'password',
            'password_confirmation'
        );
    }
}
