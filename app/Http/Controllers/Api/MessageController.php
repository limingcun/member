<?php

namespace App\Http\Controllers\Api;

use App\Models\Message;
use App\Transformers\Api\MessageTransformer;
use App\Http\Controllers\ApiController;
use App\Models\User;
use Illuminate\Http\Request;

class MessageController extends ApiController
{
    /**
     * 小程序消息推送列表
     */
    public function index() {
        $user = $this->user();
        $message = $user->message()->orderBy('created_at', 'desc')->paginate(10);
        $user->members()->update(['message_tab' => 0]);  //还原用户消息标识
        return $this->response->collection($message, new MessageTransformer());
    }
    
    /**
     * 分页列表数据
     * @param Request $request
     */
    public function pageIndex(Request $request) {
        $user = $this->user();
        $pageSize = $request->page_size ?? 1;
        $paginate = 10 * $pageSize;
        $message = $user->message()->orderBy('created_at', 'desc')->paginate($paginate);
        return $this->response->collection($message, new MessageTransformer());
    }

        /**
     * 消息删除
     * @param type $id
     * @return type
     */
    public function destroy($id) {
        $message = Message::findOrFail($id);
        $message->delete();
        return success_return();
    }
    
    /**
     * 红点消失
     * @param type $id
     */
    public function redTabFade($id) {
        $message = Message::findOrFail($id);
        $message->update(['tab' => Message::TAB['scan']]);
        return success_return();
    }
    
    /**
     * 消息设为全部已读
     */
    public function readAll() {
        $user = $this->user();
        $user->message()->update(['tab' => Message::TAB['scan']]);
        return success_return();
    }
    
    /**
     * 未读消息数量
     */
    public function udReadNumber() {
        $user = $this->user();
        $number = $user->message()->where('tab', Message::TAB['new'])->count();
        return response()->json(['number' => $number]);
    }
}
