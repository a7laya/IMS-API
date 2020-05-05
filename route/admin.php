<?php


use think\facade\Route;
// 不需要验证
Route::group('admin',function(){
    // 管理员登录
    Route::post('login','admin.Manager/login');
})->allowCrossDomain();
// 验证登录
Route::group('admin',function(){
	 // 退出登录
    Route::post('logout','admin.Manager/logout');
})->middleware(\app\middleware\hasManagerLogin::class);
// 需要验证权限
Route::group('admin',function(){
    /**
     * 管理员相关
     */
    // 当前管理员
    Route::get('manager/id/:id','admin.Manager/read');
    Route::post('manager/:id/delete','admin.Manager/delete');
    Route::get('manager/:page','admin.Manager/index');
    Route::post('manager','admin.Manager/save');
    Route::post('manager/:id','admin.Manager/update');
    Route::post('manager/:id/update_status','admin.Manager/updateStatus');

    // 规则
    Route::get('rule/:page','admin.Rule/index');
    Route::post('rule/:id/delete','admin.Rule/delete');
    Route::post('rule','admin.Rule/save');
    Route::post('rule/:id','admin.Rule/update');
    Route::post('rule/:id/update_status','admin.Rule/updateStatus');

    // 角色
    Route::post('role/:id/delete','admin.Role/delete');
    Route::get('role/:page','admin.Role/index');
    Route::post('role','admin.Role/save');
    Route::post('role/set_rules','admin.Role/setRules'); // 设置角色权限
    Route::post('role/del_rules','admin.Role/delRules'); // 删除角色权限
    Route::post('role/:id','admin.Role/update');
    Route::post('role/:id/update_status','admin.Role/updateStatus');

    // 相册管理
    Route::get('imageclass/:id/image/:page$','admin.ImageClass/images');
    Route::get('imageclass/:page','admin.ImageClass/index');
    Route::post('imageclass','admin.ImageClass/save');
    Route::post('imageclass/:id','admin.ImageClass/update');
    Route::delete('imageclass/:id','admin.ImageClass/delete');

    // 附件管理
    Route::get('image/:page','admin.Image/index');
    Route::post('image/upload','admin.Image/save');
    Route::post('image/delete_all$','admin.Image/deleteAll');
    Route::post('image/:id','admin.Image/update');
    Route::delete('image/:id','admin.Image/delete');

    // 商品分类
    Route::get('category','admin.Category/index');
    Route::post('category','admin.Category/save');
    Route::post('category/sort','admin.Category/sortCategory');
    Route::post('category/:id','admin.Category/update');
    Route::post('category/:id/update_status','admin.Category/updateStatus');
    Route::delete('category/:id','admin.Category/delete');

    // 商品规格
    Route::get('skus/:page','admin.Skus/index');
    Route::post('skus','admin.Skus/save');
    Route::post('skus/delete_all','admin.Skus/deleteAll');
    Route::post('skus/:id','admin.Skus/update');
    Route::post('skus/:id/update_status','admin.Skus/updateStatus');
    Route::post('skus/:id/delete','admin.Skus/delete');
    

    // 商品规格值
    // Route::get('skus_value/:page','admin.SkusValue/index');
    // Route::post('skus_value','admin.SkusValue/save');
    // Route::post('skus_value/:id','admin.SkusValue/update');
    // Route::delete('skus_value/:id','admin.SkusValue/delete');

    // 商品类型
    Route::get('goods_type/:page','admin.GoodsType/index');
    Route::post('goods_type','admin.GoodsType/save');
    Route::post('goods_type/delete_all','admin.GoodsType/deleteAll');
    Route::post('goods_type/:id','admin.GoodsType/update');
    Route::post('goods_type/:id/update_status','admin.GoodsType/updateStatus');
    Route::post('goods_type/:id/delete','admin.GoodsType/delete');

    // 商品类型属性
    // Route::get('goods_type_value/:page','admin.GoodsTypeValue/index');
    // Route::post('goods_type_value','admin.GoodsTypeValue/save');
    // Route::post('goods_type_value/:id','admin.GoodsTypeValue/update');
    // Route::post('goods_type_value/:id/update_status','admin.GoodsTypeValue/updateStatus');
    // Route::delete('goods_type_value/:id','admin.GoodsTypeValue/delete');

	// 商品评论
	Route::get('goods_comment/:page','admin.OrderItem/index');
	Route::post('goods_comment/review/:id','admin.OrderItem/review');
	Route::post('goods_comment/:id/update_status','admin.OrderItem/updateStatus');
	
	 // 会员
    Route::get('user/:page','admin.User/index');
    Route::post('user','admin.User/save');
    Route::post('user/:id','admin.User/update');
    Route::post('user/:id/update_status','admin.User/updateStatus');
    Route::post('user/:id/delete','admin.User/delete');

    // 会员等级
    Route::get('user_level/:page','admin.UserLevel/index');
    Route::post('user_level','admin.UserLevel/save');
    Route::post('user_level/:id','admin.UserLevel/update');
    Route::post('user_level/:id/update_status','admin.UserLevel/updateStatus');
    Route::post('user_level/:id/delete','admin.UserLevel/delete');
	
	
	Route::get('express_company/:page','admin.ExpressCompany/index');
	// 批量删除订单
	Route::post('order/delete_all','admin.Order/deleteAll');
	// 订单
	Route::get('order/:page','admin.Order/orderList');
	// 订单发货
    Route::post('order/:id/ship','admin.Order/ship');
    // 拒绝/同意
    Route::post('order/:id/handle_refund','admin.Order/handleRefund');
    // 导出订单
    Route::post('order/excelexport','admin.Order/excelexport');
	
	// 配置信息
	Route::get('sysconfig','admin.SysSetting/get');
	Route::post('sysconfig','admin.SysSetting/set');
	// 上传文件
	Route::post('sysconfig/upload','admin.SysSetting/upload');
	
	
    // 商品
    Route::get('goods/create','admin.goods/create');
    Route::get('goods/read/:id','admin.goods/adminread');
    Route::get('goods/banners/:id','admin.goods/banners');
    Route::post('goods/updateskus/:id','admin.goods/updateSkus');
    Route::post('goods/banners/:id','admin.goods/updateBanners');
    Route::post('goods/attrs/:id','admin.goods/updateAttrs');
    Route::get('goods/:page','admin.goods/index');
    Route::post('goods/restore','admin.goods/restore');
    Route::post('goods/destroy','admin.goods/destroy');
    Route::post('goods/delete_all','admin.goods/deleteAll');
    Route::post('goods/changestatus','admin.goods/changeStatus');
    Route::post('goods','admin.goods/save');
    Route::post('goods/:id','admin.goods/update');
    //Route::post('goods/:id/update_status','admin.goods/updateStatus');
    Route::post('goods/:id/check','admin.goods/checkGoods');
    Route::post('goods/:id/delete','admin.goods/delete');
    

    // 商品对应规格卡片
    Route::get('goods_skus_card/:page','admin.goodsSkusCard/index');
    Route::post('goods_skus_card','admin.goodsSkusCard/save');
    Route::post('goods_skus_card/sort','admin.goodsSkusCard/sort');
    Route::post('goods_skus_card/:id','admin.goodsSkusCard/update');
    Route::post('goods_skus_card/:id/delete','admin.goodsSkusCard/delete');
    
    // 商品对应规格卡片的值
    Route::get('goods_skus_card_value/:page','admin.goodsSkusCardValue/index');
    Route::post('goods_skus_card_value','admin.goodsSkusCardValue/save');
    Route::post('goods_skus_card_value/sort','admin.goodsSkusCardValue/sort');
    Route::post('goods_skus_card_value/:id','admin.goodsSkusCardValue/update');
    Route::post('goods_skus_card_value/:id/delete','admin.goodsSkusCardValue/delete');
    
    // 运费模板
    Route::get('express/:page','admin.Express/index');
    Route::post('express','admin.Express/save');
    Route::post('express/:id','admin.Express/update');
    Route::post('express/:id/delete','admin.Express/delete');

	// 运费模板
    Route::get('express_value/:page','admin.ExpressValue/index');
    Route::post('express_value','admin.ExpressValue/save');
    Route::post('express_value/:id','admin.ExpressValue/update');
    Route::post('express_value/:id/delete','admin.ExpressValue/delete');

    // 优惠券
    Route::get('coupon/:page','admin.Coupon/index');
    Route::post('coupon','admin.Coupon/save');
    Route::post('coupon/:id','admin.Coupon/update');
    Route::delete('coupon/:id','admin.Coupon/delete');

    // 商品轮播图
    Route::post('goods/:goods_id/goods_banner','admin.goodsBanner/save');
    Route::delete('goods/:goods_id/goods_banner/:id','admin.goodsBanner/delete');

})->middleware(\app\middleware\checkManagerToken::class)->allowCrossDomain();