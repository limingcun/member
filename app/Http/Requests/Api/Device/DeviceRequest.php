<?php

namespace App\Http\Requests\Api\Device;

use App\Http\Requests\Api\FormRequest;

class DeviceRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'device_type' => 'required_without:device_id|string',
            'device_id' => 'required_without:device_type|string'
        ];
    }
}
