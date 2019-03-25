<?php

namespace App\Http\Requests\Api\Active;

use App\Http\Requests\Api\FormRequest;

class ShopActiveRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'shop_id' => 'required|integer',
            'user_id' => 'required|integer',
        ];
    }
}
