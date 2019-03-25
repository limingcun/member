<?php

namespace App\Http\Requests\Admin\Point;
use App\Http\Requests\Admin\FormRequest;
class RuleRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'score' => 'required|integer|min:1',
            'rmb_base' => 'required'
        ];
    }

    public function attributes()
    {
        return [
            'score' => '请输入积分|类型为整数|最小值为1',
            'rmb_base' => '请输入金额',
        ];
    }
}
