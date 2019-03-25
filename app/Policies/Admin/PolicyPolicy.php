<?php

namespace App\Policies\Admin;

class PolicyPolicy extends BasePolicy
{
    public function show()
    {
        return auth()->user()->can('policy_show');
    }

    public function update()
    {
        return auth()->user()->can('policy_edit');
    }

    public function execution()
    {
        return auth()->user()->can('policy_execution');
    }
}
