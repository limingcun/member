<?php

namespace App\Http\Requests\Api\Address;

use App\Http\Requests\Api\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string',
            'phone' => 'required|string',
            'sex' => 'required|in:male,female',
            'address' => 'required|string',
            'description' => 'required|string',
            'latitude' => 'required|string',
            'longitude' => 'required|string',
        ];
    }
}
