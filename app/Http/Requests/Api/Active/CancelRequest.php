<?php

namespace App\Http\Requests\Api\Active;

use App\Http\Requests\Api\FormRequest;

class CancelRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'order_id' => 'required|integer',
        ];
    }
}
