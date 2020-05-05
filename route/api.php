<?php

use think\facade\Route;

// 无需验证
Route::group('api',function(){
    // 会员登录退出
    Route::post('login','admin.User/login');

    // app首页分类和数据
    Route::get('index_category/data','web.AppIndexCategory/index');
    Route::get('index_category/:id/data/:page','web.AppIndexCategory/read');

	// 搜索商品
    Route::post('goods/search','admin.Goods/search');

	// 热门推荐
    Route::get('goods/hotlist','admin.Goods/hotList');
	
    // 查看商品
    Route::get('goods/:id','admin.Goods/read');
    
    // 商品评论
    Route::get('goods/:id/comments/[:comment_type]','admin.Goods/comments');

	// 商品分类
    Route::get('category/app_category','admin.Category/app_category');

});

// 只有会员能操作
Route::group('api',function(){
    // 退出登录
    Route::post('logout','admin.User/logout');

    // 会员收货地址
    Route::get('useraddresses/:page','web.UserAddresses/index');
    Route::post('useraddresses','web.UserAddresses/save');
    Route::post('useraddresses/:id','web.UserAddresses/update');
    Route::delete('useraddresses/:id','web.UserAddresses/delete');
	
    // 购物车
    Route::post('cart','admin.Cart/save');
    Route::get('cart','admin.Cart/index');
    Route::post('cart/delete','admin.Cart/delete');
    Route::post('cart/updatenumber/:id','admin.Cart/updateNumber');
    Route::get('cart/:id/sku','admin.Cart/read');
    Route::post('cart/:id','admin.Cart/update');

    // 订单
    Route::post('order','admin.Order/save');
    Route::post('order/:type','admin.Order/index');
    Route::get('order/:id','admin.Order/read');
    Route::post('closeorder/:id','admin.Order/closeOrder');
    // Route::delete('order','admin.Order/delete');
    // 订单收货
    Route::post('order/:id/received','admin.Order/received');

    // 商品评价
    Route::post('order_item/:id/review','admin.OrderItem/sendReview');

    // 查看物流信息
    Route::get('order/:id/get_ship_info','admin.Order/getShipInfo');

    // 申请退款
    Route::post('order/:id/apply_refund','admin.Order/applyRefund');

    // 领取优惠券
    Route::post('getcoupon/:id','admin.Coupon/getCoupon');
    // 用户优惠券列表(是否失效)
    Route::get('usercoupon/:page/:isvalid','admin.Coupon/userCoupon');
    // 优惠券列表分页
    Route::get('coupon/:page','admin.Coupon/getList');
    // 当前订单可用优惠券数量
    Route::post('coupon_count','admin.Coupon/couponCount');
    
    // 微信支付
	Route::get('payment/:id/wxpay','common.Payment/payByWechat');
    
    // 支付宝支付
	Route::get('payment/:id/alipay','common.Payment/payByAlipay');
	
	// 微信小程序支付
	Route::get('payment/:id/wxmppay/:code','common.Payment/payByWechatMp');
    
})->middleware(\app\middleware\checkUserToken::class);

// Route::get('wxpay/:id','common.Payment/payByWechat');

// 支付宝回调
Route::post('api/payment/alipay/notify', 'common.Payment/alipayNotify')->name('alipayNotify');
// 微信回调
Route::post('api/payment/wxpay/notify', 'common.Payment/wechatNotify')->name('wechatNotify');




