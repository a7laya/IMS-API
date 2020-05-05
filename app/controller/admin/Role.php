<?php

namespace app\controller\admin;

use think\Request;
use app\controller\common\Base;
class Role extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $param = request()->param();
        $limit = intval(getValByKey('limit',$param,10));
        $totalCount = $this->M->count();
        $list = $this->M->with(['rules'=>function($q){
        	$q->alias('a')->field('a.id');
        }])->page($param['page'],$limit)->order(['id'=>'desc'])->select();
        return showSuccess([
        	'list'=>$list,
        	'totalCount'=>$totalCount
        ]);
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
    	if (request()->Model->id == 2 || request()->Model->id == 3) {
    		ApiException('演示数据，禁止操作');
    	}
        return showSuccess($this->M->Mupdate());
    }

    // 给角色授予权限
    public function setRules(){
    	if (request()->Model->id == 2 || request()->Model->id == 3) {
    		return ApiException('演示数据，禁止操作');
    	}
    	$param = request()->param();
        $rules = getValByKey('rule_ids',$param,[]);
        return showSuccess(request()->Model->setRules($rules));
    }

    // 取消角色权限
    public function delRules(){
        // $rule = request()->param('rule_ids');
        // return showSuccess(request()->Model->delRules($rule));
    }

    // 修改状态
    public function updateStatus(Request $request)
    {
    	if (request()->Model->id == 2 || request()->Model->id == 3) {
    		ApiException('演示数据，禁止操作');
    	}
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
    	if (request()->Model->id == 2 || request()->Model->id == 3) {
    		ApiException('演示数据，禁止操作');
    	}
    	if (count(request()->Model->managers) > 0) {
    		ApiException('该角色下还有其他管理员，请先修改');
    	}
        return showSuccess($this->M->Mdelete());
    }
}
