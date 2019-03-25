<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        \App\Models\User::class => \App\Policies\Admin\UserPolicy::class,
        \App\Models\Admin::class => \App\Policies\Admin\AdminPolicy::class,
        \App\Models\Ad::class => \App\Policies\Admin\AdPolicy::class,
        \App\Models\Device::class => \App\Policies\Admin\DevicePolicy::class,
        \App\Models\Order::class => \App\Policies\Admin\OrderPolicy::class,
        \App\Models\Period::class => \App\Policies\Admin\PeriodPolicy::class,
        \App\Models\Policy::class => \App\Policies\Admin\PolicyPolicy::class,
        \App\Models\PolicyCategory::class => \App\Policies\Admin\PolicyCategoryPolicy::class,
        \App\Models\Product::class => \App\Policies\Admin\ProductPolicy::class,
        \App\Models\Category::class => \App\Policies\Admin\ProductCategoryPolicy::class,
        \App\Models\Attribute::class => \App\Policies\Admin\ProductAttributePolicy::class,
        \App\Models\Material::class => \App\Policies\Admin\ProductMaterialPolicy::class,
        \App\Models\RefundOrder::class => \App\Policies\Admin\RefundOrderPolicy::class,
        \Spatie\Permission\Models\Role::class => \App\Policies\Admin\RolePolicy::class,
        \App\Models\Shop::class => \App\Policies\Admin\ShopPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
