<?php

namespace App\Http\Controllers\Api;

use Dingo\Api\Routing\Helpers;
use App\Http\Controllers\Controller as BaseController;

abstract class Controller extends BaseController
{
    use Helpers;
}
