<?php

namespace App\Services;


use App\Models\MallOrder;
use App\Models\MallProduct;
use Carbon\Carbon;
use EasyWeChat;
use Log;
use App\Models\WechatFormId;
use Carbon\Carbon;

class WxMessage
{

    /**
     * @var EasyWeChat\MiniProgram\Application
     */
    private $wechat;

    public function __construct()
    {
        $this->wechat = EasyWeChat::miniProgram();
    }

    /**
     * 优惠券领取消息
     * @throws EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     */
    public function couponMessage()
    {
        $openId = 'otVsY4wWPygiYBvU_Lg4RtaD6NZo';
        $prepay_id = 'wx210926233431836967d058041708027053';
        $result = $this->wechat->template_message->send([
            'touser' => $openId,
            'template_id' => 'kiJDF4N4EE9JU86om5Em9zFcEsymHPq8VhkJN50G8s4',
            'page' => 'pages/code/code',
            'form_id' => $prepay_id,
            'data' => [
                'keyword1' => '123',//卡券名称
                'keyword2' => '324',//有效日期
                'keyword3' => '435',//优惠内容
                'keyword4' => '3454',//仅限「勾选的一款商品」等「已选商品数量」款商品可用
                'keyword5' => '343',//点击进入小程序内查看
            ],
        ]);
        Log::info('WECHAT MESSAGE', $result);
    }

    /**
     * 商品兑换消息
     */
    public function exchangeMessage(MallOrder $mallOrder)
    {
        $openId = $mallOrder->user->wxlite_open_id;
        $formId = $mallOrder->form_id;
        if ($mallOrder->item->product->mall_type == MallProduct::MALLTYPE['invent']) {
            if (!$mallOrder->item->source->period_type) {
                $date = $mallOrder->item->source->period_end;
            } else {
                $date = Carbon::now()->addDays($mallOrder->item->source->period_day)->format('Y-m-d');
            }
            $result = $this->wechat->template_message->send([
                'touser' => $openId,
                'template_id' => config('message.exchange_message'),
                'page' => 'pages/my/my',
                'form_id' => $formId,
                'data' => [
                    'keyword1' => $mallOrder->item->name,
                    'keyword2' => $date ? substr($date, 0, 10) : '无',
                    'keyword3' => $mallOrder->score . '积分',
                    'keyword4' => $mallOrder->member->usable_score . '积分'
                ],
            ]);
        } else {
            $result = $this->wechat->template_message->send([
                'touser' => $openId,
                'template_id' => config('message.exchange_real'),
                'page' => 'pages/my/my',
                'form_id' => $formId,
                'data' => [
                    'keyword1' => $mallOrder->item->name,
                    'keyword2' => $mallOrder->express->address,
                    'keyword3' => $mallOrder->express->name.' '.$mallOrder->express->phone,
                    'keyword4' => $mallOrder->score . '积分',
                    'keyword5' => $mallOrder->member->usable_score . '积分'
                ],
            ]);
        }
        Log::info('WECHAT MESSAGE', $result);
    }
    
    /**
     * 消息模板推送
     * $openId
     */
    public function messageTpl($openId, $formId, $page, $title, $content) {
        $miniForm = WechatFormId::where('formid', $formId)
            ->where('is_used', 0)
            ->first();
        if ($miniForm) {
            $result = $this->wechat->template_message->send([
                'touser' => $openId,
                'template_id' => config('message.exchange_real'),
                'page' => $page,
                'form_id' => $formId,
                'data' => [
                    'keyword1' => $title,
                    'keyword2' => $content
                ],
            ]);
            $miniForm->update([
                'is_used' => 1,
                'used_at' => Carbon::now()->toDateTimeString()
            ]);
        }
    }
}