<?php

Route::any('takeaway/orders', 'ExpressController@test');
Route::any('wechat/notify', 'PaymentController@payment');
