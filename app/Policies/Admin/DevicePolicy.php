<?php

namespace App\Policies\Admin;

class DevicePolicy extends BasePolicy
{
    public function show()
    {
        return auth()->user()->can('device_show');
    }
}
