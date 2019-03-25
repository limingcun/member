<?php
/**
 * Created by PhpStorm.
 * User: heyujia
 * Date: 2018/8/8
 * Time: 上午11:20
 */

namespace App\Http\Controllers\Admin;

use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use Carbon\Carbon;
use App\Services\QiNiuServer;

class CommonController
{
    
    public function __construct() {
    }
    
    /*
     * 七牛云直传
     */
    public function postFile() {
        $filePath = request()->file('uploadImg');
//        $array = getimagesize($filePath->getRealPath());
//        return $array;
        
        $qiniu = new QiNiuServer();
        $result = $qiniu->server($filePath);
        if ($result['errno'] == 1) {
            return $result;
        } else {
            return ['errno' => $result['errno'], 'data' => [$result['url'].'/'.$result['path']]];
        }
    }
}