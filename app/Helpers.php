<?php

if (!function_exists('cdn_url')) {
    function cdn_url($path)
    {
        if (!$path) {
            return null;
        }

        if (App::environment('local')) {
            return url($path);
        }

        return \Storage::disk('qiniu')->getUrl($path);
    }
}

if (!function_exists('order_no')) {
    /**
     * 生成单号
     *
     * @return string
     */
    function order_no()
    {
        // 时间+6位微秒数+3位随机数
        return \Carbon\Carbon::now()->format('YmdHis').
            str_pad(gettimeofday()['usec'], 6, 0).
            mt_rand(100, 999);
    }
}

if (!function_exists('number_random')) {
    /**
     * 生成随机 n 位数
     *
     * @return string
     */
    function number_random($length = 4)
    {
        $numbers = array_map('ord', str_split(uniqid(), 1));

        shuffle($numbers);

        if ($length > 10) {
            return date('ymd') . substr(implode($numbers), 0, $length - 6);
        }

        return substr(implode($numbers), 0, $length);
    }
}
if (!function_exists('create_no')) {
    function create_no($pre){
        $no=$pre;
        $no.=date('Ymd');
        $no.=sprintf("%03d",rand(1,999));
        return $no;
    }
}
if (!function_exists('pr')) {
    function pr($content){
        echo '<pre>';
        print_r($content);
        echo '</pre>';
        die;
    }
}

if (!function_exists('system_variable')) {
    function system_variable($key, $value = 'value')
    {
        return \DB::table('system_variables')
            ->where('key', $key)
            ->whereNull('deleted_at')
            ->value($value);
    }
}
