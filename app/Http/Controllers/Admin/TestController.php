<?php

/**
 * Created by netbeanIDE.
 * User: limingcun
 * Date: 2018/4/20
 * Time: 上午10:31
 * desc: 测试控制器
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use App\Models\User;
use Illuminate\Support\Facades\Request;
use DB;
use Carbon\Carbon;
use IQuery;
use App\Services\WxMessage;
use App\Models\Member;
use App\Models\CouponLibrary;
use App\Models\Message;
use App\Facades\Test;

class TestController extends ApiController
{
    
    /**
     * 账户是否开户,是否设置密码查询
     */
    public function test() {
//        DB::table('users')->delete(109);
//        return DB::table('users')->where([
//            ['id', '<', 150],
//            ['sex', 'male']
//        ])->get();
//        return User::find(109)->value('name');
    }
    
    public function aaa($a, $b, $c) {
        return $a + $b + $c;
    }
}