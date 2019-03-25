<?php

namespace App\Policies\Admin;

class RefundOrderPolicy extends BasePolicy
{
    public function show()
    {
        return auth()->user()->can('order_refund_show');
    }

    public function refund()
    {
        return auth()->user()->can('order_refund_refund');
    }
}
