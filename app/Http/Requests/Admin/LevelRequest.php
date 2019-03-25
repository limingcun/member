<?php

namespace App\Http\Requests\Admin;

class LevelRequest extends FormRequest
{
    /*
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required',
            'exp_min' => 'required|integer',
            'exp_max' => 'required|integer',
            'exp_deduction' => 'required|integer'
        ];
    }

    public function attributes()
    {
        return [
            'name' => '请输入等级名称',
            'exp_min' => '请输入成长值最小值|请输入整数',
            'exp_max' => '请输入成长值最大值|请输入整数',
            'exp_deduction' => '请输入积分到期扣除成长值|请输入整数'
        ];
    }
}
