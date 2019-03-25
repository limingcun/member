<?php
/**
 * Created by PhpStorm.
 * User: 15210
 * Date: 2019/3/18
 * Time: 10:57
 */

namespace App\Facades;

use Illuminate\Support\Facades\Facade;
class Test extends Facade
{
    protected static function getFacadeAccessor()
    {
        return '\App\Utils\Test';
    }
}