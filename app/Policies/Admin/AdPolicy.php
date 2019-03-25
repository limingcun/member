<?php

namespace App\Policies\Admin;

class AdPolicy extends BasePolicy
{
    public function show()
    {
        return auth()->user()->can('ad_show');
    }

    public function update()
    {
        return auth()->user()->can('ad_edit');
    }
}
