<?php
namespace App\Http\Repositories\Api;

use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\User;
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
     * @param User $user
     */
    public function index(Request $request, User $user) {
        $page = $request->page_size ?? config('app.page');
        $comment = $user->comment()->with(['images' => function($query) {
            $query->select('path');
        }]);
        return $comment->orderBy('id', 'desc')->paginate($page);
    }

    /**
     * 新增意见反馈
     * @param Request $request
     */
    public function store(Request $request, User $user) {
        try {
            DB::beginTransaction();
            $comment = Comment::create([
                'user_id' => $user->id,
                'issue_type' => $request->issue_type,
                'comment' => $request->comment
            ]);
            if ($request->image_ids) {
                $comment->images()->sync($request->image_ids);
            }
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    // 更新反馈状态
    public function readComment($comments)
    {
        $comment_ids = [];
        foreach($comments as $comment) {
            $comment_ids[] = $comment->id;
        }
        if (count($comment_ids) > 0) {
            Comment::whereIn('id', $comment_ids)->where('status', 1)->update(['status' => 2]);     // 状态修改
        }
    }
}
