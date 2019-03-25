<?php

namespace App\Http\Requests\Admin\Mall;

use App\Http\Requests\Admin\FormRequest;

class MallProductRequest extends FormRequest
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
            'score' => 'required|integer|min:1',
            'store' => 'required|integer|min:0',
            'limit_purchase' => 'required|integer|min:0',
            'mall_type' => 'required|in:1,2',
            'coupon_id' => 'exists:coupons,id',
            'skus' => 'required_if:is_specification,1|array',
        ];
    }

    public function attributes()
    {
        return [
            'name' => '请输入商品名称|商品最大长度为255',
            'score' => '请输入所需兑换积分|请输入整数|积分最小值为1',
            'store' => '请输入库存数量|请输入整数|库存最小值为1',
            'limit_purchase' => '请输入限购数量|请输入整数|限购数量最小值为0',
            'mall_type' => '请输入商品类型|类型为1或者2',
            'coupon_id' => '喜茶券模版不存在',
            'period_day' => '请输入过期天数|类型为整数',
            'skus' => '请输入sku规格值|规格值为数组'
        ];
    }
}
