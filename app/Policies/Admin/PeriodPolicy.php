<?php

namespace App\Policies\Admin;

class PeriodPolicy extends BasePolicy
{
    public function show()
    {
        return auth()->user()->can('period_show');
    }

    public function setting()
    {
        return auth()->user()->can('period_setting');
    }
}
