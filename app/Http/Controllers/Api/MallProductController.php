<?php
/**
 * Created by netbeanIDE.
 * User: limingcun
 * Date: 2018/07/10
 * Time: 上午11:42
 * desc: 小程序端积分商城
 */

namespace App\Http\Controllers\Api;

use App\Models\Address;
use App\Models\MallOrderEntity;
use App\Models\MallOrderExpress;
use App\Models\MallOrderLock;
use App\Models\MallSku;
use App\Models\MallSpecification;
use App\Services\WxMessage;
use App\Transformers\Api\MallProductItemTransformer;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Models\MallProduct;
use App\Transformers\Api\MallProductTransformer;
use App\Transformers\Api\MallOrder\MallOrderTransformer;
use App\Models\MallOrderItem;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\MallOrder;
use App\Models\MallOrderCoupon;
use App\Models\CouponLibrary;
use App\Models\Coupon;
use App\Models\MemberScore;
use App\Models\Member;
use Carbon\Carbon;
use IQuery;
use DB;
use Log;
use App\Services\JiPush;

class MallProductController extends ApiController
{
    /*
     * 编码状态
     * 2001售罄
     * 2002限购
     * 2003兑换失败
     * 2004积分不足
     * 2005积分异常
     * 2006商品下架
     * 2007用户已加锁
     */
    const CODE = [
        'soldout' => 2001,
        'limitbuy' => 2002,
        'error' => 2003,
        'scorelimit' => 2004,
        'scorelock' => 2005,
        'takedown' => 2006,
        'nolock' => 2007,
    ];

    /**
     * 积分商城区分go会员专区和星球会员专区
     */
    public function memberArea() {
        if (!system_variable('istore_mall_btn_on')) {
            return response()->json(['close' => 1]);
        }
        $go_arr = [];
        $star_arr = [];
        $products = MallProduct::where('status', '=', MallProduct::STATUS['takeup'])
            ->with([
                'images',
                'skus',
                'source'
            ])
            ->orderBy('sort', 'desc')->orderBy('created_at', 'desc')
            ->get();
        $products = $this->filter($products);
        foreach ($products as $product) {
            if ($product->member_type == 0 && count($go_arr) < 4) {
                $go_arr[] = $product;
            }
            if ($product->member_type == 1 && count($star_arr) < 4) {
                $star_arr[] = $product;
            }
        }
        $go_trans = $this->response->collection($go_arr, new MallProductTransformer());
        $star_trans = $this->response->collection($star_arr, new MallProductTransformer());
        return compact('go_trans', 'star_trans');
    }

    /*
     * 商品列表
     */
    public function index(Request $request)
    {
        $page = $request->page_size ?? config('app.page');
        $products = MallProduct::where('status', '=', MallProduct::STATUS['takeup'])
            ->with([
                'images',
                'skus',
                'source'
            ])
            ->orderBy('sort', 'desc')->orderBy('created_at', 'desc')
            ->where('member_type', $request->member_type)
            ->skip((request('page', 1) - 1) * config('app.page'))
            ->take($page)
            ->get();
        $products = $this->filter($products);
        $tabs = MallProduct::where('status', '=', MallProduct::STATUS['takeup'])->where('member_type', $request->member_type)->get();
        $tabs = $this->filter($tabs);
        $pagination = new LengthAwarePaginator($products, count($tabs), config('app.page'));
        return $this->response->collection($pagination, new MallProductTransformer());
    }

    /*
     * 过滤数据
     */
    public function filter($products)
    {
        $products = $products->filter(function ($query) {
            if ($query->mall_type == MallProduct::MALLTYPE['invent']) {
                if (!$query->source->period_type) {
                    if ($query->source->period_end < Carbon::today()) {
                        return false;
                    }
                }
            }
            return true;
        });
        return $products;
    }

