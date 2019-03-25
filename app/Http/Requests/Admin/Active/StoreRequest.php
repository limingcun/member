<?php

namespace App\Http\Requests\Admin\Active;

use App\Http\Requests\Admin\FormRequest;

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
            'name' => 'required|max:255',
            'policy' => 'required|max:255',
            'shop_limit' => 'required|integer',
            'period_start' => 'date',
            'period_end' => 'date',
            'type' => 'required|in:1,2',
            'coupon.name' => 'max:255',
            'coupon.period_start' => 'required_if:coupon.period_type,1|date',
            'coupon.period_end' => 'required_if:coupon.period_type,1|date',
            'coupon.period_day' => 'required_if:coupon.period_type,2|integer',
            'period_type' => 'required|in:1,2'
        ];
    }
}
