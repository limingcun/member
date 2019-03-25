<?php

namespace App\Http\Requests\Api\User;

use App\Http\Requests\Api\FormRequest;

class PhoneRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'iv' => 'required|string',
            'encryptedData' => 'required|string',
        ];
    }
}
