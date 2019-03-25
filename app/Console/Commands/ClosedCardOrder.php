<?php

namespace App\Console\Commands;

use App\Http\Controllers\notification\PaymentController;
use App\Http\Controllers\WechatController;
use App\Models\MemberCardRecord;
use Carbon\Carbon;
use Illuminate\Console\Command;
use DB;
use IQuery;
use Log;

class ClosedCardOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'close:card_order';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '关闭未付款的星球会员购买记录';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        MemberCardRecord::where('card_no', 0)->where('status', MemberCardRecord::STATUS['wait_pay'])
            ->where('created_at', '<=', Carbon::now()->subMinutes(5))
            ->chunk(200, function ($orders) {
                $app = new WechatController();
                foreach ($orders as $order) {
                    $close_result = $app->close($order->order_no);
                    Log::info('CLOSE_WECHAT_ORDER_RETURN', [$close_result]);
                    if (('FAIL' == $close_result['result_code'])  && ('ORDERPAID' == $close_result['err_code'])) {
                        // 关闭订单失败 查询订单是否已经支付
                        $result = $app->queryByOutTradeNumber($order->order_no);
                        Log::info('QUERY_WECHAT_ORDER', [$result]);
                        if ('SUCCESS' == $result['trade_state']) {
                            // 检测用户是否有订单正在被处理
                            if (IQuery::redisSetnx('card_notify_' . $order->user_id, 1)) {
                                IQuery::redisExpire('card_notify_' . $order->user_id, 30);

                                // 检查用户从下这个单到此时是否有再次购卡 有再次购卡的进行退款处理
                                $card_order = MemberCardRecord::where('user_id', $order->user_id)
                                    ->where('card_no', 0)
                                    ->where('paid_type', 1)
                                    ->where('status', MemberCardRecord::STATUS['is_pay'])
                                    ->where('created_at', '>', $order->created_at)
                                    ->where('created_at', '<', Carbon::now())
                                    ->where('id', '!=', $order->id)
                                    ->exists();
                                if ($card_order) {
                                    // 退款处理
                                    try {
                                        $fee = $order->price * 100;
                                        $refund = $app->refund($order->order_no, 'REFUND' . $order->order_no, $fee, $fee);
                                        Log::info('REFUND_MSG = ', [$refund]);
                                        if ('SUCCESS' == $refund['result_code']) {
                                            Log::info('REFUND_SUCCESS', [$order]);
                                            $order->update(['status' => MemberCardRecord::STATUS['refund']]);
                                        }
                                    } catch (\Exception $e) {
                                        Log::error('REFUND_FAIL', [$e]);
                                    } finally {
                                        IQuery::redisDelete('card_notify_' . $order->user_id);
                                    }
                                } else {
                                    DB::beginTransaction();
                                    try {
                                        $pay = new PaymentController();
                                        $pay->notifySuccess($result['time_end'], $order);
                                        DB::commit();
                                        Log::info('SUCCESS', [$order]);
                                    } catch (\Exception $e) {
                                        DB::rollBack();
                                        Log::error('CLOSE_ORDER_ERROR', [$e]);
                                    } finally {
                                        IQuery::redisDelete('card_notify_' . $order->user_id);
                                    }
                                }
                            } else {
                                // 订单正在处理中
                                Log::info('Processing... ', [$order]);
                            }
                        }
                    } else {
                        // 关闭订单
                        $order->update(['status' => MemberCardRecord::STATUS['cancel']]);
                    }
                }
            });
    }
}
