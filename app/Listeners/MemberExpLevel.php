<?php

namespace App\Listeners;

use App\Models\GiftRecord;
use App\Models\Level;
use App\Models\Member;
use App\Models\StarLevel;
use Carbon\Carbon;
use IQuery;
use Log;
use App\Jobs\GoQueueJob;
use App\Jobs\StarQueueJob;

class MemberExpLevel
{
    protected $redis_path = 'laravel:level_trigger:';
    
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object $event
     * @return void
     * 会员等级监听器api
     */
    public function handle(Member $member)
    {
        if ($member->isDirty('exp')) {
            goLevel($member);
//            $level = Level::where('exp_min', '<=', $member->exp)->where('exp_max', '>=', $member->exp)->select(['id', 'name'])->first();
//            if ($member->level_id != $level->id) {
////                GoQueueJob::dispatch($member);
//                $redis_go_path = $this->redis_path.'go';
//                IQuery::redisPush($redis_go_path, $member->id);
//            }
        }
        if ($member->isDirty('star_exp')) {
            starLevel($member);
//            $star_level = StarLevel::where('exp_min', '<=', $member->star_exp)->where('exp_max', '>=', $member->star_exp)->select(['id', 'name'])->first();
//            if ($member->star_level_id != $star_level->id) {
////                StarQueueJob::dispatch($member);
//                $redis_star_path = $this->redis_path.'star';
//                IQuery::redisPush($redis_star_path, $member->id);
//            }
        }
        return;
    }
}
