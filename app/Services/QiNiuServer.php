<?php

namespace App\Services;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use Carbon\Carbon;

class QiNiuServer
{
    private $accessKey;
    private $secretKey;
    private $bucket;
    private $qiniuUrl;
    
    public function __construct() {
        $this->accessKey = env('QINIU_ACCESS_KEY');
        $this->secretKey = env('QINIU_SECRET_KEY');
        $this->bucket = env('QINIU_BUCKET');
        $this->qiniuUrl = env('QINIU_URL');
    }
    
    public function server($filePath) {
        $expires = 3600;
        $policy = null;
        $key = Carbon::now()->timestamp.rand(10000, 99999).'jpg';
        $auth = new Auth($this->accessKey, $this->secretKey);
        $upToken = $auth->uploadToken($this->bucket, null, $expires, $policy, true);
        $uploadMgr = new UploadManager();
        list($ret, $err) = $uploadMgr->putFile($upToken, $key, $filePath);
        if ($err !== null) {
            return ['errno' => 1, 'data' => [$err]];
        } else {
            return ['errno' => 0, 'path' => $key, 'url' => $this->qiniuUrl];
        }
    }
}