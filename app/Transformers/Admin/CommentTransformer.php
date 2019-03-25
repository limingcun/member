<?php

namespace App\Transformers\Admin;

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
            'user_id' => $comment->user_id,
            'user_name' => $comment->user->name ?? '',
            'user_phone' => $comment->user->phone ?? '',
            'level_go' => $comment->member->level->name ?? '',
            'level_star' => $comment->member->starLevel->name ?? '-',
            'submit_time' => (string) $comment->created_at,
            'issue_type' => Comment::ISSUETYPE[$comment->issue_type],
            'status' => Comment::STATUS[$comment->status],
            'comment' => $comment->comment,
            'reply_text' => $comment->reply_text,
            'reply_at' => $comment->reply_at,
            'admin_name' => $comment->admin->name ?? '',
            'images' => $comment->images,
            'image_url' => env('QINIU_URL')
        ];
    }
}
