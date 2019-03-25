<?php

namespace App\Http\Requests\Api\Active;

use App\Http\Requests\Api\FormRequest;

class JoinRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'active_id' => 'required|integer',
            'user_id' => 'required|integer',
            'order_id' => 'required|integer',
            'discount_fee' => 'required',
        ];
    }
}
