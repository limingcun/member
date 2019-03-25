<?php

namespace App\Http\Requests\Admin;

class AdRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'image_id' => 'required',
        ];
    }

    public function attributes()
    {
        return [
            'image_id' => '图片',
        ];
    }
}
