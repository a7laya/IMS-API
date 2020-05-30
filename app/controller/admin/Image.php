<?php
/*
 * @Author: your name
 * @Date: 2020-05-28 11:36:05
 * @LastEditTime: 2020-05-28 11:37:22
 * @LastEditors: Please set LastEditors
 * @Description: In User Settings Edit
 * @FilePath: /api.a7laya.com/app/controller/admin/Image.php
 */ 

namespace app\controller\admin;

use think\Request;
use app\controller\common\Base;

class Image extends Base
{
 
    // 默认相册0 图片列表
    public function index(){
        return showSuccess($this->M->Mlist());
    }

    /**
     * 根据关键字返回搜索建议
     *
     * @param  \think\Request  $request
     * @param  name  图片名称关键字
     * @param  page  页码
     * @param  limit  每页数量
     * @return \think\Response
     */
    public function find(Request $request){
        $param = $request->param();
        $model = $this->M;
        $limit = intval(getValByKey('limit',$param,10000));
		if (array_key_exists('name',$param)) {
        	$model = $model->with('imageClass')->where('name','like','%'.$param['name'].'%');
		}
		$totalCount = $model->count();
		$list = $model->page($param['page'],$limit)
                ->with('imageClass')
		        ->order([ 'id'=>'desc' ])
				->select();
		return showSuccess([
			'list'=>$list,
			'totalCount'=>$totalCount,
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
