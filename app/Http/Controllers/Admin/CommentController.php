<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Http\Repositories\Admin\CommentRepository;

class CommentController extends ApiController
{
    /**
     * 反馈列表
     * @param Request $request
     * @return type
     */
    public function index(Request $request) {
        $rps = new CommentRepository();
        $comment = $rps->index($request);
        return $this->response->collection($comment);
    }
    
    /**
     * 意见反馈回复
     * @param Request $request
     * @return type
     */
    public function commentReply(Request $request) {
        $rps = new CommentRepository();
        $res = $rps->commentReply($request);
        if ($res) {
            return success_return();
        }
        return error_return(2001);
    }
}
