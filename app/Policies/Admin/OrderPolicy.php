<?php

namespace App\Policies\Admin;

class OrderPolicy extends BasePolicy
{
    public function show()
    {
        return auth()->user()->can('order_show');
    }

    public function takeaway()
    {
        return auth()->user()->can('order_takeaway_show');
    }
}
