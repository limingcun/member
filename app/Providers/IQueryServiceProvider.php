<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Utils\IQuery;

class IQueryServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('iquery',function(){
            return new IQuery;
        });
    }
}
