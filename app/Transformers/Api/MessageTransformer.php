<?php

namespace App\Transformers\Api;

use Carbon\Carbon;
use App\Models\Message;
use League\Fractal\TransformerAbstract;

class MessageTransformer extends TransformerAbstract
{
    /**
     * 小程序消息推送列表
     * @return array
     */
    public function transform(Message $message)
    {
        return [
            'id' => $message->id,
            'user_id' => $message->user_id,
            'title' => $message->title,
            'content' => $message->content,
            'type' => $message->type,
            'tab' => $message->tab,
            'path_go' => $message->path_go,
            'created_at' => $this->dateTransform((string) $message->created_at) 
        ];
    }
    
    /**
     * 时间转换
     * $date时间
     */
    public function dateTransform($date) {
        return Carbon::parse($date)->format('Y.m.d H:i');
    }
}
