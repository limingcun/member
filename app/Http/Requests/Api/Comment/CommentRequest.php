<?php

namespace App\Http\Requests\Api\Comment;

use App\Http\Requests\Api\FormRequest;

class CommentRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'issue_type' => 'required|integer',
            'comment' => 'required|string'
        ];
    }
    
    public function attributes()
    {
        return [
            'issue_type' => '请选择反馈问题类型|格式为整型',
            'comment' => '请输入反馈问题|类型为字符串'
        ];
    }
}
