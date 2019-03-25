<?php

namespace App\Http\Requests\Api\Order;

use App\Http\Requests\Api\FormRequest;

class ShopPatchRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'status' => 'in:CANCELED,WAIT_BUYER_CONFIRM_GOODS,TRADE_CLOSED,DISPATCHING_GOODS',
        ];
    }
}
