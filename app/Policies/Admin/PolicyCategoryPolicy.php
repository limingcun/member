<?php

namespace App\Policies\Admin;

class PolicyCategoryPolicy extends BasePolicy
{
    public function show()
    {
        return auth()->user()->can('policy_category_show');
    }

    public function update()
    {
        return auth()->user()->can('policy_category_edit');
    }
}
