<?php

namespace App\Transformers\Api;

use App\Models\MallProduct;
use League\Fractal\TransformerAbstract;
use Carbon\Carbon;
use App\Models\Coupon;
use App\Policies\CouponLibrary\CashCouponPolicy;
use App\Policies\CouponLibrary\FeeCouponPolicy;
use App\Policies\CouponLibrary\BuyFeeCouponPolicy;
use App\Policies\CouponLibrary\DiscountCouponPolicy;
use App\Policies\CouponLibrary\QueueCouponPolicy;
use IQuery;
use App\Models\Member;

class MallProductItemTransformer extends TransformerAbstract
{
    const VALEN = [
        '最高价', '次高价', '次低价', '最低价'
    ];
    
    /**
     *
     * 积分商城商品数据获取和转化
     * @return array
     */
    public function transform(MallProduct $mall_product)
    {
        if ($mall_product->is_specification) {
            $store = $mall_product->skus->where('is_show', 1)->sum('store');
        } else {
            $store = $mall_product->store;
        }
        $data = [
            'id' => $mall_product->id,
            'name' => $mall_product->name,
            'score' => $mall_product->score,
            'is_specification' => $mall_product->is_specification,
            'store' => $store,
            'remark' => $mall_product->remark,
            'mall_type' => $mall_product->mall_type,
            'image_url' => $mall_product->images[0]->path ?? '',
            'image_gallery' => $mall_product->images->pluck('path'),
            'http_url' => env('QINIU_URL'),
            'gray_flag' => $this->grayFlag($mall_product->user_id, $mall_product->member_type)
        ];
        if ($mall_product->mall_type == MallProduct::MALLTYPE['invent']) {
            $source = $mall_product->source;
            $policy_rule = $source->policy_rule;
            $policy = $source->policy;
            if ($policy == CashCouponPolicy::class) {
                $contentText = '不可与现金券,赠饮券,买赠券,折扣券同时使用';
                $data = array_merge($data, [
                    'type' => '现金券',
                    'cut' => $this->proper($policy_rule, 'cut').'元',
                    'enough' => $this->enoughText($this->proper($policy_rule, 'enough')),
                    'type_num' => 0,
                    'content_text' => $contentText
                ]);
            } else if ($policy == FeeCouponPolicy::class) {
                $enough = '无门槛';
                $contentText = '不可与现金券,赠饮券,买赠券,折扣券同时使用';
                $cup_type = $policy_rule['cup_type'];
                switch($cup_type) {
                    case 0:
                        $cname = $this->getShopAndProduct($source->category);
                        break;
                    case 1:
                        $cname = $this->getShopAndProduct($source->product);
                        break;
                    case 2:
                        $cname = '配送费';
                        break;
                    case 3:
                        $cname = $this->getShopAndProduct($source->material);
                        break;
                    default:
                        $cname = '';
                        break;
                }
                $data = array_merge($data, [
                    'type' => '赠饮券',
                    'valen' => self::VALEN[$policy_rule['valen']] ?? '',
                    'enough' => $enough,
                    'cname' => $cname,
                    'cup_type' => $cup_type,
                    'type_num' => 1,
                    'content_text' => $contentText
                ]);
            } else if ($policy == BuyFeeCouponPolicy::class) {
                $enough = '无门槛';
                $contentText = '不可与现金券,赠饮券,买赠券,折扣券同时使用';
                $data = array_merge($data, [
                    'type' => '买赠券',
                    'buy' => $policy_rule['buy'],
                    'fee' => $policy_rule['fee'],
                    'enough' => $enough,
                    'valen' => self::VALEN[$policy_rule['valen']] ?? '',
                    'type_num' => 2,
                    'content_text' => $contentText
                ]);
            } else if ($policy == DiscountCouponPolicy::class) {
                $cup_type = $policy_rule['cup_type'];
                $enough = '无门槛';
                $contentText = '不可与现金券,赠饮券,买赠券,折扣券同时使用';
                switch($cup_type) {
                    case 0:
                        $cname = '订单金额';
                        break;
                    case 1:
                        $cname = $this->getShopAndProduct($source->product);
                        break;
                    case 2:
                        $cname = '配送费';
                        break;
                    case 3:
                        $cname = $this->getShopAndProduct($source->material);
                        break;
                    default:
                        $cname = '';
                        break;
                }
                $data = array_merge($data, [
                    'type' => '折扣券',
                    'discount' => $policy_rule['discount'],
                    'valen' => self::VALEN[$policy_rule['valen']] ?? '',
                    'cname' => $cname,
                    'cup_type' => $cup_type,
                    'enough' => $enough,
                    'type_num' => 3,
                    'content_text' => $contentText
                ]);
            } else if ($policy == QueueCouponPolicy::class) {
                $queue = app(QueueCouponPolicy::class);
                $share = $policy_rule['share'];
                $clsarr = $policy_rule['clsarr'];
                $contentText = $queue->queueShare($share, $clsarr);
                $enough = '无门槛';
                $data = array_merge($data, [
                    'type' => '优先券',
                    'content_text' => $contentText,
                    'enough' => $enough,
                    'type_num' => 4
                ]);
            }
            $data = array_merge($data, [
                'period_type' => $source->period_type,
                'period_start' => Carbon::parse($source->period_start)->format('Y-m-d'),
                'period_end' => Carbon::parse($source->period_end)->format('Y-m-d'),
                'period_day' => $source->period_day,
                'unit_time' => $this->returnUnit($source->unit_time),
                'shop_limit' => $source->shop_limit ? $this->shopLimit($source) : 1,
                'product_limit' => $source->product_limit ? $this->productLimit($source) : 1,
                'use_limit' => $source->use_limit
            ]);
        } else if ($mall_product->mall_type == MallProduct::MALLTYPE['real']) {
            if ($mall_product->is_specification) {
                $skuData = [];
                $specification = $mall_product->specification;
                //规格排序
                foreach ($mall_product->specification_sort ?? [] as $item) {
                    $data['specification'][] = [
                        'name' => $item,
                        'value' => $specification->where('name', $item)->unique()->toArray()
                    ];
                }
                foreach ($mall_product->skus as $sku) {
                    $skuItem = [];
                    $skuItem['specification'] =  $specification->whereIn('id', explode(',',$sku->specificationIds))->toArray();
                    $skuItem['id'] = $sku->id;
                    $skuItem['no'] = $sku->no;
                    $skuItem['is_show'] = $sku->is_show;
                    $skuItem['specificationIds'] = $sku->specificationIds;
                    $skuItem['store'] = $sku->store;
                    $skuData[$sku->specificationIds] = $skuItem;
                }
                $data['skus'] = $skuData;
            } else {
                $data['specification'] = [];
                $data['skus'] = [];
            }
        }
        return $data;
    }

