<?php
// +----------------------------------------------------------------------
// | 后台管理模块路由
// +----------------------------------------------------------------------
use think\facade\Route;

// 游客-不需要身份验证
Route::group('admin', function(){
    // 登录 
    Route::post('login', 'admin.Manager/login');
    // 登出
    Route::post('logout', 'admin.Manager/logout');
})->allowCrossDomain();

// 管理员-需要身份验证的
Route::group('admin', function(){
    // ========== 管理员 相关 ==========
    // 删除管理员 
    Route::post('manager/:id/delete', 'admin.Manager/delete');
    // 修改管理员状态
    Route::post('manager/:id/update_status', 'admin.Manager/updateStatus');
    // 更新管理员
    Route::post('manager/:id', 'admin.Manager/update');
    // 创建管理员 该路由规则要写在POST最下方
    Route::post('manager', 'admin.Manager/save');
    // 管理员列表
    Route::get('manager/:page', 'admin.Manager/index');

    
    // ========== 角色 相关 ==========
    // 设置角色权限
    Route::post('role/set_rules', 'admin.Role/setRules');
    // 删除角色 
    Route::post('role/:id/delete', 'admin.Role/delete');
    // 修改角色状态
    Route::post('role/:id/update_status', 'admin.Role/updateStatus');
    // 更新角色
    Route::post('role/:id', 'admin.Role/update');
    // 创建角色 该路由规则要写在POST最下方
    Route::post('role', 'admin.Role/save');
    // 角色列表
    Route::get('role/:page', 'admin.Role/index');


    
})->middleware(\app\middleware\checkManagerToken::class);