<?php

namespace App\Http\Requests\Api\User;

use App\Http\Requests\Api\FormRequest;

class AppRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'zone' => 'required',
            'openid' => 'required',
            'phone' => 'required|digits_between:6,20'
        ];
    }
    
    public function attributes()
    {
        return [
            'zone' => '区号缺失',
            'openid' => 'openid缺失',
            'phone' => '手机号缺失|手机号格式不对'
        ];
    }
}
