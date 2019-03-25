<?php

namespace App\Http\Requests\Api\Order;

use App\Http\Requests\Api\FormRequest;

class OrderDiscountRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'orderIds' => 'required|array',
        ];
    }
}
