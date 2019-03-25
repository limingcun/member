<?php

namespace App\Policies\Admin;

class ProductMaterialPolicy extends BasePolicy
{
    public function show()
    {
        return auth()->user()->can('product_material_show');
    }

    public function update()
    {
        return auth()->user()->can('product_material_edit');
    }
}
