<?php

namespace App\Http\Requests\Admin;

class LevelRuleRequest extends FormRequest
{
    /*
     * @return array
     */
    public function rules()
    {
        return [
            'type' => 'required'
        ];
    }

    public function attributes()
    {
        return [
            'type' => '请选择过期类型'
        ];
    }
}
