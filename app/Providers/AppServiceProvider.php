<?php

namespace App\Providers;

use Validator;
use App\Services\Validation;
use League\Fractal\Manager;
use App\Support\Transform;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use League\Fractal\Serializer\DataArraySerializer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        Validator::resolver(function($translator, $data, $rules, $messages)
        {
            return new Validation($translator, $data, $rules, $messages);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(Transform::class, function () {
            $fractal = new Manager;

            if (request()->has('include')) {
                $fractal->parseIncludes(request()->query('include'));
            }

            $fractal->setSerializer(new DataArraySerializer);

            return new Transform($fractal);
        });
    }
}