    /*
     * 商品详情
     */
    public function show($id)
    {
        $mall_product = MallProduct::findOrFail($id);
        if ($mall_product->is_specification) {
            $mall_product->load([
                'specification' => function ($query) {
                    $query->orderBy('sort');
                },
                'skus' => function ($query) {
                    $query->orderBy('specificationIds');
                },
            ]);
        }
        $mall_product->user_id = $this->user()->id;
        return $this->response->item($mall_product, new MallProductItemTransformer());
    }

    /*
     * 商品限购数提示
     * code:
     * 2002购买该商品已达到限购数
     * 2005表示积分已经冻结
     * 0表示可以兑换
     */
    public function showMsg($id)
    {
        $user = $this->user();
        if ($user->members[0]->score_lock) {
            return response()->json(['code' => self::CODE['scorelock']]);
        }
        $mall_product = MallProduct::findOrFail($id);
        //判断该用户是否达到限购
        if (!$this->limitPurchase($mall_product, $id)) {
            return response()->json(['code' => self::CODE['limitbuy'], 'msg' => '购买该商品已达到限购数']);
        }
        return success_return();
    }


    /**
     * 锁定商品
     */
    public function exchangeLock()
    {
        $id = \request('id');
        $user = $this->user();
        if ($user->members[0]->score_lock) {
            return response()->json(['code' => self::CODE['scorelock']]);
        }
        if ($orderLock = MallOrderLock::where('user_id', $user->id)
            ->where('mall_product_id', $id)
            ->where('mall_sku_id', \request('sku_id', 0))
            ->where('status', MallOrderLock::STATUS_USEFUL)
            ->where('expire_at', '>', Carbon::now())
            ->first()) {
            //已有当前商品锁
            return success_return($orderLock);
        }
        DB::beginTransaction();
        try {
            $mall_product = MallProduct::lockForUpdate()->findOrFail($id);  //加入锁,对单条数据锁定

            //判断小程序是否下架
            if ($mall_product->status != MallProduct::STATUS['takeup']) {
                DB::commit();
                return response()->json(['code' => self::CODE['takedown'], 'msg' => '商品已下架']);
            }
            //判断积分是否不足以兑换
            if ($mall_product->score > $user->members[0]->usable_score) {
                DB::commit();
                return response()->json(['code' => self::CODE['scorelimit'], 'msg' => '积分不足']);
            }
            //判断是否达到限购数量
            if (!$this->limitPurchase($mall_product, $id)) {
                DB::commit();
                return response()->json(['code' => self::CODE['limitbuy'], 'msg' => '购买该商品已达到限购数']);
            }
            //多规格
            if ($mall_product->is_specification) {
                if (!$sku = MallSku::find(request('sku_id'))) {
                    return response()->json(['code' => 403, 'msg' => 'sku_id 错误']);
                }
                if ($sku->store == 0) {
                    DB::commit();
                    return response()->json(['code' => self::CODE['soldout'], 'msg' => '商品已售罄']);
                }
                $sku->decrement('store', 1);
            } else {
                //判断库存是否为0
                if ($mall_product->store == 0) {
                    DB::commit();
                    return response()->json(['code' => self::CODE['soldout'], 'msg' => '商品已售罄']);
                }
                //库存减1
                $mall_product->update([
                    'store' => bcsub($mall_product->store, 1)
                ]);
            }
            $orderLock = MallOrderLock::create([
                'user_id' => $user->id,
                'mall_product_id' => $id,
                'mall_sku_id' => $sku->id ?? 0,
                'status' => MallOrderLock::STATUS_USEFUL,
                'expire_at' => Carbon::now()->addSecond(config('mall.order_lock_second')),
            ]);
            DB::commit();
            return success_return($orderLock);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info('exchange:error', [$e]);
            return response()->json(['code' => self::CODE['error'], 'msg' => '锁定失败']);
        }
    }

