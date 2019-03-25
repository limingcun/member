<?php

namespace App\Http\Requests\Api\User;

use App\Http\Requests\Api\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'encryptedData' => 'required_without:name,sex,birthday|string',
            'iv' => 'required_with:encryptedData|string',

            'name' => 'string',
            'sex' => 'in:male,female,unknow',
            'birthday' => 'date',
            'avatar_id' => 'exists:images,id',
        ];
    }
}
