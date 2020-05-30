<?php

namespace app\model\admin;

use app\model\common\BaseModel;

/**
 * @mixin think\Model
 */
class Storehouse extends BaseModel
{
	// // 关联商品
	// public function appStorehouseItems(){
	// 	return $this->hasMany('AppStorehouseItem');
	// }

    // 清除与商品之间的关系
    public function delGoods($goodsId){
        return $this->goods()->detach($goodsId);
    }

    // 关联子分类
    public function childStorehouse(){
        return $this->hasMany('Storehouse');
    }
    
	// 关联商品
    public function goods(){
        return $this->belongsToMany('Goods','goods_storehouse');
    }

    
    // 列表
    public function Mlist(){
        $param = request()->param();
        // laya 排序
        $arr = $this->order('order','asc')->select();
        $totalCount = $this->count();
        $list = list_to_tree2($arr->toArray(),'storehouse_id');
        return [
        	'list'=>$list,
        	'totalCount'=>$totalCount,
        	'storehouse'=>list_to_tree($arr->toArray(),'storehouse_id')
        ];
    }

    /**
     * 删除仓库之前操作
     * 1. 删除商品和仓库的关联关系
     * 2. 删除对应的子分类
     * @param [type] $rule
     * @return void
     */
    public static function onBeforeDelete($storehouse){
       // 删除商品和仓库的关联关系
       $goodsIds = $storehouse->goods->map(function($v){
            return $v->id;
       })->toArray();
       if (count($goodsIds)) $storehouse->delGoods($goodsIds);

       // 删除对应的子分类
       $storehouse->childStorehouse->each(function($v){
            $v->delete();
       });
    }
}
