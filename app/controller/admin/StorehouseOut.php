<?php
/*
 * @Author: your name
 * @Date: 2020-06-15 08:16:34
 * @LastEditTime: 2020-06-15 11:14:02
 * @LastEditors: Please set LastEditors
 * @Description: In User Settings Edit
 * @FilePath: /api.a7laya.com/app/controller/admin/StorehouseIn.php
 */ 

namespace app\controller\admin;
use app\model\admin\Goods;


use think\Request;
// 引入基类控制器
use app\controller\common\Base;

class StorehouseOut extends Base
{
    protected $excludeValidateCheck = ['index'];
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


    // 修改状态
    public function updateStatus(Request $request)
    {
        return showSuccess($this->M->_UpdateStatus());
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {   
        return showSuccess($this->M->Mdelete());
    }

    // 批量删除
    public function deleteAll(){
        return showSuccess($this->M->MdeleteAll());
    }


}
