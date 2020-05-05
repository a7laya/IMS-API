<?php

namespace app\controller\web;

use think\Request;
use app\controller\common\Base;
class UserAddresses extends Base
{
    // 自动实例化模型
    protected $ModelPath = 'common\UserAddresses';
    /**
     * 获取当前会员的地址列表
     *
     * @return \think\Response
     */
    public function index(Request $request)
    {
        return showSuccess($request->UserModel->userAddresses()->page($request->param('page'),10)->order('last_used_time','desc')->select());
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
    	$param = $request->param();
    	if ($param['default'] == 1) {
    		$param['last_used_time'] = time();
    	}
        return showSuccess($request->UserModel->userAddresses()->save($param));
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        $param = $request->param();
        $param['last_used_time'] = $param['default'] == 1 ? time() : null;
        // 判断当前会员是否有权限
        $this->M->__checkActionAuth();
        $param['user_id'] = $request->UserModel->id;
        return showSuccess($request->Model->save($param));
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete(Request $request, $id)
    {
        // 判断当前用户是否有权限
        $this->M->__checkActionAuth();
        return showSuccess($request->Model->delete());
    }
}
