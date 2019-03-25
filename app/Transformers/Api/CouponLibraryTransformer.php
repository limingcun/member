<?php

namespace App\Transformers\Api;

use App\Models\CouponLibrary;
use Carbon\Carbon;
use function dd;
use League\Fractal\TransformerAbstract;
use IQuery;

class CouponLibraryTransformer extends TransformerAbstract
{
    public function transform(CouponLibrary $couponLibrary)
    {
        $libraryPolicy = app($couponLibrary->policy);
        $coupon = $couponLibrary->coupon;
        return [
            'id' => $couponLibrary->id,
            'name' => $couponLibrary->name,
            'period_start' => $couponLibrary->period_start->format('Y.m.d'),
            'period_end' => $couponLibrary->period_end->format('Y.m.d'),
            'shop_limit' => $coupon->shop_limit ? $this->shopLimit($coupon) : 1,
            'product_limit' => $coupon->product_limit ? $this->productLimit($coupon) : 1,
            'use_limit' => $couponLibrary->use_limit,
            'category_limit' => $coupon->category_limit ? $this->categoryLimit($coupon->category) : '',
            'usable' => $couponLibrary->usable ?? null,
            'discount' => $couponLibrary->discount ?? null,
            'discountText' => $libraryPolicy->discountText($couponLibrary) ?? null,
            'discountUnit' => $libraryPolicy->discountUnit($couponLibrary) ?? null,
            'threshold' => $libraryPolicy->threshold($couponLibrary) ?? null,
            'type_num' => $libraryPolicy->typeNum() ?? null,
            'share' => $this->isShare($libraryPolicy->typeNum(), $couponLibrary->policy_rule),
            'contentText' => $libraryPolicy->contentText($couponLibrary),
            'productShow' => $libraryPolicy->productShow($couponLibrary),
            'price_limit' => $libraryPolicy->priceLimit($couponLibrary) ?? null,
            'origin_flag' => $this->originFlag($coupon),
            'unuse_text' => $couponLibrary->unuse_text ?? null,
            'interval_time' => $couponLibrary->interval_time
        ];
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
     * 添加分类限制
     * @param type $limits
     * @return type
     */
    public function categoryLimit($limits) {
        $arr = $limits->pluck('name');
        $s = '';
        foreach ($arr as $a) {
            $s .= $a . ',';
        }
        $s = substr($s, 0, strlen($s) - 1);
        return IQuery::filterEmoji($s);
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

    /*
     * 优惠券是否共用
     * $type券类型
     * $rule规则
     */
    public function isShare($type, $rule) {
        if ($type == 4) {
            $share = $rule['share'];
            if ($share == 0) {
                $s = '';
            } else {
                $s = $rule['clsarr'];
            }
        } else {
            $s = '';
        }
        return $s;
    }

    /**
     * 优惠券来源
     * $coupon券模板
     */
    public function originFlag($coupon) {
        $flag = $coupon->flag;
        if ($flag >= 11 && $flag <= 30) {
            $way = '随GO会员升级获取';
        } else if ($flag >= 31 && $flag <= 40) {
            $way = '随星球会员购卡获取';
        } else if ($flag >= 41 && $flag <= 50) {
            $way = '随星球会员首充获取';
        } else if ($flag >= 51 && $flag <= 70) {
            $way = '随星球会员福利获取';
        } else if ($flag >= 71 && $flag <= 80) {
            $way = '随星球会员升级获取';
        } else if ($flag >= 81 && $flag <= 90) {
            $way = '星球移民返劵获取';
        } else if ($flag >= 101 && $flag <= 109) {
            $way = '喜茶员工券';
        } else if ($flag >=110 && $flag <=120) {
            $way = '活动获取';
        } else if ($flag == -127) {
            $way = '其他券(旧数据,不作考虑)';
        } else if ($flag == 6) {
            $way = '喜茶金猪活动赠送';
        } else {
            $way = null;
        }
        return $way;
    }
 }
