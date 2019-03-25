<?php

namespace App\Policies\Admin;

class AdminPolicy extends BasePolicy
{
    public function show()
    {
        return auth()->user()->can('admin_show');
    }

    public function update()
    {
        return auth()->user()->can('admin_edit');
    }
}
