<?php

namespace App\Listeners;

use App\Models\Active;
use Carbon\Carbon;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Log;

class ActiveEventSubscriber
{
    public function subscribe($events)
    {
        $events->listen(
            'eloquent.created: App\Models\Active',
            'App\Listeners\ActiveEventSubscriber@onActiveCreated'
        );

        $events->listen(
            'eloquent.updated: App\Models\Active',
            'App\Listeners\ActiveEventSubscriber@onActiveUpdated'
        );
    }

    public function onActiveCreated($active)
    {
        if (Carbon::parse($active->period_start)->format('Y-m-d') == Carbon::today()->format('Y-m-d') || $active->period_type == Active::PERIOD['relative']) {
            $active->where('status', Active::STATUS['tostart'])->update(['status' => Active::STATUS['starting']]);
        }
    }

    public function onActiveUpdated($active)
    {
        if (Carbon::parse($active->period_start)->format('Y-m-d') == Carbon::today()->format('Y-m-d') || $active->period_type == Active::PERIOD['relative']) {
            Active::where('id', $active->id)->whereIn('status', [Active::STATUS['tostart'], Active::STATUS['pause']])->update(['status' => Active::STATUS['starting']]);
        }
        if (Carbon::parse($active->period_start)->format('Y-m-d') > Carbon::today()->format('Y-m-d')) {
            Active::where('id', $active->id)->where('status', Active::STATUS['pause'])->update(['status' => Active::STATUS['tostart']]);
        }
    }
}
