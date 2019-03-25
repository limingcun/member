<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'Overtrue\LaravelUploader\Events\FileUploaded' => [
            'App\Listeners\FileUploadedListener',
        ],
        'eloquent.updated: App\Models\Member' => [
            'App\Listeners\MemberExpLevel',
        ]
    ];

    protected $subscribe = [
        'App\Listeners\UserEventSubscriber',
        'App\Listeners\ActiveEventSubscriber'
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
