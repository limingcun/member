<?php

namespace App\Transformers\Admin;

use App\Models\MallProduct;
use League\Fractal\TransformerAbstract;
use Carbon\Carbon;
use App\Models\Coupon;

class MallProductTransformer extends TransformerAbstract
{
    /*
     * 前端按钮灰置
     * nottab按钮不可点
     * istab按钮可点
     */
    const BTNSTATUS = [
        'nottab' => 0,
        'istab' => 1
    ];

    /**
     *
     * 积分商城商品数据获取和转化
     * @return array
     */
    public function transform(MallProduct $mall_product)
    {
        if ($mall_product->is_specification) {
            $store = $mall_product->skus->where('is_show',1)->sum('store');
        } else {
            $store = $mall_product->store;
        }
        $image_url = '';
        if ($mall_product->images->isNotEmpty()) {
            $image_url = env('QINIU_URL').$mall_product->images[0]->path;
        }
        $data = [
            'id' => $mall_product->id,
            'name' => $mall_product->name,
            'score' => $mall_product->score,
            'store' => $store,
            'is_specification' => $mall_product->is_specification,
            'sold_count' => $mall_product->sold_count,
            'status' => $mall_product->status,
            'shelf_time' => $mall_product->shelf_time,
            'mall_type' => $mall_product->mall_type,
            'image_url' => $image_url,
            'sort' => $mall_product->sort
        ];
        if ($mall_product->mall_type == MallProduct::MALLTYPE['invent']) {
            $data = array_merge($data, [
                'no_code' => $mall_product->no_code,
                'period_time' => $this->period($mall_product->source),
                'status_text' => $this->changeStatus($mall_product->status, $mall_product->store,
                    $mall_product->source->period_end, $mall_product->source->period_type),
                'coupon_store' => $mall_product->source->count,
                'expire_status' => $this->getExpire($mall_product->mall_type, $mall_product->source->period_type, $mall_product->source->period_end)
            ]);
        }
        if ($mall_product->mall_type == MallProduct::MALLTYPE['real']) {
            $specArr = [];
            if($mall_product->is_specification){
                $specification = $mall_product->specification;
                //拼接规格字符串
                foreach ($mall_product->specification_sort ?? [] as $name) {
                    $values = $specification->where('name', $name)->sortBy('sort')->pluck('value');
                    $specArr[]= $name . ':' . implode('/', $values->toArray());
                }
            }

            $data = array_merge($data, [
                'no_code' => $mall_product->no_code,
                'period_time' => '无',
                'specStr' => implode(',',$specArr),
                'status_text' => $this->realProductStatus($mall_product)
            ]);
        }
        return $data;
    }

    /*
     * 周期有效期范围内
     */
    public function period($source)
    {
        switch($source->unit_time) {
            case Coupon::TIMEUNIT['day']:
                $unit = '天';
                break;
            case Coupon::TIMEUNIT['month']:
                $unit = '月';
                break;
            case Coupon::TIMEUNIT['year']:
                $unit = '年';
                break;
            default:
                $unit = '天';
                break;
        }
        if (!$source->period_type) {
            $start = $source->period_start->format('Y-m-d') ?? '';
            $end = $source->period_end->format('Y-m-d') ?? '';
            return $start . '至' . $end;
        } else {
            $day = $source->period_day ?? '';
            return $day.$unit;
        }
    }

    /*
     * 实体商品状态
     */
    public function realProductStatus(MallProduct $mall_product)
    {
        if ($mall_product->is_specification) {
            $store = $mall_product->skus->sum('store');
        } else {
            $store = $mall_product->store;
        }
        switch ($mall_product->status) {
            case MallProduct::STATUS['wait']:
                return '待上架';
            case MallProduct::STATUS['takeup']:
                if ($store > 0) {
                    return '售卖中';
                } else {
                    return '已售罄';
                }
            case MallProduct::STATUS['takedown']:
                return '已下架';
            default:
                return;
        }
    }

    /*
     * 虚拟商品状态变更
     */
    public function changeStatus($status, $store, $end, $type)
    {
        switch ($status) {
            case MallProduct::STATUS['wait']:
                return '待上架';
            case MallProduct::STATUS['takeup']:
                if ($store > 0) {
                    if ($type == MallProduct::PERIODTYPE['positon']) {
                        return '售卖中';
                    }
                    if (Carbon::today()->format('Y-m-d') <= $end) {
                        return '售卖中';
                    } else {
                        return '已下架';
                    }
                } else {
                    if ($type == MallProduct::PERIODTYPE['positon']) {
                        return '已售罄';
                    }
                    if (Carbon::today()->format('Y-m-d') <= $end) {
                        return '已售罄';
                    } else {
                        return '已下架';
                    }
                }
            case MallProduct::STATUS['takedown']:
                return '已下架';
            default:
                return;
        }
    }

    /*
     * 获取过期状态
     */
    public function getExpire($mall_type, $period_type, $period_end)
    {
        if ($mall_type == MallProduct::MALLTYPE['real']) {
            return self::BTNSTATUS['istab'];
        }
        if ($period_type == MallProduct::PERIODTYPE['positon']) {
            return self::BTNSTATUS['istab'];
        }
        if (Carbon::today()->format('Y-m-d') > $period_end) {
            return self::BTNSTATUS['nottab'];
        }
        return self::BTNSTATUS['istab'];
    }
}
