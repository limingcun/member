<?php

namespace App\Http\Controllers\Admin;

use App\Models\MallExpress;
use App\Models\MallOrderExpress;
use App\Models\Member;
use App\Models\MemberScore;
use App\Services\JuheExp;
use App\Services\KDNiao;
use App\Utils\IQuery;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Models\MallOrder;
use App\Transformers\Admin\MallOrderItemTransformer;
use Maatwebsite\Excel\Excel;

class MallOrderController extends ApiController
{
    const REDIS_EXCEL = 'laravel:excel:';

    /*
     * 积分商城订单列表
     * page_size页码数量
     * keyword搜索关键词(模糊搜索)
     * status商品状态(待上架、上架和下架)
     * start兑换开始时间
     * end兑换结束时间
     * mall_product_id从查看详情页商品传过来的id
     * sort排序数组['字段','asc']
     */
    public function index(Request $request)
    {
        $page = $request->page_size != '' ? $request->page_size : config('app.page');
        $mall_orders = MallOrder::with('user')->when($request->keyword, function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('no', 'like', '%' . $value . '%')
                    ->orWhereHas('item', function ($query) use ($value) {
                        $query->where('name', 'like', '%' . $value . '%');
                    })
                    ->orWhereHas('user', function ($query) use ($value) {
                        $query->where('phone', 'like', '%' . $value . '%')
                            ->orWhere('name', 'like', '%' . $value . '%')
                            ->orWhere('id', $value);
                    });
            });
        })->when($request->status, function ($query, $value) {
            $query->where('status', $value);
        })->when($request->start, function ($query, $value) {
            $query->whereDate('exchange_time', '>=', $value);
        })->when($request->end, function ($query, $value) {
            $query->whereDate('exchange_time', '<=', $value);
        })->when($request->mall_product_id, function ($query, $value) {
            $query->whereHas('item', function ($query) use ($value) {
                $query->where('mall_product_id', $value);
            });
        })->when($request->mall_type, function ($query, $value) {
            $query->where('mall_type', $value);
        });
        if ($request->sort) {
            $sort = json_decode($request->sort, true);
            $mall_orders = $mall_orders->orderBy($sort[0], $sort[1]);
        } else {
            $mall_orders = $mall_orders->orderBy('created_at', 'desc');
        }
        $mall_orders->with([
            'express',
            'item',
        ]);
        return $this->response->collection($mall_orders->paginate($page));
    }

    /*
     * 查看订单详情
     * $id订单id
     */
    public function show($id)
    {
        $mall_order = MallOrder::with('item', 'item.product', 'item.source')->findOrFail($id);
        return $this->response->item($mall_order, new MallOrderItemTransformer());
    }

    /*
     * 统计订单数量和使用积分
     */
    public function dataCount()
    {
        $statusAll = [MallOrder::STATUS['success'], MallOrder::STATUS['wait_dispatch'], MallOrder::STATUS['dispatching'], MallOrder::STATUS['finish'], MallOrder::STATUS['refund']];
        $statusArr = array_slice($statusAll, 0, 4);
        $order_count = MallOrder::whereIn('status', $statusAll)->count();
        $score_sum = MallOrder::whereIn('status', $statusArr)->sum('score');
        return compact('order_count', 'score_sum');
    }

    /**
     * 退单
     */
    public function refund()
    {
        $order = MallOrder::findOrFail(\request('id'));
        if (MallOrder::STATUS['refund'] == $order->status) {
            abort(403, '订单已退');
        }
        $user = $order->user;
        $usable_score = $user->members[0]->usable_score;
        $used_score = $user->members[0]->used_score;
        MemberScore::create([
            'user_id' => $user->id,
            'source_id' => $order->id,
            'source_type' => MallOrder::class,
            'score_change' => $order->score,
            'method' => MemberScore::METHOD['mall_refund'],
            'description' => '退单'
        ]);
        //个人可用积分增加,已使用积分添加
        $user->members()->update([
            'usable_score' => bcadd($usable_score, $order->score),
            'used_score' => bcsub($used_score, $order->score)
        ]);
        //更新订单状态
        $order->update([
            'status' => MallOrder::STATUS['refund'],
            'refund_reason' => \request('reason'),
        ]);
        return success_return();
    }

    /**
     * 获取快递公司
     * @return MallExpress[]|\Illuminate\Database\Eloquent\Collection
     */
    public function express()
    {
        $express = MallExpress::all();
        return $express;
    }

    /**
     * 修改快递信息
     * @param $id
     * @return array
     */
    public function editExpress($id)
    {
        $fields = \request()->all();
        $orderExpress = MallOrderExpress::where('mall_order_id', $id)->first();
        if ($fields['no']) {
            $orderExpress->order()->update([
                'status' => MallOrder::STATUS['dispatching']
            ]);
        }
        $orderExpress->update($fields);
        return success_return();
    }

    /**
     * 上传快递信息表格
     * @param Request $request
     * @param Excel $excel
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function storeExcel(Request $request, Excel $excel)
    {
        $filePath = \IQuery::getExcel($request);
        if (!$filePath) {
            return response()->json(['msg' => '上传文件类型错误，请上传xls或xlsx类型文件', 'code' => 2001]);
        }
        try {
            $reader = $excel->load($filePath);
        } catch (\Exception $e) {
            @unlink($filePath);
            return response()->json(['msg' => '请填写完整快递单号数据信息', 'code' => 2003]);
        }
        $data = $reader->getSheet(0)->toArray();
        @unlink($filePath);
        if ($data[0][0] !== '订单号' || $data[0][7] !== '快递单号') {
            return response()->json(['msg' => '导入excel模板错误,请参照批量导出模板', 'code' => 2002]);
        }
        $JuheExp = new JuheExp();
        $correct = [];
        $wrong = [];
        if (MallOrder::whereIn('no', array_column(array_slice($data, 1), '0'))
            ->where('status', '!=', MallOrder::STATUS['wait_dispatch'])
            ->first()) {
            return response()->json(['msg' => '请上传待发货订单号和对应快递单号', 'code' => 2005]);
        }
        for ($i = 1; $i < count($data); $i++) {
            $order = $data[$i];
            if ($order[0]) {
                $no = strval($order[7]);
                $no = preg_replace('# #', '', $no);
                if (!preg_match('/^[0-9a-zA-Z]+$/', $no)) {
                    return response()->json(['msg' => '快递单号不能含有中文或特殊字符，请检查完善后再导入', 'code' => 2004]);
                }
                $orderNo = $order[0];
                if (is_array($JuheExp->query($request->shipper_code, $no))) {
                    $correct[] = [
                        'shipper_code' => $request->shipper_code,
                        'no' => $no,
                        'orderNo' => $orderNo,
                    ];
                } else {
                    $wrong[] = $no;
                }
            }
        }
        $admin = auth()->guard('admin')->user()->id ?? auth()->guard('m_admin')->user()->id;
        $flag = self::REDIS_EXCEL . time() . $admin;
        \IQuery::redisSet($flag, $correct, 3600);  //存储临时redis
        $correctNum = count($correct);
        return response()->json(['code' => 0, 'correct_num' => $correctNum, 'wrong' => $wrong, 'flag' => $flag]);
    }

    /**
     *
     */
    public function updateExcel()
    {
        $flag = request('flag');
        $corrects = \IQuery::redisGet($flag);
        if ($corrects && count($corrects)) {
            $express = MallExpress::where('shipper_code', $corrects[0]['shipper_code'])->first();
            foreach ($corrects as $correct) {
                $order = MallOrder::where('no', $correct['orderNo'])->first();
                if ($order->status == MallOrder::STATUS['wait_dispatch']) {
                    $order->express()
                        ->update([
                            'shipper' => $express->shipper,
                            'shipper_code' => $correct['shipper_code'],
                            'no' => $correct['no'],
                        ]);
                    $order->update(['status' => MallOrder::STATUS['dispatching']]);
                }
            }
        }
        return success_return();
    }

    /**
     * 查询物流信息
     *
     */
    public function expressTraces()
    {
        $JuheExp = new JuheExp();
        $delivery = $JuheExp->query(\request('shipper_code'), \request('no'));
        if (!is_array($delivery)) {
            return error_return(403, $delivery);
        }
        if (is_array($delivery)) {
            $traces = $delivery['list'];
        } else {
            $traces = [];
        }
        return success_return($traces);
    }

    /**
     * 生成下载表格的签名
     * @return array
     */
    public function excelSign()
    {
        $token = encrypt([
            'begin' => \request('begin'),
            'end' => \request('end'),
            'status' => \request('status'),
            'expire' => Carbon::now()->addSecond(20),
        ]);
        return success_return($token);
    }

    /**
     * 下载表格
     * @param Excel $excel
     */
    public function excel(Excel $excel)
    {
        $filter = decrypt(\request('token'));
        if (Carbon::now()->timestamp - $filter['expire']->timestamp > 20) {
            abort('403', 'token过期');
        }
        $orders = MallOrder::with([
            'item',
            'item.source',
            'express',
        ])
            ->where('mall_type', MallOrder::MALLTYPE['real'])
            ->when($filter['status'], function ($query, $value) {
                $query->where('status', $value);
            })
            ->where('mall_type', MallOrder::MALLTYPE['real'])
            ->whereDate('exchange_time', '>=', $filter['begin'])
            ->whereDate('exchange_time', '<=', $filter['end'])
            ->get();
        $data = [];
        foreach ($orders as $order) {
            $express = $order->express;
            $specs = $order->item->source->specifications;
            $specStr = '';
            foreach ($specs as $spec) {
                $spec_name = IQuery::filterEmoji($spec['name']);
                $spec_value = IQuery::filterEmoji($spec['value']);
                $specStr .= "{$spec_name}:{$spec_value},";
            }
            switch ($order->status) {
                case MallOrder::STATUS['wait_dispatch']:
                    $statusTxt = '待发货';
                    break;
                case MallOrder::STATUS['dispatching']:
                    $statusTxt = '已发货';
                    break;
                case MallOrder::STATUS['finish']:
                    $statusTxt = '已完成';
                    break;
                case MallOrder::STATUS['refund']:
                    $statusTxt = '已退单';
                    break;
                default:
                    $statusTxt = '未知';
                    break;
            }
            $data[] = [
                '订单号' => $order->no,
                '会员号' => $order->user_id,
                '商品名' => $order->item->name ?? '',
                '规格' => substr($specStr, 0, strlen($specStr) - 1),
                '收货人姓名' => $express->name ?? '',
                '收货人电话' => $express->phone ?? '',
                '收货人地址' => $express->address ?? '',
                '快递单号' => $express->no ?? '',
                '状态' => $statusTxt,
            ];
        }
        \IQuery::loadExcel($excel, '积分商城订单表', $data);
    }
}
