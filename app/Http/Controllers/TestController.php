<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\MAdmin;
use App\Models\MallProduct;
use App\Models\MallSku;
use App\Models\MPermission;
use App\Models\MRole;
use App\Models\Product;
use App\Models\User;
use App\Services\Jwt;
use function EasyWeChat\Kernel\Support\get_server_ip;
use Eureka\EurekaClient;
use Illuminate\Database\Eloquent\Builder;

class TestController extends Controller
{
    public function __construct()
    {
        \DB::enableQueryLog();
    }

    public function getSql()
    {
        dd(\DB::getQueryLog());
    }

    public function test()
    {
//        $appUrl=   config('app.url');
//        $client = new EurekaClient([
//            'eurekaDefaultUrl' => 'http://20.10.28.252:30020/eureka/',
//            'hostName' => 'service.member',
//            'appName' => 'service-member',
//            'ip' => '127.0.0.1',
//            'port' => ['80', true],
//            'homePageUrl' => $appUrl,
//            'statusPageUrl' => $appUrl.'/info',
//            'healthCheckUrl' => $appUrl.'/health'
//        ]);
//        $client->deRegister();
//        $client->start();
    }

    public function admin()
    {
        $admin = User::find(3);
        $token = \Auth::guard('admins')->fromUser($admin);
        dd($token);


        $admin = Admin::first();
        $token = \Auth::guard('api')->fromUser($admin);
        dd($token);
    }
}
