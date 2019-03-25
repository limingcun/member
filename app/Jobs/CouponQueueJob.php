<?php

namespace App\Jobs;

use App\Models\Member;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use DB;
use Log;
use App\Models\User;
use App\Services\WxMessage;

class CouponQueueJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $users;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($users)
    {
        $this->users = $users;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $users = $this->users;
        $wxs = new WxMessage();
        $page = 'pages/my/my';
        $title = '喜茶券到账通知';
        $content = '收到新的喜茶券啦，一起来看看吧';
        foreach($users as $user_id) {
            $user = User::findOrFail($user_id);
            $openId = $user->wxlite_open_id;
            $wechatFormId = $user->wechatFormId()->where('is_used', 0)->first();
            $formId = $wechatFormId->formid;
            $wxs->messageTpl($openId, $formId, $page, $title, $content);
        }
    }
}
