<?php

namespace App\Transformers\Admin;

use App\Models\MallProduct;
use League\Fractal\TransformerAbstract;
use Carbon\Carbon;
use App\Policies\CouponLibrary\CashCouponPolicy;
use App\Policies\CouponLibrary\FeeCouponPolicy;

class MallProductItemTransformer extends TransformerAbstract
{
    /**
     *
     * 积分商城商品数据获取和转化
     * @return array
     */
    public function transform(MallProduct $mall_product)
    {
        $data = [
            'id' => $mall_product->id,
            'no' => $mall_product->no,
            'name' => $mall_product->name,
            'score' => $mall_product->score,
            'store' => $mall_product->store,
            'is_specification' => $mall_product->is_specification,
            'limit_purchase' => $mall_product->limit_purchase,
            'mall_type' => $mall_product->mall_type,
            'remark' => $mall_product->remark,
            'image_path' => $mall_product->images,
            'specification_sort' => $mall_product->specification_sort,
            'status' => $mall_product->status,
            'image_url' => env('QINIU_URL'),
            'member_type' => $mall_product->member_type
        ];
        if ($mall_product->mall_type == MallProduct::MALLTYPE['invent']) {
            $source = $mall_product->source;
            $data = array_merge($data, [
                'policy' => $source->policy == CashCouponPolicy::class ? 0 : 1,
                'coupon_id' => $source->id,
                'coupon_name' => $source->name,
                'coupon_store' => $source->count,
                'coupon_no' => $source->no
            ]);
        } else if ($mall_product->mall_type == MallProduct::MALLTYPE['real']) {
            $skuData=[];
            $specification=$mall_product->specification;
            foreach ($specification as $spec){
                $data['specification'][$spec->name][] = $spec;
            }
            foreach ($mall_product->skus as $sku){
                $skuItem=[];
                $skuSpec=[];
                foreach ($specification->whereIn('id',explode(',',$sku->specificationIds))->toArray() as $item){
                    $skuSpec[$item['name']]=$item['value'];
                }
                $skuItem['specification']= $skuSpec;
                $skuItem['no']=$sku->no;
                $skuItem['is_show']=$sku->is_show;
                $skuItem['store']=$sku->store;
                $skuData[]=$skuItem;
            }
            $data['skus'] = $skuData;
        }
        return $data;
    }
}
