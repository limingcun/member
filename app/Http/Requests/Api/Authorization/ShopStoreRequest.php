<?php

namespace App\Http\Requests\Api\Authorization;

use App\Http\Requests\Api\FormRequest;

class ShopStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'shop_no' => 'required',
            'shop_code' => 'required'
        ];
    }

    public function attributes()
    {
        return [
            'shop_no' => '门店编号',
            'shop_code' => '门店授权码',
        ];
    }
}
