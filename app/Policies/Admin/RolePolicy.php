<?php

namespace App\Policies\Admin;

class RolePolicy extends BasePolicy
{
    public function update()
    {
        return auth()->user()->can('role_edit');
    }
}
