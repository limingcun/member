<?php

namespace App\Policies\Admin;

class ShopPolicy extends BasePolicy
{
    public function show()
    {
        return auth()->user()->can('shop_show');
    }

    public function update()
    {
        return auth()->user()->can('shop_edit');
    }

    public function efficacy()
    {
        return auth()->user()->can('shop_efficacy_show');
    }

    public function updateEfficacy()
    {
        return auth()->user()->can('shop_efficacy_edit');
    }
}
