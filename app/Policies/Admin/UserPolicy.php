<?php

namespace App\Policies\Admin;

use App\Models\User;

class UserPolicy extends BasePolicy
{
    public function show()
    {
        return auth()->user()->can('user_show');
    }
}