    public function returnUnit($unit_time)
    {
        switch ($unit_time) {
            case Coupon::UNITIME['day']:
                return '天';
            case Coupon::UNITIME['month']:
                return '月';
            case Coupon::UNITIME['year']:
                return '年';
            default:
                return '天';
        }
    }

    /*
     * 返回规则各属性
     */
    public function proper($policy_rule, $field)
    {
        return $policy_rule[$field] ?? '';
    }
    
    /*
     * 获取门店和饮品
     */
    public function getShopAndProduct($res)
    {
        $arr = $res->pluck('name');
        $s = '';
        foreach ($arr as $a) {
            $s .= $a . ',';
        }
        $s = substr($s, 0, strlen($s) - 1);
        return IQuery::filterEmoji($s);
    }
    
    /**
     * 门槛返回文字
     * @param type $enough
     * @return string
     */
    public function enoughText($enough) {
        if ($enough) {
            return '订单满'.$enough.'元可用';
        }
        return '无门槛';
    }
    
    /**
     * 门店限制
     * @param type $limits
     * @return string
     */
    public function shopLimit($coupon) {
        $cityArr = [];
        $nameArr = [];
        $arrs = $coupon->shop()->select('city', 'name')->get();
        foreach($arrs as $arr) {
            if (!in_array($arr->city, $cityArr)) {
                $cityArr[] = $arr->city;
            }
        }
        foreach($cityArr as $city) {
            $nameArr[] = '【'.$city.'】:'.$this->cityCategory($coupon->shop(), 'city', $city);
        }
        return $nameArr;
    }
    
    /**
     * 商品限制
     * @param type $limits
     * @return string
     */
    public function productLimit($coupon)
    {   
        $categoryArr = [];
        $nameArr = [];
        $arrs = $coupon->product()->with(['category' => function($query) {
            $query->select('id', 'name');
        }])->select('id', 'category_id')->get();
        foreach($arrs as $arr) {
            if ($arr->category) {
                if (!in_array($arr->category->name, $categoryArr)) {
                    $categoryArr[$arr->category->id] = $arr->category->name;
                }
            }
        }
        foreach($categoryArr as $k => $category) {
            $nameArr[] = '【'.IQuery::filterEmoji($category).'】:'.$this->cityCategory($coupon->product(), 'category_id', $k);
        }
        return $nameArr;
    }
    
    /**
     * 分类获取数据
     * @param type $res
     * @param type $colmn
     * @param type $value
     * @return type
     */
    public function cityCategory($res, $colmn, $value) {
        $str = implode(',', $res->where($colmn, $value)->pluck('name')->toArray());
        return IQuery::filterEmoji($str);
    }
    
    /**
     * 判断星球和Go判断
     */
    public function grayFlag($user_id, $member_type) {
        if ($member_type == 0) {
            return 1;
        }
        $member = Member::where('user_id', $user_id)->first();
        $is_star = $this->isStarMember($member);
        if ($is_star) {
            return 1;
        }
        return 0;
    }
    
    /**
     * 判断是星球会员还是Go会员
     * $member会员
     */
    public function isStarMember(Member $member) {
        if (!$member->expire_time) {
            return false;
        } else {
            if (Carbon::parse($member->expire_time)->timestamp >= Carbon::today()->timestamp) {
                return true;
            } else {
                return false;
            }
        }
    }
}
