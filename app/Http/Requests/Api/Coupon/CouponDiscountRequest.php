<?php

namespace App\Http\Requests\Api\Coupon;

use App\Http\Requests\Api\FormRequest;

class CouponDiscountRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'items' => 'required|array',
            'coupon_library_id' => 'required|string',
            'shop_id' => 'required|integer',
        ];
    }
}
