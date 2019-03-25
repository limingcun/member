<?php

namespace App\Http\Controllers\Admin;

use function create_no;
use App\Http\Requests\Admin\Active\StoreRequest;
use App\Models\Active;
use App\Models\Admin;
use App\Models\Coupon;
use App\Http\Controllers\ApiController;
use App\Models\Order;
use function array_merge;
use Auth;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use JWTAuth;
use Log;
use function success_return;
use IQuery;
use Carbon\Carbon;
use App\Models\ActiveJoin;

class ActiveController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $actives = Active::select([
            'actives.id', 'actives.no','actives.erp_no', 'actives.name', 'actives.status', 'actives.type', 'actives.admin_id', 'actives.period_start', 'actives.period_end',
            'actives.created_at', 'actives.policy', 'actives.period_type',
            DB::raw('sum(orders.total_fee) as total_fee'),
            DB::raw('sum(active_joins.discount_fee) as discount_fee'),
            DB::raw('count(orders.id) as orderCount'),
        ])
            ->with([
                'admin' => function ($query) {
                    $query->select(['id', 'name']);
                }
            ])
            ->leftJoin('active_joins', 'active_joins.active_id', '=', 'actives.id')
            ->leftJoin('orders', 'active_joins.order_id', '=', 'orders.id')
            ->groupBy('actives.id')
            ->orderBy('id', 'desc')
            ->whereNull('active_joins.deleted_at')
            ->when(request('keyword'), function ($query, $value) {
                $query->where('actives.no','like', "%$value%")->orWhere('actives.name', 'like', "%$value%");
            })
            ->when(request('status'), function ($query, $value) {
                $query->where('actives.status', $value -= 1);
            })
            ->when(request('type'), function ($query, $value) {
                $query->where('actives.type', $value);
            })
            ->paginate(request('page_size') ?? config('app.page'));
        return $this->response->collection($actives);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        $user = Auth::user();
        try {
            DB::beginTransaction();
            if ($couponField = $request->get('coupon')) {
                $coupon = Coupon::create($couponField);
                //优惠券门店限制
                if ($couponField['shop_limit']) {
                    $coupon->shop()->sync($couponField['shop_ids']);
                }
                //优惠券商品限制
                if ($couponField['product_limit']) {
                    $coupon->product()->sync($couponField['product_ids']);
                }
            }
            $active = Active::create(array_merge($request->all(), [
                'coupon_id' => $coupon->id ?? 0,
                'admin_id' => $user->id,
                'no' => create_no('HD')
            ]));
            //活动门店限制
            if ($request->get('shop_limit')) {
                $active->shop()->sync($request->get('shop_ids'));
            }
            //活动用户限制
            if ($request->get('user_limit')) {
                if ($request->user_limit == Active::USERLIMIT['excel']) {
                    $user_ids = IQuery::redisGet($request->redis_path);
                } else {
                    $user_ids = explode(';', $request->get('user_ids'));
                }
                $active->user()->sync($user_ids);
            }
            DB::commit();
            return success_return($active);
        } catch (Exception $exception) {
            DB::rollBack();
            Log::info('ACTIVE', [$exception]);
            throw  $exception;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $Active = new Active();
        $active = $Active->findOrFail($id);
        $active->load([
            'coupon',
            'admin',
        ]);
        if ($active->shop_limit) {
            $active->shop_ids = $active->shop()->pluck('name');
        }
        if ($active->user_limit) {
            $active->user_ids = $active->user()->pluck('name');
        }
        $active->count = Order::select([
            DB::raw('ifnull(sum(orders.total_fee),0) as total_fee'),
            DB::raw('ifnull(sum(orders.discount_fee),0) as discount_fee'),
            DB::raw('count(orders.id) as count'),
        ])
            ->join('active_joins', 'active_joins.order_id', '=', 'orders.id')
            ->whereNull('active_joins.deleted_at')
            ->where('active_id', $id)
            ->first();
        return success_return($active);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $active = Active::findOrFail($id);
        $active['cup'] = $active->policy_rule['cup'];
        $active['free'] = $active->policy_rule['free'];
        if ($active->shop_limit) {
            $shop = $active->shop()->select('id', 'name', 'province')->get();
        }
        if ($active->user_limit) {
            $user = $active->user()->select('id', 'name')->get();
        }
        return compact('active', 'shop', 'user');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(StoreRequest $request, $id)
    {
        $active = Active::find($id);
        try {
            DB::beginTransaction();
            $active->update($request->all());
            //活动门店限制
            if ($request->get('shop_limit')) {
                $active->shop()->sync($request->get('shop_ids'));
            }

            $couponField = $request->get('coupon');
            $coupon = $active->coupon;
            if ($coupon && $couponField) {
                $coupon->update($couponField);
                //优惠券门店限制
                if ($couponField['shop_limit']) {
                    $coupon->shop()->sync($couponField['shop_ids']);
                }
                //优惠券商品限制
                if ($couponField['product_limit']) {
                    $coupon->product()->sync($couponField['product_ids']);
                }
            }
            //活动用户限制
            if ($request->get('user_limit')) {
                if ($request->user_limit == Active::USERLIMIT['excel']) {
                    $user_ids = IQuery::redisGet($request->redis_path);
                } else {
                    $user_ids = explode(';', $request->get('user_ids'));
                }
                $active->user()->sync($user_ids);
            }
            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            Log::info('ACTIVE', [$exception]);
        }
        return success_return();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $join = ActiveJoin::where('active_id', $id)->first();
        if ($join) {
            return response()->json(['msg' => '活动已存在订单，无法删除', 'code' => 4401]);
        }
        Active::destroy($id);
        return success_return();
    }

    /*
     * 更改活动状态
     */
    public function changeStatus($id)
    {
        $active = Active::findOrFail($id);
        switch ($active->status) {
            case Active::STATUS['pause']:
                $status = Active::STATUS['starting'];
                break;
            case Active::STATUS['starting']:
                $status = Active::STATUS['pause'];
                break;
            default:
                break;
        }
        $active->where('id', $id)->update(['status' => $status]);
        return success_return();
    }

    public function activeOrder($id)
    {
        $page = request('page_size') ?? config('app.page');
        $data = Order::select([
            DB::raw('DATE_FORMAT( orders.created_at, "%Y-%m-%d") as day'),
            DB::raw('sum(orders.total_fee) as total_fee'),
            DB::raw('sum(orders.discount_fee) as discount_fee'),
            DB::raw('sum(orders.payment) as payment'),
            DB::raw('count(1) as count')
        ])
            ->join('active_joins', 'active_joins.order_id', '=', 'orders.id')
            ->whereNull('active_joins.deleted_at')
            ->groupBy('day')
            ->orderBy('day', 'desc')
            ->skip((request('page', 1) - 1) * $page)
            ->where('active_id', $id)
            ->take($page)
            ->get();
        $total = Order::select(DB::raw('DATE_FORMAT( orders.created_at, "%Y-%m-%d") as day'))
            ->join('active_joins', 'active_joins.order_id', '=', 'orders.id')
            ->whereNull('active_joins.deleted_at')
            ->where('active_id', $id)
            ->groupBy('day')
            ->get();
        $pagination = new LengthAwarePaginator($data, count($total), $page);
        return success_return($pagination);
    }
}
