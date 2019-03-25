<?php

namespace App\Listeners;

use App\Models\Member;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserEventSubscriber
{
    public function subscribe($events)
    {
        $events->listen(
            'eloquent.created: App\Models\User',
            'App\Listeners\UserEventSubscriber@onUserCreated'
        );

        $events->listen(
            'eloquent.updated: App\Models\User',
            'App\Listeners\UserEventSubscriber@onUserUpdated'
        );
    }

    public function onUserCreated($user)
    {
        Member::create(['user_id' => $user->id, 'type' => 'wxlite', 'name' => $user->name, 'sex' => $user->sex]);
    }

    public function onUserUpdated($user)
    {
        $member = $user->members()->where('type', 'wxlite')->first();
        $member->update([
            'name' => $user->name,
            'sex' => $user->sex,
            'avatar_id' => $user->avatar_id,
            'birthday' => $user->birthday,
            'phone' => $user->phone
        ]);
    }
}
