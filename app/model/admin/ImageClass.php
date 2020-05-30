<?php
/*
 * @Author: your name
 * @Date: 2020-05-27 15:47:55
 * @LastEditTime: 2020-05-28 10:06:45
 * @LastEditors: Please set LastEditors
 * @Description: In User Settings Edit
 * @FilePath: /api.a7laya.com/app/model/admin/ImageClass.php
 */ 

namespace app\model\admin;

use app\model\common\BaseModel;

/**
 * @mixin think\Model
 */
class ImageClass extends BaseModel
{
    // 获取当前相册下的图片 
    public function images(){
        return $this->hasMany('Image');
    }

    // 相册列表
    public function Mlist(){
        $param = request()->param();
        $limit = intval(getValByKey('limit',$param,10));
        $totalCount = $this->count();
        $list = $this->withCount('images')->page($param['page'],$limit)->order([
        			'order'=>'desc',
	        		'id'=>'desc'
        		])->select();
        return [
        	'list'=>$list,
            'totalCount'=>$totalCount,
        ];
    }
    
    // 指定相册下的图片列表
    public function MimageList(){
        $param = request()->param();
        $limit = intval(getValByKey('limit',$param,10));
        $model = request()->Model->images();
        $order = getValByKey('order',$param,'desc');
        $where = [];
        $keyword = getValByKey('keyword',$param,false);
        if($keyword){
        	$where[] = [
        		['name','like','%'.$keyword.'%']
        	];
        }
        return [
        	'list'=>$model->page($param['page'],$limit)->where($where)->order('id',$order)->select(),
        	'totalCount'=>$model->where($where)->order('id',$order)->count()
        ];
    }

    // 创建相册
    public function Mcreate(){
        $param = request()->param();
        return $this->create($param);
    }

    // 修改相册
    public function Mupdate(){
        $param = request()->param();
        return request()->Model->save($param);
    }

    // 删除相册
    public function Mdelete(){
        return request()->Model->delete();
    }
}
