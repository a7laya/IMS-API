<?php
/*
 * @Author: laya
 * @Date: 2020-05-29 09:58:19
 * @LastEditTime: 2020-05-29 10:38:45
 * @LastEditors: Please set LastEditors
 * @Description: In User Settings Edit
 * @FilePath: /api.a7laya.com/app/model/admin/SysField.php
 */ 

namespace app\model\admin;

use app\model\common\BaseModel;

/**
 * @mixin think\Model
 */
class SysField extends BaseModel
{
    // 列表
    public function Mlist(){
        $param = request()->param();
        $limit = intval(getValByKey('limit',$param,10));
        $page = intval(getValByKey('page',$param,1));
        $keyword = getValByKey('keyword',$param,'');
        $where = [];
        $where[] = [
            ['name','like','%'.$keyword.'%']
        ];
        $totalCount = $this->where($where)->count();
        $list = $this->page($page,$limit)
                     ->where($where)
                     ->order(['id'=>'desc'])
                     ->select();
        return [ 
        	'list'=>$list,
        	'totalCount'=>$totalCount
        ];
    }

}
