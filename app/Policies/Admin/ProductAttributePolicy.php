<?php

namespace App\Policies\Admin;

class ProductAttributePolicy extends BasePolicy
{
    public function show()
    {
        return auth()->user()->can('product_attribute_show');
    }

    public function update()
    {
        return auth()->user()->can('product_attribute_edit');
    }
}