    /**
     * 取消锁定
     */
    public function exchangeLockCancel()
    {
        if ($lock = MallOrderLock::where('mall_product_id', request('id'))
            ->where('status', MallOrderLock::STATUS_USEFUL)
            ->where('mall_sku_id', \request('sku_id', 0))
            ->first()) {
            $lock->update(['status' => MallOrderLock::STATUS_CANCEL]);
            $mall_product = $lock->product;
            if ($mall_product->is_specification) {
                $lock->sku()->increment('store', 1);
            } else {
                $mall_product->update([
                    'store' => bcadd($mall_product->store, 1)
                ]);
            }
            return success_return();
        } else {
            return error_return(self::CODE['nolock'], '未找到锁');
        }
    }

    /*
     * 商品兑换
     * $id商品id
     * code:
     * 2001商品已售罄
     * 2002购买该商品已达到限购数
     * 2003兑换失败
     * 2004兑换积分不足
     * 2005积分锁
     * 2006商品已下架
     * 0表示兑换成功
     */
    public function exchange($id)
    {
        $user = $this->user();
        if ($user->members[0]->score_lock) {
            return response()->json(['code' => self::CODE['scorelock']]);
        }
        $mall_product = MallProduct::find($id);
        if ($mall_product->is_specification) {
            if (!$sku = MallSku::find(request('sku_id'))) {
                return response()->json(['code' => 403, 'msg' => 'sku_id 错误']);
            }
        }
        //查询锁定库存
        if (!$orderLock = MallOrderLock::where('user_id', $user->id)
            ->where('mall_product_id', $mall_product->id)
            ->where('mall_sku_id', $sku->id ?? 0)
            ->where('mall_order_id', 0)
            ->where('status', MallOrderLock::STATUS_USEFUL)
            ->where('expire_at', '>', Carbon::now())
            ->first()) {
            return response()->json(['code' => 403, 'msg' => '未找到订单锁']);
        }
        DB::beginTransaction();
        try {
            //保存商城订单
            $mall_order = $this->saveMallOrder($mall_product);
            if ($mall_product->mall_type == MallProduct::MALLTYPE['invent']) {  //虚拟商品兑换
                //相对个人生成虚拟优惠券
                $library = $this->createNewCoupon($mall_product);
                //保存订单虚拟商品信息
                $mall_order_coupon = $this->saveMallOrderCoupon($mall_product, $library);
                $item_type = MallOrderCoupon::class;
                $item_id = $mall_order_coupon->id;
                //虚拟券模板减1
                $this->couponCountDelete($mall_product);
            } else if ($mall_product->mall_type == MallProduct::MALLTYPE['real']) {  //实体商品兑换
                if (!$address_id = \request('address_id')) {
                    return response()->json(['code' => 403, 'msg' => '实体商品必须传地址']);
                }
                $address = Address::findOrFail($address_id);
                $mall_order->express()->create([
                    'address_id' => $address_id,
                    'name' => $address->name,
                    'phone' => $address->phone,
                    'address' => ($address->complete_address ?? $address->address) . $address->description,
                ]);
                if ($mall_product->is_specification) {
                    $speces = MallSpecification::whereIn('id', explode(',', $sku->specificationIds))->get();
                    $speces = $speces->sort(function ($a, $b) use ($mall_product) {
                        return array_search($a->name, $mall_product->specification_sort) > array_search($b->name, $mall_product->specification_sort);
                    });
                    $speces = array_merge($speces->toArray(), []);
                } else {
                    $speces = [];
                }
                $entity = MallOrderEntity::create([
                    'specifications' => $speces
                ]);
                $item_type = MallOrderEntity::class;
                $item_id = $entity->id;
            }
            //保存orderItem
            $this->saveOrderItem($mall_product, $mall_order, $item_type, $item_id, $sku->id ?? 0);
            //生成积分记录(积分商城),保存个人积分数据信息
            $this->scoreMemberChange($mall_order);

            //更新订单锁
            $orderLock->update([
                'mall_order_id' => $mall_order->id,
                'status' => MallOrderLock::STATUS_USED,
            ]);
            //商品销量加1
            $mall_product->update([
                'sold_count' => bcadd($mall_product->sold_count, 1)
            ]);
            //消息通知提醒
            $this->sendMsgForm($mall_order, $mall_product);
            DB::commit();
            return success_return();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info('ERROR_INFO', [$user->name . '兑换' . $mall_product->name . '出错']);
            Log::info('exchange:error', [$e]);
            return response()->json(['code' => self::CODE['error'], 'msg' => '兑换出错']);
        }
    }


