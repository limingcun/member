<?php

namespace App\Http\Requests\Admin;

class CouponRequest extends FormRequest
{
    /*
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required',
            'count' => 'required|integer',
            'period_type' => 'required|integer',
            'period_start' => 'date|nullable',
            'period_end' => 'date|nullable',
            'period_day' => 'integer|nullable',
            'use_limit' => 'required|integer',
            'policy' => 'required',
            'shop_limit' => 'required|integer',
            'cut' => 'required_if:policy,0',
            'cup' => 'required',
            'enough' => 'required_if:policy,0',
            'cup' => 'required_if:policy,1',
            'cup_type' => 'required_if:policy,1,3',
//            'category_ids' => 'fieldif:policy,1,cup_type,0',
//            'product_ids' => 'required_if:cup_type,1',
//            'material_ids' => 'required_if:cup_type,3',
            'buy' => 'required_if:policy,2',
            'fee' => 'required_if:policy,2',
            'discount' => 'required_if:policy,3',
            'share' => 'required_if:policy,4',
            'clsarr' => 'required_if:share,1'
        ];
    }

    public function attributes()
    {
        return [
            'name' => '请输入优惠券名称',
            'count' => '清输入发券数量|格式为整数',
            'period_type' => '请选择有效期类型|格式为整数',
            'period_start' => '格式为日期类型',
            'period_end' => '格式为日期类型',
            'period_day' => '格式为整数',
            'use_limit' => '请选择使用限制|格式为整数',
            'policy' => '请选择优惠券类型',
            'shop_limit' => '请选择门店限制|格式为整数',
            'cut' => '请输入门槛',
            'cup' => '请输入杯数',
            'enough' => '请输入门槛',
            'cup_type' => '请输入赠饮类型',
//            'category_ids' => '请选择赠饮类型',
//            'product_ids' => '请选择赠饮饮品',
//            'material_ids' => '请选择加料',
            'buy' => '请输入购买数量',
            'fee' => '请输入赠送数量',
            'discount' => '请输入折扣力度',
            'share' => '请选择是否共用',
            'clsarr' => '请选择共用券类型'
        ];
    }
}
