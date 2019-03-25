<?php

namespace App\Http\Requests\Api\Order;

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
            'status' => 'in:CANCELED',
        ];
    }

    public function attributes()
    {
        return [
            'products' => '商品',
            'shop_id' => '门店',
            'products.*.quantity' => '商品数量'
        ];
    }
}
