<?php

namespace App\Policies\Admin;

class ProductPolicy extends BasePolicy
{
    public function show()
    {
        return auth()->user()->can('product_show');
    }

    public function update()
    {
        return auth()->user()->can('product_edit');
    }
}
