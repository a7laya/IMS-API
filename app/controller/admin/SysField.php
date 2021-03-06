<?php
/*
 * @Author: laya
 * @Date: 2020-05-29 15:43:31
 * @LastEditTime: 2020-05-30 16:27:21
 * @LastEditors: Please set LastEditors
 * @Description: In User Settings Edit
 * @FilePath: /api.a7laya.com/app/controller/admin/SysField.php
 */ 

namespace app\controller\admin;

use think\Request;
use app\controller\common\Base;
class SysField extends Base
{
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
