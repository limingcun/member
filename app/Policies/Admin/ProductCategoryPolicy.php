<?php

namespace App\Policies\Admin;

class ProductCategoryPolicy extends BasePolicy
{
    public function show()
    {
        return auth()->user()->can('product_category_show');
    }

    public function update()
    {
        return auth()->user()->can('product_category_edit');
    }
}
