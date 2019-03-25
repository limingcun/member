<?php
/**
 * Created by PhpStorm.
 * User: surui
 * Date: 2017/11/16
 * Time: 上午1:00
 */

namespace App\Http\Controllers;


class ErrorCode
{
    protected static $codeMsg = null;

    public static $OK                   = 0;
    public static $IllegalCardNo        = 1001;
    public static $IllegalCardCode      = 1002;
    public static $IllegalCardStatus    = 1003;

    public static $AccountNotExist      = 2001;
    public static $AccountAuthError     = 2002;
    public static $AccountCardEmpty     = 2003;

    public static $ShopItemNotExist     = 3001;
    public static $PointsNotEnough      = 3002;
    public static $AddressNotExist      = 3003;
    public static $ShopOrderNotExist    = 3004;

    public static $MemberNotExist       = 4001;
    public static $ERROE_MSG_CONFIG = 'errormsg';
    // public static function errorMsg($errorCode)
    // {
    //     if(ErrorCode::$codeMsg == null){
    //         ErrorCode::$codeMsg = [
    //             ErrorCode::$OK                  => '成功！',
    //             ErrorCode::$IllegalCardCode     => '卡密码错误',
    //             ErrorCode::$IllegalCardNo       => '卡号不存在',
    //             ErrorCode::$IllegalCardStatus   => '卡状态异常',
    //             ErrorCode::$AccountNotExist     => '账号为空',
    //             ErrorCode::$AccountAuthError    => '授权账户不一致',
    //             ErrorCode::$AccountCardEmpty    => '没有可用卡片信息',
    //             ErrorCode::$ShopItemNotExist    => '商品已下架',
    //             ErrorCode::$AddressNotExist     => '没有填写地址信息',
    //             ErrorCode::$ShopOrderNotExist   => '商品订单不存在',
    //             ErrorCode::$MemberNotExist      => '会员信息不存在',
    //         ];
    //     }
    //     return ErrorCode::$codeMsg[$errorCode];
    // }
    public static function errorMsg($errorCode)
    {
        $key = substr($errorCode, 0 ,2);
        return config(ErrorCode::$ERROE_MSG_CONFIG . '.' .$key . '.' . $errorCode);
    }
}
