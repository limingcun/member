<?php
/**
 * Created by netbeanIDE.
 * User: limingcun
 * Date: 2018/9/7
 * Time: 上午9:22
 * desc: 优惠券控制器
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Admin\CouponRequest;
use App\Policies\CouponLibrary\CashCouponPolicy;
use App\Policies\CouponLibrary\FeeCouponPolicy;
use App\Policies\CouponLibrary\BuyFeeCouponPolicy;
use App\Policies\CouponLibrary\DiscountCouponPolicy;
use App\Policies\CouponLibrary\QueueCouponPolicy;
use Illuminate\Http\Request;
use App\Models\Coupon;
use App\Models\User;
use IQuery;
use Log;
use DB;
use Carbon\Carbon;
use App\Models\Category;
use App\Models\Product;
use App\Transformers\Admin\CouponDetailTransformer;

class CouponController extends ApiController
{
    /*
     * 4001操作失败
     */
    const CODE = [
        'error' => 4001
    ];
    const POLICY = [CashCouponPolicy::class, FeeCouponPolicy::class, BuyFeeCouponPolicy::class, DiscountCouponPolicy::class, QueueCouponPolicy::class];
    const HOMEPOLICY = [
        'cash' => 0,  //现金券
        'fee' => 1,  //赠饮券
        'buyfee' => 2, //买赠券
        'discount' => 3, //折扣券
        'queue' => 4  //优先券
    ];
    /*
     * 喜茶模板券列表
     * page_size页码
     * keyword关键字
     * statue状态
     * policy券类型
     * start开始时间
     * end结束时间
     */
    public function index(Request $request)
    {
        $page = $request->page_size != '' ? $request->page_size : config('app.page');
        $coupons = Coupon::with('grand.admin', 'mallProduct')->whereIn('flag', [0, 1, 2, 3])
            ->when($request->keyword, function ($query, $value) {
                $query->where(function ($query) use ($value){
                    $query->where('name', 'like', '%' . $value . '%')->orWhere('no', 'like', '%' . $value . '%');
                });
            })->when($request->start, function($query, $value) {
                $query->whereDate('created_at', '>=', $value);
            })->when($request->end, function($query, $value) {
                $query->whereDate('created_at', '<=', $value);
            });
            if ($request->status != '') {
                $coupons = $coupons->where('status', $request->status);
            }
            if ($request->policy != '') {
                $coupons = $coupons->where('policy', self::POLICY[$request->policy]);
            }
        return $this->response->collection($coupons->orderBy('id', 'desc')->paginate($page));
    }

    /*
     * 优惠券模板新增
     * name优惠券名称
     * coupon_type优惠券类型
     * cut优惠券面额
     * enough优惠券门槛
     * period_type过期类型 0为固定过期 1为相对过期
     * period_start过期开始时间
     * period_end过期结束时间
     * period_day过期相对日期
     * count库存
     * shop_limit门店限制
     * product_limit商品限制
     * use_limit使用场景
     */
    public function store(CouponRequest $request)
    {
        try {
            DB::beginTransaction();
            $p = $this->policyRule($request);
            $policy = $p[0];
            $policy_rule = $p[1];
            $coupon = Coupon::create(array_merge($request->all('name', 'period_type', 'shop_limit', 'count', 'image', 'use_limit'), [
                'policy' => $policy,
                'policy_rule' => $policy_rule,
                'no' => create_no('TN'),
                'period_start' => Carbon::parse($request->period_start)->startOfDay() ?? null,
                'period_end' => Carbon::parse($request->period_end)->endOfDay() ?? null,
                'period_day' => $request->period_day ?? 0,
                'unit_time' => $request->unit_time ?? 0,
                'product_limit' => $request->product_limit ?? 0,
                'category_limit' => $request->category_limit ?? 0,
                'material_limit' => $request->material_limit ?? 0,
                'admin_name' => auth()->guard('admin')->user()->name ?? auth()->guard('m_admin')->user()->name,
                'interval_time' => $request->interval_time ?? '1'
            ]));
            //门店和商品限制
            $this->pslimit($request, $coupon);
            DB::commit();
            return success_return();
        } catch (\Exception $e) {
            DB::rollback();
            Log::info('coupon_temp_store_error', [$e]);
            return response()->json(['code' => self::CODE['error']]);
        }
    }

    /*
     * 编辑与套用显示数据共用
     */
    public function edit($id)
    {
        $coupon = Coupon::find($id);
        $policy_rule = $coupon->policy_rule;
        if ($coupon->policy == self::POLICY[0]) {
            $coupon['policy'] = 0;
            $coupon['cut'] = $policy_rule['cut'];
            $coupon['enough'] = $policy_rule['enough'];
        } else if ($coupon->policy == self::POLICY[1]) {
            $coupon['policy'] = 1;
            $coupon['cup'] = $policy_rule['cup'];
            $coupon['valen'] = $policy_rule['valen'];
            $coupon['cup_type'] = $policy_rule['cup_type'];
        } else if ($coupon->policy == self::POLICY[2]) {
            $coupon['policy'] = 2;
            $coupon['valen'] = $policy_rule['valen'];
            $coupon['buy'] = $policy_rule['buy'];
            $coupon['fee'] = $policy_rule['fee'];
        } else if ($coupon->policy == self::POLICY[3]) {
            $coupon['policy'] = 3;
            $coupon['valen'] = $policy_rule['valen'];
            $coupon['discount'] = $policy_rule['discount'];
            $coupon['cup_type'] = $policy_rule['cup_type'];
        } else {
            $coupon['policy'] = 4;
            $coupon['share'] = $policy_rule['share'];
            $coupon['clsarr'] = $policy_rule['clsarr'];
        }
        if ($coupon->shop_limit) {
            $shop = $coupon->shop()->select('id', 'name', 'province')->get();
        }
        if ($coupon->product_limit) {
            $product = $coupon->product()->select('id', 'name')->get();
        }
        if ($coupon->category_limit) {
            $category = $coupon->category()->select('id', 'name')->get();
        }
        if ($coupon->material_limit) {
            $material = $coupon->material()->select('id', 'name')->get();
        }
        $image_url = env('QINIU_URL');
        return compact('coupon', 'shop', 'product', 'category', 'material', 'image_url');
    }

    /*
     * 优惠券模板新增
     * name优惠券名称
     * coupon_type优惠券类型
     * cut优惠券面额
     * enough优惠券门槛
     * period_type过期类型 0为固定过期 1为相对过期
     * period_start过期开始时间
     * period_end过期结束时间
     * period_day过期相对日期
     * count库存
     * shop_limit门店限制
     * product_limit商品限制
     * use_limit使用场景
     */
    public function update(CouponRequest $request, $id)
    {
        try {
            DB::beginTransaction();
            $coupon = Coupon::findOrFail($id);
            $p = $this->policyRule($request);
            $policy = $p[0];
            $policy_rule = $p[1];
            $coupon->update(array_merge($request->all('name', 'period_type', 'shop_limit', 'count', 'image', 'use_limit'), [
                'policy' => $policy,
                'policy_rule' => $policy_rule,
                'period_start' => Carbon::parse($request->period_start)->startOfDay() ?? null,
                'period_end' => Carbon::parse($request->period_end)->endOfDay() ?? null,
                'period_day' => $request->period_day ?? 0,
                'unit_time' => $request->unit_time ?? 0,
                'product_limit' => $request->product_limit ?? 0,
                'category_limit' => $request->category_limit ?? 0,
                'material_limit' => $request->material_limit ?? 0,
                'admin_name' => auth()->guard('admin')->user()->name ?? auth()->guard('m_admin')->user()->name,
                'interval_time' => $request->interval_time ?? '1'
            ]));
            //门店和商品限制
            $this->pslimit($request, $coupon);
            DB::commit();
            return success_return();
        } catch (\Exception $e) {
            DB::rollback();
            Log::info('coupon_temp_update_error', [$e]);
            return response()->json(['code' => self::CODE['error']]);
        }
    }

    /*
     * 返回策略信息
     * $request传入值
     * @return返回值(策略,策略规则)
     */
    public function policyRule(Request $request) {
        switch($request->policy) {
            case self::HOMEPOLICY['cash']:
                $policy = self::POLICY[0];
                $policy_rule = $request->all('cut', 'enough');
                break;
            case self::HOMEPOLICY['fee']:
                $policy = self::POLICY[1];
//                if ($request->cup_type == 0) {
//                    $request->offsetSet('category_limit', 1);
//                } else if ($request->cup_type == 1) {
//                    $request->offsetSet('product_limit', 1);
//                } else if($request->cup_type == 2) {
//                    $request->offsetSet('use_limit', 2);
//                } else {
//                    $request->offsetSet('material_limit', 1);
//                }
                $policy_rule = $request->all('cup', 'valen', 'cup_type');
                break;
            case self::HOMEPOLICY['buyfee']:
                $policy = self::POLICY[2];
                $policy_rule = $request->all('buy', 'fee', 'valen');
                break;
            case self::HOMEPOLICY['discount']:
                $policy = self::POLICY[3];
//                if ($request->cup_type == 1) {
//                    $request->offsetSet('product_limit', 1);
//                } else if($request->cup_type == 2) {
//                    $request->offsetSet('use_limit', 2);
//                } else if ($request->cup_type == 3) {
//                    $request->offsetSet('material_limit', 1);
//                }
                $policy_rule = $request->all('discount', 'valen', 'cup_type');
                break;
            case self::HOMEPOLICY['queue']:
                $policy = self::POLICY[4];
                $policy_rule = $request->all('share', 'clsarr'); //share表示共用,1表示可共用,0表示不可共用
                break;
        }
        return [$policy, $policy_rule];
    }


    /*
     * 门店限制
     * 商品限制
     * 加料限制
     * 分类限制
     * $request传入值，$coupon优惠券
     */
    public function pslimit(Request $request, Coupon $coupon)
    {
        // 门店限制
        if ($request->shop_limit) {
            $coupon->shop()->sync($request->shop_ids);
        }
        // 商品限制
        if ($request->product_limit) {
            $coupon->product()->sync($request->product_ids);
        }
        // 加料限制
        if ($request->material_limit) {
            $coupon->material()->sync($request->material_ids);
        }
        // 分类限制
        if ($request->category_limit) {
            $coupon->category()->sync($request->category_ids);
        }
    }

    /*
     * 优惠券模板停用和启用
     * $id模板券id
     */
    public function statusChange($id) {
        $coupon = Coupon::findOrFail($id);
        if ($coupon->status == Coupon::STATUS['start']) {
            $coupon->update(['status' => Coupon::STATUS['end']]);
        } else if ($coupon->status == Coupon::STATUS['end']) {
            $coupon->update(['status' => Coupon::STATUS['start']]);
        } else {
            return response()->json(self::CODE['error']);
        }
        return success_return();
    }

    /*
     * 模板券新增库存
     * number库存增加数量
     */
    public function addStore(Request $request, $id) {
        $this->validate($request, [
            'number' => 'required|integer|min:1|max:99999999'
        ]);
        $coupon = Coupon::findOrFail($id);
        $coupon->count += $request->number;
        $coupon->save();
        return success_return($coupon->count);
    }

    public function show($id) {
        $coupon = Coupon::with('shop', 'product', 'category', 'material')
            ->where('id',$id)
            ->orWhere('no',$id)
            ->firstOrFail();
        return $this->response->item($coupon, new CouponDetailTransformer());
    }

    public function allProShop() {
        $shop_num = \App\Models\Shop::count();
        $product_num = \App\Models\Product::count();
        return compact('shop_num', 'product_num');
    }
}