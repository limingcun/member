<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use App\Models\CardCodeOrder;
use App\Models\MemberCardRecord;
use App\Transformers\Admin\CardOrderTransformer;
use App\Transformers\Admin\CDKEYTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use IQuery;
use DB;
use Maatwebsite\Excel\Excel;


class StarCDKEYController extends ApiController
{

    /**
     * 兑换码订单列表
     */
    public function cardOrderList(Request $request)
    {
        $card_orders = CardCodeOrder::when($request['keyword'], function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('id', $value)
                    ->orWhere('name', $value)
                    ->orWhere('phone', $value)
                    ->orWhere('email', $value);
            });
        })->when($request['id'], function ($query, $value) {
            $query->where('id', '=', $value);
        })->when($request['start_time'], function ($query, $value) {
            $query->where('created_at', '>=', $value);
        })->when($request['end_time'], function ($query, $value) {
            $query->where('created_at', '<=', $value . ' 23:59:59');
        })->when($request['status'], function ($query, $value) {
            $query->where('status', $value);
        })->select(['id', 'name', 'phone', 'email', 'address', 'status', 'created_at', 'card_type', 'price',
            'period_start', 'period_end', 'count', 'admin_id'])
            ->where('status', '>=', 0)
            ->orderBy('created_at', 'DESC')
            ->paginate($request['page_size'] ?? 10);
        return $this->response->collection($card_orders, new CardOrderTransformer());
    }

    /**
     * 新增兑换码订单
     */
    public function createCardOrder(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'phone' => 'required',
            'email' => 'required',
            'address' => 'required',
            'card_type' => 'required',
            'price' => 'required',
            'count' => 'required',
            'start_time' => 'required'
        ]);
        if ($request['count'] > 1000) {
            return $this->response->json(['code' => 2001, 'msg' => '数量过多']);
        }
        // 目前只支持年卡导出
