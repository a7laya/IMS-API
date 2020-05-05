<?php

namespace app\controller\admin;

use think\Request;
use app\controller\common\Base;
class Category extends Base
{
	protected $excludeValidateCheck = ['app_category','index'];
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        return showSuccess($this->M->Mlist());
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
        return showSuccess($this->M->Mcreate());
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
        return showSuccess($this->M->Mupdate());
    }

    public function updateStatus(){
        return showSuccess($this->M->_updateStatus());
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
    	$id = intval($id);
    	if ($id <= 177) {
    		ApiException('演示数据，禁止删除');
    	}
        return showSuccess($this->M->Mdelete());
    }
    
    
    // app分类
    public function app_category()
    {
    	$list = $this->M->with(['appCategoryItems'])->select();
        return showSuccess($list);
    }

	
	// 排序
	public function sortCategory(){
		$data = request()->param('sortdata');
		$data = json_decode($data,true);
		return showSuccess($this->M->saveAll($data));
	}

}
