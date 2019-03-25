<?php

namespace App\Http\Requests\Admin;

class AdminedRequest extends FormRequest
{
    /*
     * @return array
     */
    public function rules()
    {
        return [
            'old_password' => 'required',
            'password' => 'required|confirmed|min:6|numstr',
            'password_confirmation' => 'required'
        ];
    }

    public function attributes()
    {
        return [
            'old_password' => '请输入原始密码',
            'password' => '请输入新密码|两次密码不一致|最小输入6位|请输入数值或者字符串',
            'password_confirmation' => '请再次输入密码'
        ];
    }
}
