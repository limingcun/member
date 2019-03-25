<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use SoftDeletes;
    protected $table = 'comments';
    protected $dates = ['deleted_at'];  //开启deleted_at

    const ISSUETYPE = [
        '功能异常', '体验问题', '新功能建议', '其他'
    ];

    const STATUS = [
        '未回复', '已回复', '回复已读'
    ];

    protected $fillable = ['id', 'user_id', 'issue_type', 'comment', 'reply_at', 'reply_text', 'admin_id', 'status'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function admin()
    {
        return $this->belongsTo(istoreUpmsUser::class);
    }

    public function member() {
        return $this->belongsTo(Member::class, 'user_id', 'user_id');
    }

    public function images()
    {
        return $this->belongsToMany(Image::class, 'comment_images', 'comment_id', 'image_id');
    }
}
