<?php

namespace App\Providers;

use App\Policies\Active\CupActivePolicy;
use App\Policies\Active\CupFreeActivePolicy;
use App\Policies\Coupon\CostCouponPolicy;
use App\Policies\CouponLibrary\CashCouponPolicy;
use Illuminate\Support\ServiceProvider;

class CouponProvider extends ServiceProvider
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
        $this->app->singleton('CupActivePolicy',function(){
            return new CupActivePolicy();
        });
        $this->app->singleton('CostCouponPolicy',function(){
            return new CostCouponPolicy();
        });
        $this->app->singleton('CashCouponPolicy',function(){
            return new CashCouponPolicy();
        });
        $this->app->singleton('CupFreeActivePolicy',function(){
            return new CupFreeActivePolicy();
        });
    }
}
