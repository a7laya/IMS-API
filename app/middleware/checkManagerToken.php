<?php
// 除了登录之外,基本上所有接口都要走这个中间件
declare (strict_types = 1);

namespace app\middleware;

class checkManagerToken
{
    /**
     * 处理请求
     *
     * @param \think\Request $request
     * @param \Closure       $next
     * @return Response
     */
    public function handle($request, \Closure $next)
    {
        // 获取token - 请求的时候会把token放入header头进行请求
        $token = $request->header('token');
        // token不存在?
        if(!$token)  ApiException('非法token');
        // 检查是否登陆 (通过token去查找缓存里面的$user信息)
        $user = cms_getUser([
            'token' => $token
        ]);
        if(!$user) ApiException('非法token,请先登录');
        // 挂在用户实例
        $request->UserModel = \app\model\Manager::find($user['id']);

        // 检查当前用户是否被禁用
        if(!$request->UserModel || !$request->UserModel->status){
            ApiException('当前用户被禁用');
        }

        // 验证当前用户的权限(超级管理员无需验证)
        if(!$request->UserModel->super){
            // todo...
        }
        // 往下走...
        return $next($request);
    }
}
