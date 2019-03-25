<?php

namespace App\Http\Requests\Api\Order;

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
            'is_takeaway' => 'required|boolean',
            'phone' => 'required_if:is_takeaway,false',
            'address_id' => 'required_if:is_takeaway,true|integer|exists:addresses,id',
            'products' => 'required|array',
            'products.*.sku_id' => 'required|integer|exists:skus,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.attributes' => 'array',
            'products.*.materials' => 'array',
            'shop_id' => 'required|integer|exists:shops,id',
            'period_id' => 'nullable|integer|exists:periods,id',
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
