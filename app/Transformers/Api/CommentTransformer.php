<?php

namespace App\Transformers\Api;

use Carbon\Carbon;
use App\Models\Comment;
use League\Fractal\TransformerAbstract;

class CommentTransformer extends TransformerAbstract
{
    /**
     * 
     * 意见反馈数据获取和转化
     * @return array
     */
    public function transform(Comment $comment)
    {
        return [
            'id' => $comment->id,
            'submit_time' => (string) $comment->created_at,
            'issue_type' => Comment::ISSUETYPE[$comment->issue_type],
            'status' => Comment::STATUS[$comment->status],
            'comment' => $comment->comment,
            'reply_text' => $comment->reply_text,
            'reply_at' => $comment->reply_at,
            'images' => $comment->images,
            'image_url' => env('QINIU_URL')
        ];
    }
}