//        if ($request['card_type'] != MemberCardRecord::CARD_TYPE['annual']) {
//            return $this->response->json(['code' => 2002, 'msg' => '目前兑换码只支持年卡']);
//        }
        DB::beginTransaction();
        try {
            $period_start = Carbon::createFromTimestamp(strtotime($request['start_time']));
            $period_end = Carbon::createFromTimestamp(strtotime($request['start_time']))->addYear();
            $admin = auth()->guard('admin')->user()->id ?? auth()->guard('m_admin')->user()->id;
            $cardOrder = CardCodeOrder::create([
                'user_id' => 0,
                'name' => $request['name'],
                'phone' => $request['phone'],
                'email' => $request['email'],
                'address' => $request['address'],
                'price' => $request['price'],
                'count' => $request['count'],
                'card_type' => $request['card_type'],
                'period_start' => $period_start,
                'period_end' => $period_end,
                'status' => 1,
                'admin_id' => $admin,
            ]);
            $this->createCode($request['count'], $cardOrder);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('CREATE_CARD_ORDER_ERROR', [$e]);
            return $this->response->json(['code' => 2002, 'msg' => '系统错误']);
        }
        return $this->response->json(['code' => 0, 'msg' => '']);
    }

    /**
     * 修改兑换码订单
     */
    public function updateCardOrder(Request $request)
    {
        $this->validate($request, [
            'id' => 'required',
            'name' => 'required',
            'phone' => 'required',
            'email' => 'required',
            'address' => 'required',
        ]);
        $status = CardCodeOrder::where('id', $request['id'])->update(
            array_filter($request->only('name', 'phone', 'email', 'address'))
        );
        if ($status) {
            return $this->response->json(['code' => 0, 'msg' => '修改成功']);
        }
        return $this->response->json(['code' => 2001, 'msg' => '修改失败']);
    }

    /**
     * 删除兑换码订单
     */
    public function deleteCardOrder($id)
    {
        if ($id) {
            $admin = auth()->guard('admin')->user()->id ?? auth()->guard('m_admin')->user()->id;
            // 只有超级管理员才能够删除兑换码
            if ($admin == 1) {
                // 兑换码被人使用过后将不再支持删除
                $cards = MemberCardRecord::where('card_code_order_id', $id)
                    ->where('user_id', '!=', 0)
                    ->whereNull('deleted_at')
                    ->exists();
                if ($cards) {
                    return $this->response->json(['code' => 2001, 'msg' => '兑换码已投入使用，禁止删除']);
                }
                DB::beginTransaction();
                try {
                    $order = CardCodeOrder::where('id', $id)->first();
                    if ($order) {
                        // 删除兑换码订单
                        CardCodeOrder::where('id', $id)->delete();
                        // 删除兑换码数据
                        MemberCardRecord::where('card_code_order_id', $order->id)->delete();
                        DB::commit();
                        return $this->response->json(['code' => 0, 'msg' => '']);
                    }
                } catch (\Exception $e) {
                    \Log::error('DELETE_CARD_ORDER_ERROR   ', [$e]);
                    DB::rollBack();
                }
                return $this->response->json(['code' => 2002, 'msg' => '操作失败，请重试']);
            }
            return $this->response->json(['code' => 2003, 'msg' => '权限不足']);
        }
    }

    /**
     * 兑换码列表 兑换码脱敏
     */
    public function cdkey(Request $request)
    {
        $page = $request['page'] ?? 1;
        $page_size = $request['page_size'] ?? 10;
        $count = DB::table('member_card_records as m')
            ->select('m.id')
            ->leftJoin('users as u', 'm.user_id', 'u.id')
            ->leftJoin('card_code_orders as c', 'm.card_code_order_id', 'c.id')
            ->whereNull('m.deleted_at')
            ->where('card_code_order_id', '!=', 0)
            ->where('m.status', MemberCardRecord::STATUS['is_pay'])
            ->when($request['keyword'], function ($query, $value) {
                $query->where(function ($query) use ($value) {
                    $query->where('m.id', $value)
                        ->orWhere('m.no', $value)
                        ->orWhere('m.code', $value)
                        ->orWhere('u.name', $value)
                        ->orWhere('u.phone', $value);
                });
            })->when($request['use_start_time'], function ($query, $value) {
                $query->where('paid_at', '>=', "$value");
            })->when($request['use_end_time'], function ($query, $value) {
                $query->where('paid_at', '<=', "$value  23:59:59");
            })->when($request['start_time'], function ($query, $value) {
                $query->where('c.period_end', '>=', "$value");
            })->when($request['end_time'], function ($query, $value) {
                $query->where('c.period_end', '<=', "$value");
            })->when($request['status'], function ($query, $value) {
                if ($value == 1) {  // 未兑换
                    $query->whereNull('paid_at');
                } else {    // 已兑换
                    $query->whereNotNull('paid_at');
                }
            })->when($request['order_id'], function ($query, $value) {
                $query->where('card_code_order_id', $value);
            })->count();
        $codes = DB::table('member_card_records as m')
            ->select([
                'u.name as u_name', 'u.phone', 'm.paid_at', 'm.no', 'm.code', 'c.name',
                'c.id', 'm.user_id', 'c.period_start', 'c.period_end', 'c.card_type'
            ])
            ->leftJoin('users as u', 'm.user_id', 'u.id')
            ->leftJoin('card_code_orders as c', 'm.card_code_order_id', 'c.id')
            ->whereNull('m.deleted_at')
            ->where('card_code_order_id', '!=', 0)
            ->where('m.status', MemberCardRecord::STATUS['is_pay'])
            ->when($request['keyword'], function ($query, $value) {
                $query->where(function ($query) use ($value) {
                    $query->where('m.id', $value)
                        ->orWhere('m.no', $value)
                        ->orWhere('m.code', $value)
                        ->orWhere('u.name', $value)
                        ->orWhere('u.phone', $value);
                });
            })->when($request['use_start_time'], function ($query, $value) {
                $query->where('paid_at', '>=', "$value");
            })->when($request['use_end_time'], function ($query, $value) {
                $query->where('paid_at', '<=', "$value  23:59:59");
            })->when($request['start_time'], function ($query, $value) {
                $query->where('c.period_end', '>=', "$value");
            })->when($request['end_time'], function ($query, $value) {
                $query->where('c.period_end', '<=', "$value");
            })->when($request['status'], function ($query, $value) {
                if ($value == 1) {  // 未兑换
                    $query->whereNull('paid_at');
                } else {    // 已兑换
                    $query->whereNotNull('paid_at');
                }
            })->when($request['order_id'], function ($query, $value) {
                $query->where('card_code_order_id', $value);
            })
            ->orderBy('paid_at', 'desc')
            ->orderBy('m.no', 'asc')
            ->offset(($page - 1) * $page_size)->limit($page_size)->get();
        return $this->response->collection($codes, new CDKEYTransformer(), [
            'pagination' => [
                "total" => $count,
                "count" => $page_size,
                "per_page" => $page_size,
                "current_page" => (int)$page,
                "total_pages" => ceil($count / $page_size),
            ]]);
    }

    /**
     * 返回兑换码下载key
     */
    public function checkExport(Request $request)
    {
        // 检查权限
        $id = $request['order_id'] ?? false;
        if ($id) {
            $admin = auth()->guard('admin')->user()->id ?? auth()->guard('m_admin')->user()->id;
            $card_order = CardCodeOrder::where('id', $id)
                ->where('admin_id', $admin)
                ->where('status', '!=', 0)->exists();
            if ($card_order) {
                $token = encrypt([
                    'admin' => $admin,
                    'order_id' => $id,
                    'expire' => Carbon::now()->addSecond(20),
                ]);
                return $this->response->json(['code' => 0, 'token' => $token]);
            }
        }
        return $this->response->json(['code' => 2001, 'msg' => '操作失败']);
    }

    /**
     * 导出兑换码
     */
    public function export(Request $request)
    {
        try {
            $filter = decrypt(\request('token'));
        } catch (\Exception $e) {
            abort('400', '请求参数错误');
        }
        if (Carbon::now()->timestamp - $filter['expire']->timestamp > 20) {
            abort('403', 'token过期');
        }
        $admin = $filter['admin'] ?? 0;
        $order_id = $filter['order_id'] ?? 0;
        $card_order = CardCodeOrder::where('id', $order_id)->where('admin_id', $admin)->where('status', '!=', 0)->first();
        if ($card_order) {
            $codes = MemberCardRecord::select(['no', 'code', 'card_type', 'paid_at'])
                ->where('card_code_order_id', $order_id)->get();
            $cellData = [
                ['会员卡类型', '兑换卡号', '兑换码', '生效日', '失效日'],
            ];
            foreach ($codes as $code) {
                $cellData[] = [
                    MemberCardRecord::CARD_NAME[$code->card_type],
                    $code->no,
                    $code->code,
                    $card_order->period_start,
                    $card_order->period_end
                ];
            }
            $file_name = $card_order->id . '-' . $card_order->name . '-' .
                MemberCardRecord::CARD_NAME[$card_order->card_type] . '-' . $card_order->count . '张';
            $card_order->status = CardCodeOrder::STATUS['used'];
            $card_order->save();
            app(Excel::class)->create($file_name, function ($excel) use ($cellData) {
                $excel->sheet('score', function ($sheet) use ($cellData) {
                    $sheet->rows($cellData);
                });
            })->export('xlsx');
        } else {
            abort('400', '操作失败');
        }
    }

    /**
     * 生成兑换码数据
     */
    public function createCode($amount, CardCodeOrder $order)
    {
        // todo 兑换码加密
        $no = MemberCardRecord::where('status', 1)->whereNotNull('no')
            ->orderBy('id', 'DESC')->value('no');
        if (!$no) {
            $no = 1;
        } else {
            $no++;
        }
        for ($i = 1; $i <= $amount; $i++) {
            $couponArr[] = [
                'user_id' => 0,
                'card_no' => 0,       // 付费会员卡不存在卡号 默认为0
                'card_type' => $order->card_type,
                'price' => MemberCardRecord::getPrice($order->card_type),
                'period_start' => $order->period_start,
                'period_end' => $order->period_end,
                'status' => 1,
                'paid_type' => 4,   //  兑换码为4
                'card_code_order_id' => $order->id,
                'no' => sprintf("%08d", $no),
                'code' => 'HY' . IQuery::createCode(7),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];
            $no++;
        }
        $member_card_records = new MemberCardRecord();
        $member_card_records->maxInsert($couponArr);
    }
}
