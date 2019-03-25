<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Models\Comment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests\Api\Comment\CommentRequest;
use App\Http\Repositories\Api\CommentRepository;
use App\Transformers\Api\CommentTransformer;
use App\Services\QiNiuServer;
use App\Models\Image;

class CommentController extends ApiController
{
    /**
     * 个人反馈列表
     * @param Request $request
     * @return type
     */
    public function index(Request $request) {
        $user = $this->user();
        $rps = new CommentRepository();
        $comment = $rps->index($request, $user);
        $rps->readComment($comment);
        return $this->response->collection($comment, new CommentTransformer());
    }

    /**
     * 新增意见反馈
     * @param Request $request
     * @return type
     */
    public function store(CommentRequest $request) {
        $user = $this->user();
        $count = $user->comment()->whereDate('created_at', Carbon::today())->count();
        if ($count >= 3) {
            return error_return(2002);
        }
        $rps = new CommentRepository();
        $res = $rps->store($request, $user);
        if ($res) {
            return success_return();
        }
        return error_return(2001);
    }
    
    /**
     * 意见反馈图片上传
     */
    public function imageUpload() {
        $user = $this->user();
        $filePath = request()->file('uploadImg');
        $qiniu = new QiNiuServer();
        $result = $qiniu->server($filePath);
        if ($result['errno'] == 0) {
            $array = getimagesize($filePath->getRealPath());
            $size = filesize($filePath->getRealPath());
            $image = Image::create([
                'user_id' => $user->id,
                'path' => $result['path'],
                'origin_name' => $filePath->getClientOriginalName(),
                'width' => $array[0],
                'height' => $array[1],
                'size' => $size,
                'content_type' => $array['mime']
            ]);
            $url = $result['url'];
            return compact('image', 'url');
        } else {
            return response()->json(['code' => 3001, 'msg' => '上传图片失败']);
        }
    }
}
