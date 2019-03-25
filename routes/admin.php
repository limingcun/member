<?php
/*
|--------------------------------------------------------------------------
| Admin API Routes
|--------------------------------------------------------------------------
*/
Route::group(['namespace' => 'Admin\Auth'], function () {
    Route::post('user_login', 'AuthController@issueToken');
    Route::get('user_logout', 'AuthController@revokeToken');

    Route::post('login', 'AuthController@login');
    Route::post('logout', 'AuthController@logout');

});
Route::get('test', 'Admin\TestController@test');

Route::get('storage_out_excel/{sign}/{user_id}/{start_time?}/{end_time?}', 'Admin\CashFlowBillController@outExcelBalance');
Route::get('storage_out_rebill/{sign}/{rebill_start}/{rebill_end}/{status?}/{pay_way?}', 'Admin\CashFlowBillController@outRebillCount');
Route::get('storage_out_rebuy/{sign}/{rebuy_start}/{rebuy_end}/{status?}/{pay_way?}', 'Admin\CashFlowBillController@outRebuyCount');
Route::get('load_excel/{sign}', 'Admin\CouponGrandController@loadExcel');
Route::get('out_code/{id}/{sign}', 'Admin\CouponGrandController@outCode');
Route::get('mall_order/excel', 'Admin\MallOrderController@excel');
Route::get('star/config/excel/template', 'Admin\StarConfigController@template'); // 下载批量上传模板
Route::get('star/config/export', 'Admin\StarConfigController@exportWrongExcel'); // 导出错误数据excel
Route::get('star/cdkey/export', 'Admin\StarCDKEYController@export'); // 导出会员兑换码
Route::group(['namespace' => 'Admin', 'middleware' => ['jwt']], function () {
    Route::post('postFile', 'CommonController@postFile');
    //管理员密码修改
    Route::post('pass_update', 'AdminController@updatePassword');
    //会员信息
    Route::get('member/member_num', 'MemberController@memberNum'); //时间段内新增会员
    Route::post('member/level', 'MemberController@levelUpdate');
    Route::get('member/unlock/{id}', 'MemberController@unLockScore');
    Route::post('member/increase', 'MemberController@memberIncrease'); //会员数量增长点
    Route::get('member/star_list/{id?}', 'MemberController@starList');  //星球会员历史记录
    Route::get('member/go_star_right/{user_id?}', 'MemberController@goAndStarRight'); //会员权益和福利
    Route::get('member/query_member', 'MemberController@queryMember');
    Route::get('member/score_list/{id}', 'MemberController@scoreList');
    Route::get('member/member_exp_head/{user_id}', 'MemberController@memberGoStarExp'); //会员经验值头部
    Route::get('member/member_exp/{user_id}', 'MemberController@memberExp'); //会员经验值明细
    Route::get('member/usable_coupon_list', 'MemberController@usableCouponList');
    Route::get('member/all_coupon_list', 'MemberController@allCouponList');
    Route::get('member/coupon_detail/{coupon_id?}', 'MemberController@couponDetail'); //个人优惠券详情页
    Route::get('member/order_detail', 'MemberController@orderDetail'); //个人订单详情页
    Route::get('member/star_coupon/{user_id}', 'MemberController@starCoupon'); // 星球会员满单赠饮券
    Route::resource('member', 'MemberController');
    //会员等级数据信息
    Route::get('level_rule', 'LevelController@getLevelRule');
    Route::post('level_set', 'LevelController@setLevelRule');
    Route::get('level_ratia', 'LevelController@levelRatia');
    Route::get('level/increase', 'LevelController@levelIncrease');
    Route::resource('level', 'LevelController');
    //星球会员等级数据信息
    Route::get('star_level/increase', 'StarLevelController@starLevelIncrease');
    Route::resource('star_level', 'StarLevelController');
    //积分数据信息
    Route::get('point/get_rule', 'PointController@getRule');
    Route::post('point/set_rule', 'PointController@setRule');
    Route::get('point/record', 'PointController@record');
    Route::get('point/record_total', 'PointController@record_total');
    Route::get('point/detail', 'PointController@detail');
    //优惠券模板接口
    Route::get('coupon/all_pro_shop', 'CouponController@allProShop');
    Route::get('coupon/status/{id}', 'CouponController@statusChange'); //优惠券模板状态变更
    Route::post('coupon/store/{id}', 'CouponController@addStore'); //优惠券模板状库存增加
    Route::resource('coupon', 'CouponController');
    //优惠券发放记录
    Route::post('grand/excel', 'CouponGrandController@excelStore');
    Route::get('grand/sign_check', 'CouponGrandController@signCheck');
    Route::get('grand/check_user', 'CouponGrandController@checkUser');
    Route::get('grand/tpl', 'CouponGrandController@getTemplate');
    Route::get('grand/coupon_number', 'CouponGrandController@couponNumber');
    Route::get('grand/state/{id}', 'CouponGrandController@changeState');
    Route::get('grand/all_user', 'CouponGrandController@allUser');
    Route::get('grand/top_show/{id}', 'CouponGrandController@topShow');
    Route::get('grand/coupon_code/{grand_id?}', 'CouponGrandController@codeRecord');
    Route::resource('grand', 'CouponGrandController');
    //活动管理
    Route::get('active/status/{id}', 'ActiveController@changeStatus');
    Route::get('active/order/{id}', 'ActiveController@activeOrder');
    Route::resource('active', 'ActiveController');
    //门店列表
    Route::get('shop/province', 'ShopController@province');
    Route::get('shop/city', 'ShopController@city');
    Route::get('/shopList', 'ShopController@shopList');
    //分类列表
    Route::get('/category', 'ShopController@category');
    Route::get('/product', 'ShopController@product');
    //加料列表
    Route::get('material', 'ShopController@material');
    //积分商城商品
    Route::get('mall_product/scan_mall/{id}', 'MallProductController@scanMall');
    Route::get('mall_product/sort/{id}', 'MallProductController@sort');
    Route::get('mall_product/status/{id}', 'MallProductController@statusChange');
    Route::post('mall_product/store/{id}', 'MallProductController@addStore');
    Route::post('mall_product/stock', 'MallProductController@takeStock');
    Route::resource('mall_product', 'MallProductController');
    //积分商城订单
    Route::get('mall_order/count', 'MallOrderController@dataCount');
    Route::get('mall_order/express', 'MallOrderController@express');
    Route::post('mall_order/edit_express/{id}', 'MallOrderController@editExpress');
    Route::get('mall_order/refund', 'MallOrderController@refund');
    Route::get('mall_order/express_traces', 'MallOrderController@expressTraces');
    Route::get('mall_order/excelSign', 'MallOrderController@excelSign');
    Route::post('mall_order/storeExcel', 'MallOrderController@storeExcel');
    Route::get('mall_order/updateExcel', 'MallOrderController@updateExcel');
    Route::resource('mall_order', 'MallOrderController');
    //意见反馈
    Route::post('comment/reply', 'CommentController@commentReply');
    Route::resource('comment', 'CommentController');
    //储值
    Route::get('cash_storage/storage_num', 'CashStorageController@storageNum');
    Route::get('cash_storage/storage_increase', 'CashStorageController@storageIncrease');
    Route::get('cash_storage/storage_rate', 'CashStorageController@storageRate');
    Route::get('cash_storage/add_up', 'CashStorageController@feeAddUp');
    Route::resource('cash_storage', 'CashStorageController');
    //储值账单明细
    Route::get('cash_flow_bill/account/{user_id?}', 'CashFlowBillController@accountMoney');
    Route::get('cash_flow_bill/bill_no_detail/{user_id?}', 'CashFlowBillController@billNoDetail');
    Route::get('cash_flow_bill/excel_balance/{user_id?}', 'CashFlowBillController@excelBalance');
    Route::get('cash_flow_bill/rebill_count', 'CashFlowBillController@rebillCount');  //充值统计
    Route::get('cash_flow_bill/rebuy_count', 'CashFlowBillController@rebuyCount');  //消费统计
    Route::resource('cash_flow_bill', 'CashFlowBillController');
    //余额钱包配置
    Route::get('wallet/delete', 'WalletController@delete');
    Route::post('wallet/add', 'WalletController@add');
    Route::get('wallet/query', 'WalletController@query');
    Route::get('wallet/gift_time', 'WalletController@setGiftTime');
    Route::get('wallet', 'WalletController@index');
    //重置密码
    Route::post('password/reset', 'WalletController@resetPassword');
    //管理账号后台
    Route::post('resetPassword/{id}', 'AdminController@resetPassword');
    Route::post('updatePasswordNew', 'AdminController@updatePasswordNew');
    Route::post('role/{id}', 'AdminController@role');
    Route::resource('admin','AdminController');
    Route::get('permission', 'RoleController@permission');
    Route::post('authPermission/{id}', 'RoleController@authPermission');
    Route::resource('role','RoleController');

    // 后台赠送会员卡
    Route::get('star/config', 'StarConfigController@index'); // 单个用户信息查询
    Route::post('star/config/excel', 'StarConfigController@upExcel'); // 批量操作 导入excel表
    Route::get('star/config/list', 'StarConfigController@queryMemberList'); // 根据flag找到excel里的用户列表
    Route::get('star/config/count', 'StarConfigController@starConfigCount'); // 历史调配总人数
    Route::get('star/config/history', 'StarConfigController@starConfigRecords'); // 历史调配记录
    Route::post('star/config', 'StarConfigController@starConfig'); // 给传入用户发卡 调整等级

    // 会员兑换码
    Route::post('star/cdkey/order', 'StarCDKEYController@createCardOrder'); // 新增兑换码购买订单
    Route::put('star/cdkey/order', 'StarCDKEYController@updateCardOrder'); // 修改兑换码订单
    Route::get('star/cdkey/order', 'StarCDKEYController@cardOrderList'); // 兑换码购买订单列表
    Route::get('star/cdkey', 'StarCDKEYController@cdkey'); // 兑换码列表
    Route::get('star/cdkey/export/token', 'StarCDKEYController@checkExport'); // 获取导出excel的token
    Route::delete('star/cdkey/order/{id}', 'StarCDKEYController@deleteCardOrder'); // 删除兑换码订单

    Route::post('vka/upgrade', 'AdminVkaController@upgrade');
    Route::get('member/data/amount', 'MemberController@memberData'); // 付费数据与迁移数据
    
    Route::post('vka/upgrade', 'AdminVkaController@upgrade');
});