    /*
     * 判断是否达到限购数量
     */
    public function limitPurchase(MallProduct $mall_product, $id)
    {
        $user = $this->user();
        if (!$mall_product->limit_purchase) {
            return true;
        }
        //判断该用户是否达到限购
        $res = $user->mallOrder()->where('status', '!=', MallOrder::STATUS['refund'])->with('item')->get()->filter(function ($r) use ($id) {
            if ($id == $r->item->mall_product_id) {
                return true;
            }
            return false;
        });
        if ($mall_product->limit_purchase <= count($res)) {
            return false;
        }
        return true;
    }

    /*
     * 保存积分商城订单
     * @param $mall_product商城商品对象
     */
    public function saveMallOrder(MallProduct $mall_product)
    {
        $user = $this->user();
        $mall_order = new MallOrder;
        $mall_order->no = IQuery::createNo();
        $mall_order->user_id = $user->id;
        $mall_order->score = $mall_product->score;
        $mall_order->status = $mall_product->mall_type == MallProduct::MALLTYPE['invent'] ? MallOrder::STATUS['success'] : MallOrder::STATUS['wait_dispatch'];
        $mall_order->exchange_time = Carbon::now();
        $mall_order->remark = $mall_product->remark ? $mall_product->remark : '默认';
        $mall_order->mall_type = $mall_product->mall_type;
        $mall_order->form_id = \request('form_id');
        $mall_order->is_express = \request('address_id') ? 1 : 0;
        $mall_order->origin_from = \request('origin_from') ?? 'MINI';
        $mall_order->save();
        return $mall_order;
    }

    /*
     * 保存订单虚拟商品信息
     * @param $mall_product商城商品对象
     */
    public function saveMallOrderCoupon(MallProduct $mall_product, CouponLibrary $library)
    {
        $source = $mall_product->source;
        $mall_order_coupon = new MallOrderCoupon;
        $mall_order_coupon->no = $source->no;
        $mall_order_coupon->policy = $source->policy;
        $mall_order_coupon->policy_rule = $source->policy_rule;
        $mall_order_coupon->period_type = $source->period_type;
        $mall_order_coupon->period_start = !$source->period_type ? $source->period_start : Carbon::now()->format('Y-m-d');
        $mall_order_coupon->period_end = !$source->period_type ? $source->period_end : Coupon::getTimePeriod($source);
        $mall_order_coupon->period_day = $source->period_day;
        $mall_order_coupon->shop_limit = $source->shop_limit;
        $mall_order_coupon->product_limit = $source->product_limit;
        $mall_order_coupon->code_id = $library->code_id;
        $mall_order_coupon->use_limit = $source->use_limit;
        $mall_order_coupon->save();
        return $mall_order_coupon;
    }

    /*
     * 生成虚拟优惠券
     * @param $mall_product商城商品对象
     */
    public function createNewCoupon(MallProduct $mall_product)
    {
        $user = $this->user();
        $source = $mall_product->source;
        $library = $user->library()->create([
            'name' => $mall_product->name,
            'user_id' => $user->id,
            'order_id' => 0,  //0为无绑定订单
            'coupon_id' => $source->id,
            'policy' => $source->policy,
            'policy_rule' => $source->policy_rule,
            'period_start' => !$source->period_type ? $source->period_start : Carbon::now()->format('Y-m-d'),
            'period_end' => !$source->period_type ? $source->period_end : Coupon::getTimePeriod($source),
            'status' => CouponLibrary::STATUS['surplus'],
            'code_id' => $source->no . $this->createNumber($mall_product->source_id),
            'use_limit' => $source->use_limit,
            'tab' => CouponLibrary::NEWTAB['new'] //1为新优惠券标识
        ]);
        return $library;
    }

