<?php

namespace App\Policies\Admin;

use Illuminate\Auth\Access\HandlesAuthorization;

class BasePolicy
{
    use HandlesAuthorization;

    public function before($user, $ability)
    {
        if (auth()->user()->isSuperAdmin()) {
            return true;
        }
    }
}
