<?php
namespace App\Http\Repositories\Admin;

use Illuminate\Http\Request;
use App\Models\Comment;
use App\Http\Repositories\BaseRepository;
use Carbon\Carbon;
use DB;

class CommentRepository extends BaseRepository
{
    protected $table;

    public function __construct() {
        $this->table = new Comment();
    }

    /**
     * 意见反馈列表
     * @param Request $request
     */
    public function index(Request $request) {
        $page = $request->page_size ?? config('app.page');
        $comment = $this->table->with(['user', 'admin', 'member.level', 'images' => function($query) {
            $query->select('path');
        }])->when($request->keyword, function($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereHas('user', function ($query) use ($value) {
                    $query->where('id', $value)->orWhere('phone', $value);
                });
            });
        })->when($request->level_id, function($query, $value) {
            $query->whereHas('member', function($query) use ($value) {
                $query->where('level_id', $value);
            });
        })->when($request->star_level_id, function($query, $value) {
            $query->whereHas('member', function($query) use ($value) {
                $query->where('star_level_id', $value);
            });
        });
        if ($request->issue_type != '') {
            $comment = $comment->where('issue_type', $request->issue_type);
        }
        return $comment->orderBy('id', 'desc')->paginate($page);
    }

    /**
     * 单个意见反馈回复
     */
    public function commentReply(Request $request) {
        $comment_ids = $request->comment_ids;
        $reply_text = $request->reply_text;
        $res = Comment::whereIn('id', $comment_ids)->update([
            'reply_text' => $reply_text,
            'reply_at' => Carbon::now(),
            'admin_id' => auth()->guard('m_admin')->user()->id ?? auth()->guard('admin')->user()->id,
            'status' => 1
        ]);
        if ($res) {
            $this->addHint($comment_ids);
        }
        return $res;
    }

    /**
     * 给被回复用户新增提示
     */
    public function addHint($comment_ids)
    {
        $comment = Comment::whereIn('id', $comment_ids)->select('user_id')->groupBy('user_id')->get();
        foreach ($comment->pluck('user_id') as $id) {
            \IQuery::redisSet('hint_'.$id, Carbon::now()->toDateTimeString());
        }
    }
}
