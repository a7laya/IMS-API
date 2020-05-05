<?php

namespace app\controller\admin;

use think\Request;
use app\controller\common\Base;
use app\model\admin\SysSetting as SysSettingModel;
class SysSetting extends Base
{
	protected $excludeValidateCheck = ['get','upload'];
    /**
     * 设置
     *
     * @return \think\Response
     */
    public function set()
    {
    	$param = request()->param();
        $res = SysSettingModel::update($param,['id' => 1]);
        return showSuccess($res);
    }

    /**
     * 获取.
     *
     * @return \think\Response
     */
    public function get()
    {
        $data = $this->M->find(1);
        return showSuccess($data);
    }
    
    // 上传
    public function upload(){
    	$file = request()->file('file');
    	// 上传到本地服务器
    	$savename = \think\facade\Filesystem::putFile( 'wx', $file);
    	return showSuccess('/public/storage/'.$savename);
    }
}
