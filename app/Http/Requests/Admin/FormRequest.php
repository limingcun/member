<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest as Request;

class FormRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    public function messages()
    {
        return [
            'required' => ':attribute必须填写',
            'unique' => ':attribute已经存在，请重新填写',
            'confirmed' => ':attribute与密码不一致',
            'exists' => ':attribute不存在',
        ];
    }
}
