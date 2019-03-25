<?php

namespace App\Http\Requests\Admin;

class GrandRequest extends FormRequest
{
    /*
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required',
            'coupon_id' => 'exists:coupons,id',
            'grand_type' => 'required|integer',
            'scence' => 'required|integer',
            'range_type' => 'required_if:scence,0|integer',
            'count' => 'required_if:scence,0, 2|integer',
            'amount' => 'required_if:scence,1,2|integer',
            'grand_time' => 'required_if:grand_type,2|date',
            'user_ids' => 'required_if:range_type,1',
            'redis_path' => 'required_if:range_type,2',
//            'period_type' => 'required_if:scence,1,2',
//            'period_start' => 'fieldif:period_type,0,scence,1,2',
//            'period_end' => 'fieldif:period_type,0,scence,1,2',
//            'period_day' => 'fieldif:period_type,1,scence,1,2',
//            'unit_time' => 'fieldif:period_type,1,scence,1,2'
        ];
    }

    public function attributes()
    {
        return [
            'name' => '请输入发券活动名称',
            'coupon_id' => '喜茶券模板不存在',
            'grand_type' => '请选择发放时间|格式为整数',
            'scence' => '请选择使用场景|格式为整数',
            'range_type' => '请选择派发对象|格式为整数',
            'count' => '清输入限领数量|格式为整数',
            'amount' => '请输入发放数量|格式为整数',
            'grand_time' => '请输入发放时间|格式为时间格式',
            'user_ids' => '请输入指定用户',
            'redis_path' => '导入excel表错误',
//            'period_type' => '请选择有效期类型',
//            'period_start' => '请输入固定日期',
//            'period_end' => '请输入固定日期',
//            'period_day' => '请输入相对日期天数',
//            'unit_time' => '请选择时间维度'
        ];
    }
}
