<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

$api = app('Dingo\Api\Routing\Router');
$api->version('v1', [
    'namespace' => 'App\Http\Controllers\Api',
    'middleware' => ['serializer:array', 'api.response'],
], function ($api) {
    $api->post('authorizations', 'AuthController@store');
    $api->post('app_bind', 'AuthController@appBind');   //手机绑定
    $api->post('send_msg', 'AuthController@sendMsg');   //发送短信
    $api->post('app_login', 'AuthController@appLogin'); //APP微信登录
    $api->post('save_avatar', 'UserController@saveAvatar');  //保存app头像
    $api->post('login_register', 'AppController@isLoginOrRigester');  //判断APP是否是登录还是注册
    $api->post('phone_register', 'AppController@appPhoneRegister');  //APP手机号注册
    $api->post('phone_login', 'AppController@appPhoneLogin');  //APP手机号登录
    $api->post('phone_msg', 'AppController@sendPhoneMsg');   //手机号登录注册发送验证码
    $api->post('login_again', 'AppController@loginAgain'); //过期重新登录
    $api->post('reset_pwd', 'AppController@resetPwd');  //手机号重置密码
    $api->post('game/coupon/index', 'GameCouponController@index'); //游戏优惠券
    $api->group(['middleware' => 'api:api'], function ($api) {
        //APP用户数据信息
        $api->post('app_update_user', 'UserController@appUpdateUser');
        $api->post('app_upload', 'UserController@appUploadImage');
        $api->post('images', 'ImageController@store');
        $api->post('app/store_pwd', 'AppController@storePwd'); //设置密码
        $api->post('app/update_pwd', 'AppController@updatePwd'); //修改密码
        $api->post('app/bind_wxchat', 'AppController@appBindWxchat'); //绑定微信账号
        $api->get('app/applogout', 'AppController@appLogout'); //退出用户账号
        //用户数据信息
        $api->get('user', 'UserController@userData');
        $api->get('bind_phone', 'UserController@bindPhone');
        $api->get('is_bind', 'UserController@isBindPhone');
        $api->put('user', 'UserController@update');
        $api->get('user/image_pic', 'UserController@imagePic');
        $api->post('user/phone', 'UserController@getPhone');
        $api->get('user/birthday', 'UserController@setBirthday'); // 查看是否设置生日
        $api->put('user/birthday', 'UserController@setBirthday'); // 设置生日
        $api->get('hint', 'UserController@hint'); // 引导提示（小红点）
        // 星球会员
        $api->get('user/card/code', 'MemberCardController@cardCode'); // 二维码
        $api->get('user/card/list', 'MemberCardController@MemberCardList'); // 付费会员卡列表
        $api->post('user/card', 'MemberCardController@buyMemberCard'); // 购买付费会员卡
        $api->get('user/member_club', 'UserController@memberClub'); // 获取会员俱乐部信息
        $api->get('user/bonus_center', 'GiftController@show'); // 奖励中心
        $api->put('user/bonus_center/{id}', 'GiftController@getGift'); // 奖励中心领取礼包奖励
        $api->put('user/star_coupon/', 'CouponController@exchangeCoupon'); // 奖励中心星球会员钻石以上等级领取满单赠饮券
        $api->get('user/member_card_record', 'MemberCardController@MemberCardRecordList'); // 购卡记录
        $api->get('user/card/status/{id}', 'MemberCardController@getStatus'); // 轮询获得会员卡回调状态
        //获取积分和优惠券数量
        $api->get('get_point_coupon_num', 'IndexController@getPointAndCouponNum');
        //获取积分数据信息
        $api->get('get_point', 'PointController@getPoint');
        $api->get('get_all_point', 'PointController@getAllPoint');
        $api->get('get_rule', 'PointController@getRule');
        //优惠券数据信息
        $api->get('coupon/usable_coupon', 'CouponController@usableCoupon'); //可用优惠券
        $api->get('coupon/used_period', 'CouponController@usedAndPeriodCoupon'); //已使用或已过期优惠券
        $api->post('order_coupon', 'CouponController@orderCoupon');
        $api->post('coupon/takeout_fee', 'CouponController@takeoutFee'); //外卖配送费时间变化
        $api->get('check_pop', 'CouponController@checkPop');  //兑换弹窗
        $api->post('code_change', 'CouponController@codeExchange');  //线下优惠券兑换码兑换
        $api->get('qrcode_show', 'CouponController@qrCodeShow'); //显示二维码兑换弹窗
        $api->post('qrcode_change', 'CouponController@qrCodeExchange');  //线下二维码兑换
        //积分商城商品列表
        $api->post('mall_product/exchange/{id}', 'MallProductController@exchange');
        $api->post('mall_product/exchangeLock', 'MallProductController@exchangeLock');
        $api->post('mall_product/exchangeLockCancel', 'MallProductController@exchangeLockCancel');
        $api->get('mall_product/show_msg/{id}', 'MallProductController@showMsg');
        $api->get('mall_product/exch_record', 'MallProductController@exchRecord');
        $api->get('mall_product/member_area', 'MallProductController@memberArea');  //积分商城专区
        $api->resource('mall_product', 'MallProductController');
        //商品订单信息
        $api->get('mall_order/order_list', 'MallOrderController@orderList');
        $api->post('mall_order/order_detail', 'MallOrderController@orderDetail');
        //意见反馈
        $api->post('comment/comment_image', 'CommentController@imageUpload');
        $api->resource('comment', 'CommentController');
        //vka会员等级升级
        $api->post('vka/upgrade', 'VkaController@upgrade');
        $api->get('vka/record', 'VkaController@vkaRecord');

        // 9块9邀请活动
        $api->get('invite-activity/', 'InviteActivityController@index');  // 邀请活动首页接口
        $api->get('invite-activity/is_join', 'InviteActivityController@isJoin');  // 用户是否参与过活动
        $api->get('invite-activity/status', 'InviteActivityController@inviteActivityStatus');  // 活动状态
        $api->get('invite-activity/list', 'InviteActivityController@invitationList');  // 邀请列表
        $api->put('invite-activity/coupon', 'InviteActivityController@getCoupon');  // 领券接口
        $api->get('invite-activity/address', 'InviteActivityController@address');  // 查看填写的收货地址
        $api->post('invite-activity/address', 'InviteActivityController@address');  // 新增收获地址

        // 会员兑换码
        $api->post('cdkey', 'StarCDKEYController@CDKEY');  // 使用会员兑换码
        $api->get('cdkey/status', 'StarCDKEYController@exchangeStatus');  // 使用会员兑换码
        $api->post('cdkey_orcoupon', 'StarCDKEYController@cdKeyOrCoupon'); //兑换会员卡或优惠券
        
        //消息推送
        $api->get('message/tab_fade/{id}', 'MessageController@redTabFade');
        $api->get('message/read_all', 'MessageController@readAll');
        $api->get('message/unread_number', 'MessageController@udReadNumber');
        $api->get('message/page_index', 'MessageController@pageIndex');
        $api->resource('message', 'MessageController');
    });
    //md5加密传值
    $api->group([
        'middleware' => 'sign'
    ], function ($api) {
        $api->get('dec_score', 'PointController@decScore');  //退单减去积分
        $api->get('common_api', 'IndexController@commonApi'); //点单调用公共接口(消费获取积分，优惠券核销，新会员)
        $api->get('coupon_discount', 'CouponController@couponDiscount');  //计算优惠额度
        $api->post('shop_active', 'ActiveController@shopActive');
        $api->post('join_active', 'ActiveController@joinActive');
        $api->post('cancel_active', 'ActiveController@cancelActive');
        $api->post('active_info', 'ActiveController@getActiveInfo'); //获取活动数据信息
        $api->post('order_discount', 'OrderController@orderDiscount'); //获取活动数据信息
        $api->post('sync_buy', 'IndexController@syncBuyApi');  //同步时下单
        $api->post('sync_refund', 'PointController@syncRefundApi'); //同步时退款
    });

    //md5加密传值
    $api->group([
        'middleware' => 'storage'
    ], function ($api) {
        $api->post('storage/phone_msg', 'AuthController@receivePhoneMsg');  //接收储值短信通知
        $api->post('storage/session_key', 'AuthController@updateSessionKey');  //接收储值session_key
        $api->post('cash_storage/account', 'CashStorageController@account');  //喜茶钱包开户
        $api->post('cash_storage/recharge_consume', 'CashStorageController@rechargeConsume');  //喜茶钱包流水账单
        $api->post('checkCode', 'AuthController@checkCode'); //检测支付码
    });
});
