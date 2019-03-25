<?php

namespace App\Transformers\Api;

use App\Models\CouponLibrary;
use League\Fractal\TransformerAbstract;
use Carbon\Carbon;
use App\Models\Coupon;
use App\Models\Member;
use IQuery;

class CouponTransformer extends TransformerAbstract
{
    public function transform(CouponLibrary $coupon_library)
    {
        $libraryPolicy = app($coupon_library->policy);
        $coupon = $coupon_library->coupon;
        return [
            'id' => $coupon_library->id,
            'name' => $coupon_library->name,
            'period_start' => $coupon_library->period_start->format('Y.m.d'),
            'period_end' => $coupon_library->period_end->format('Y.m.d'),
            'shop_limit' => $coupon->shop_limit ? $this->shopLimit($coupon) : 1,
            'product_limit' => $coupon->product_limit ? $this->productLimit($coupon) : 1,
            'use_limit' => $coupon_library->use_limit,
            'category_limit' => $coupon->category_limit ? $this->categoryLimit($coupon->category) : '',
            'discountText' => $libraryPolicy->discountText($coupon_library) ?? null,
            'discountUnit' => $libraryPolicy->discountUnit($coupon_library) ?? null,
            'threshold' => $libraryPolicy->threshold($coupon_library) ?? null,
            'price_limit' => $libraryPolicy->priceLimit($coupon_library) ?? null,
            'type_num' => $libraryPolicy->typeNum() ?? null,
            'tab' => $coupon_library->tab,
            'status' => $coupon_library->status,
            'image_url' => env('QINIU_URL'),
            'lock' => $this->starLock($coupon->flag, $coupon_library),
            'contentText' => $libraryPolicy->contentText($coupon_library),
            'productShow' => $libraryPolicy->productShow($coupon_library),
            'origin_flag' => $this->originFlag($coupon),
            'interval_time' => $coupon_library->interval_time,
            'time_gray' => $this->timeGray($coupon_library->period_start, $coupon_library->interval_time)
        ];
    }
    
    /**
     * 时间未到置灰
     * $period_start开始过期时间
     * $interval_time时间段
     */
    public function timeGray($period_start, $interval_time) {
        if (Carbon::now()->timestamp < Carbon::parse($period_start)->timestamp) {
            return 0;
        }
        if ($interval_time == 1) {
            return 1;
        }
        if (!IQuery::rangeTime($interval_time)) {
            return 0;
        }
        return 1;
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
        } else if ($flag >=101 && $flag <=109) {
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

        /**
     * 星球会员状态
     * $flag模板券标志
     * $member会员数据
     */
    public function starLock($flag, $library) {
        if ($library->status == 3 || $library->status == 2) {
            return false;
        }
        $member = $library->member;
        if (in_array($flag, [Coupon::FLAG['fee_star_prime_day'], Coupon::FLAG['fee_star_birthday'], Coupon::FLAG['fee_star_anniversary'], Coupon::FLAG['fee_star_update']])) {
            $is_star = $this->isStarMember($member);
            if ($is_star) {
                return false;
            }
            return true;
        }
        return false;
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
