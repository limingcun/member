<?php

namespace App\Http\Requests\Admin;

class AdminRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $userid = 'unique:admins';
        $item = [];

        if ($this->method() == 'PATCH') {
            $userid = 'unique:admins,wechat_userid,' . $this->id . ',id';
        }

        if ($this->method() == 'POST') {
            $item = [
                'password' => 'required|confirmed',
                'password_confirmation' => 'required',
            ];
        }

        return array_merge([
            'wechat_userid' => $userid,
            'name' => 'required',
        ], $item);
    }

    public function attributes()
    {
        return [
            'wechat_userid' => '企业微信 User ID',
            'name' => '系统用户名',
            'password' => '密码',
            'password_confirmation' => '确认密码',
        ];
    }
}
