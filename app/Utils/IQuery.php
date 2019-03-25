<?php
/**
 * desc: 工具类
 * autoer: limingcun
 * date: 2018/3/16
 */
namespace App\Utils;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Log;
use Redis;
use Carbon\Carbon;

class IQuery{
    /*
     * redis初始链接
     */
    public static function initRedis($conn = '') {
        return Redis::connection($conn);
    }
    /*
     * 获取redis中get值
     * $key键值
     */
    public function redisGet($key) {
        $redis = self::initRedis();
        $value = $redis->get($key);
        $value_serl = @unserialize($value);
        if(is_object($value_serl)||is_array($value_serl)){
            return $value_serl;
        }
        return $value;
    }

    /*
     * 设置redis键值
     * $key键,$val值
     * 默认不过期
     */
    public function redisSet($key, $val, $time = 0) {
        $redis = self::initRedis();
        if(is_object($val)||is_array($val)){
            $val = serialize($val);
        }
        if (!$time) {
            $redis->set($key, $val);
        } else {
            $redis->setex($key, $time, $val);
        }
    }

    /*
     * 更新redis时间
     * $key键,$time更新时间
     */
    public function redisExpire($key, $time) {
        $redis = self::initRedis();
        $redis->expire($key, $time);
    }

    /*
     * 删除redis键
     * $key键
     */
    public function redisDelete($key) {
        $redis = self::initRedis();
        if ($redis->exists($key)) {
            $redis->del($key);
        }
    }

    /*
     * 判断redis键值是否存在
     * $key键
     */
    public function redisExists($key) {
        $redis = self::initRedis();
        if ($redis->exists($key)) {
            return true;
        }
        return false;
    }

    /**
     * $redis->setnx('key', 'value');
     */
    public function redisSetnx($key, $val) {
        $redis = self::initRedis();
        return $redis->setnx($key, $val);
    }

    /**
     * list表自增值
     */
    public function redisPush($key, $value) {
        $redis = self::initRedis();
        $redis->rPush($key, $value);
    }

    /**
     * 排序
     */
    public function redisRange($key, $start, $end) {
        $redis = self::initRedis();
        return $redis->lRange($key, $start, $end);
    }

    /**
     * redis自增
     * @param type $key
     * @param type $step
     */
    public function redisIncr($key, $step = 1) {
        $redis = self::initRedis();
        $redis->incrBy($key, $step);
    }

    /**
     * redis自减
     * @param type $key
     * @param type $step
     */
    public function redisDecr($key, $step = 1) {
        $redis = self::initRedis();
        $redis->decrBy($key, $step);
    }

    /*
     * md5验证
     */
    public function md5Check($request) {
        $time = $request->header('x-timestamp');
        $sign = $request->header('x-sign');
        $scr_md5 = md5(json_encode($request->all()).$time.env('CUSTOMER_SECRET'));
        Log::info('receid',[json_encode($request->all())]);
        if ($scr_md5 != $sign) {
            return false;
        }
        return true;
    }

    // 获取excel文件
    public function getExcel($request) {
        $file = $request->file('excel_file');
        $image_path = config('app.excel_path');   //文件根目录
        if ($request->hasFile('excel_file')) {
            $path = 'excel/';
            $Extension = $file->getClientOriginalExtension();  //文件后缀
            $filename = time().'.'. $Extension;  //文件加密
            $file->move($path, $filename);
            $filePath = $path.$filename;
        }
        if($Extension!='xls'&&$Extension!='xlsx') {
            @unlink($filePath);
            return false;
        }
        return $filePath;
    }

    //获取excel表
    public function loadExcel($excel,$filename,$export) {
        if (ob_get_contents()) ob_end_clean();//清除缓冲区,避免乱码
        $excel->create($filename, function($excel1) use($export) {
            $excel1->sheet('Excel sheet', function($sheet) use($export) {
                $sheet->fromArray($export);
                $sheet->setOrientation('landscape');
            });
        })->export('xls');
    }

    //字符串往前面补0
    public function strPad($id) {
        return str_pad($id, 9, 0, STR_PAD_LEFT);
    }

    //兑换码生成
    public function createCode($length) {
        if ($length <= 0) return false;
        $timestamp = $this->miniTime();
        $string = $this->get_char($timestamp << 2, true);
        $char = '123456789ABCDEFGHIJKLMNPQRSTUVWXYZ';
        for($i = $length; $i > 0; $i--) {
            $string .= $char[mt_rand(0, strlen($char) - 1)];
        }
        if (strlen($string) == 15) {
            $string = str_pad($string, 16, 'Z', STR_PAD_LEFT);
        }
        return $string;
    }

    // 36位进制数转换
    public function get_char($num, $flag = false) {
        $charArr = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L',
            'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        $char = '';
        do {
            $key = ($num - 1) % 36;
            $char= $charArr[$key] . $char;
            $num = floor(($num - $key) / 36);
        } while ($num > 0);
        //替换字符串中的0和O
        if ($flag) {
            $char = str_replace(array('0', 'O'), mt_rand(1, 9), $char);
        }
        return $char;
    }

    // 当前时间毫秒级时间戳
    public function miniTime() {
        $time = explode (' ', microtime ());
        $time = $time [1] . ($time[0] * 1000);
        $time2 = explode ('.', $time);
        $time = $time2 [0];
        return $time;
    }

    /*
     * 生成订单规则
     */
    public function createNo() {
        return 'SC'.date('YmdHis').str_pad($this->get_char(gettimeofday()['usec']), 4, 0).mt_rand(1001, 9999);
    }

    /*
     * 发送数据请求方法
     */
    public function send_post($url, $post_data, $method='POST') {
        $postdata = http_build_query($post_data);
        $options = array(
            'http' => array(
                'method' => $method, //or GET
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $postdata,
                'timeout' => 15 * 60 // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        return $result;
    }

    /*
     * 获取返回数据
     */
    public function api_notice_increment($url, $data){
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检测
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Expect:')); //解决数据包大不能提交
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        $tmpInfo = curl_exec($curl); // 执行操作
        if (curl_errno($curl)) {
            return 'Errno'.curl_error($curl);
        }
        curl_close($curl); // 关键CURL会话
        return $tmpInfo; // 返回数据
    }

    /**
     * 替换掉emoji表情
     * @param $text
     * @param string $replaceTo
     * @return mixed|string
     */
    public static function filterEmoji($text, $replaceTo = '')
    {
        //执行一个正则表达式搜索并且使用一个回调进行替换
        $text = preg_replace_callback(
            '/./u',
            function (array $match) {
                return strlen($match[0]) >= 4 ? '' : $match[0];
            },
            $text);
        return $text;
    }

    /*
     * 会员随机编号
     * $id会员id
     * $n位数
     */
    public function memberNoCreate($id, $n) {
        $s = '';
        for($i = 0; $i < $n; $i++) {
            $s .= rand(0, 9);
        }
        return $s.$id;
    }

    /**
     * 判断有效时段
     * @param type $rand_time
     * @return boolean
     */
    public function rangeTime($rand_time){
        $date = date('Y-m-d', time());
        $rand_time = explode('-', $rand_time);
        $start_time = $rand_time[0];
        $end_time = $rand_time[1];
        //开始时间
        $start = strtotime($date.$start_time);
        $end = strtotime($date.$end_time);
        //当前时间
        $now = time();
        if($now >= $start && $now <= $end){
            return true;
        }else{
            return false;
        }
    }
}