    /*
     * 生成优惠券编码
     * coupon_id优惠券id
     */
    public function createNumber($coupon_id)
    {
        $res = CouponLibrary::where('coupon_id', $coupon_id)->orderBy('code_id', 'desc')->select('code_id')->first();
        if ($res) {
            return IQuery::strPad(intval(substr($res->code_id, 13, 9)) + 1);
        }
        return IQuery::strPad(1);
    }

    /*
     * 保存orderItem
     * @param $mall_product商城商品对象
     * @param $mall_order商城订单对象
     * $item_type多态关联类型
     * $item_id多态关联id
     */
    public function saveOrderItem(MallProduct $mall_product, MallOrder $mall_order, $item_type, $item_id, $sku_id)
    {
        $mall_order_item = new MallOrderItem;
        $mall_order_item->name = $mall_product->name;
        $mall_order_item->mall_order_id = $mall_order->id;
        $mall_order_item->mall_product_id = $mall_product->id;
        $mall_order_item->mall_sku_id = $sku_id;
        $mall_order_item->source_type = $item_type;
        $mall_order_item->source_id = $item_id;
        $mall_order_item->remark = $mall_product->remark ? $mall_product->remark : '默认';
        $mall_order_item->save();
    }

    /*
     * 保存用户数据信息
     * @param $mall_order商城订单对象
     */
    public function scoreMemberChange(MallOrder $mall_order)
    {
        $user = $this->user();
        $usable_score = $user->members[0]->usable_score;
        $used_score = $user->members[0]->used_score;
        MemberScore::create([
            'user_id' => $user->id,
            'source_id' => $mall_order->id,
            'source_type' => MallOrder::class,
            'score_change' => $mall_order->score,
            'method' => MemberScore::METHOD['change'],
            'description' => '兑换' . $mall_order->item->name . '商品'
        ]);
        $memberData = [
            'usable_score' => bcsub($usable_score, $mall_order->score),
            'used_score' => bcadd($used_score, $mall_order->score)
        ];
        if ($mall_order->mall_type == MallOrder::MALLTYPE['invent']) {
            $memberData = array_merge($memberData, [
                'new_coupon_tab' => Member::NEWTAB['new']
            ]);
        }
        //个人可用积分减少,已使用积分添加
        $user->members()->update($memberData);
    }

    /*
     * 消息服务通知提醒
     */
    public function sendMsgForm(MallOrder $mall_order, MallProduct $mall_product)
    {
        $user = $this->user();
        //小程序服务通知
        if ($mall_order->origin_from == 'MINI') {
            $WxMessage = new WxMessage();
            $WxMessage->exchangeMessage($mall_order);
        } else if ($mall_order->origin_from == 'IOS') { //APP极光推送
            $jpush = new JiPush;
            $jpush->sendAppMsg((string)$user->id, '兑换' . $mall_product->name . '成功通知');
        }
        if (!$mall_product->member_type) {
            \App\Models\Message::couponsGetMsg($user->id);
            $member = $user->members()->first();
            $member->update(['message_tab' => $member->message_tab + 1]);
        }
    }

    /**
     * 模板券库存减1
     * @param MallProduct $mall_product
     */
    public function couponCountDelete(MallProduct $mall_product)
    {
        $coupon = $mall_product->source;
        $mall_product->source()->update([
            'count' => bcsub($coupon->count, 1)
        ]);
    }


    /*
     * 兑换记录
     */
    public function exchRecord()
    {
        $user = $this->user();
        $records = $user->mallOrder()
            ->with([
                'item',
                'item.source',
            ])
            ->orderBy('id', 'desc')
            ->paginate(config('app.page'));
        return $this->response->collection($records, new MallOrderTransformer());
    }
}