<?php
/*
 * @Author: your name
 * @Date: 2020-06-15 08:10:42
 * @LastEditTime: 2020-06-19 11:32:14
 * @LastEditors: Please set LastEditors
 * @Description: In User Settings Edit
 * @FilePath: /api.a7laya.com/app/model/admin/StorehouseIn.php
 */ 

namespace app\model\admin;

use app\model\common\BaseModel;
use app\model\admin\Storehouse;
use app\model\admin\Goods;
use app\model\admin\GoodsStorehouse;

use think\model\concern\SoftDelete;

/**
 * @mixin think\Model
 */
class StorehouseOut extends BaseModel
{   
    
    use SoftDelete;
    protected $deleteTime = 'delete_time';

    // 出库单对应的商品
    public function goods(){
        return $this->belongsTo('Goods');
    }

    // 出库
    public function Mcreate(){
        $storehouse_stock = request()->param('storehouse_stock');
        $param = request()->param();
        $arr = Array();
        foreach($storehouse_stock as $v){
            $this->reduceStock($v['stock'],$v['storehouse_id']);
            $param['stock'] = $v['stock'];
            $param['storehouse_id'] = $v['storehouse_id'];
            $arr[] = $param;
        }
        return $this->saveAll($arr);
    }

    
    // 修改
    public function Mupdate(){
        $param = request()->param();
        $id = request()->param('id');
        $this->addStock($id);
        $this->reduceStock();
        return request()->Model->save($param);
    }

    // 删除单个
    public function Mdelete(){
        $id = request()->param('id');
        $this->addStock($id);
        return request()->Model->force()->delete();
    }

    // 批量删除
    public function MdeleteAll(){
        $ids = request()->param('ids');
        forEach($ids as $id){
            $this->addStock($id);
        }
        // 找到所有数据并删除
        return $this->where('id','in',$ids)->force()->delete();
    }

    // 根据传过来的param减少相应的商品库存 - 创建出库单时
    private function reduceStock($stock = false, $storehouse_id = false){
        $param = request()->param();
        $goods_id = intval(getValByKey('goods_id',$param,0));
        $storehouse_id = $storehouse_id ? $storehouse_id : intval(getValByKey('storehouse_id',$param,0));
        $stock = $stock ? $stock : intval(getValByKey('stock',$param,0));

        // ========== 更新商品Goods总库存 ==========
        $goods = Goods::find($goods_id);
        $goods->stock -= $stock;
        $goods->save();

        // ========== 更新相应位置GoodsStorehouse库存 ==========
        $this->updateHouseStock($goods_id, $storehouse_id, -$stock);
    }

    // 根据传过来的出库id增加相应的商品库存 - 删除出库单时
    private function addStock($id) {
        $item = $this->find($id)->toArray();
        $goods_id = $item['goods_id'];
        $storehouse_id = $item['storehouse_id'];
        $stock = $item['stock'];
        $goods = Goods::find($goods_id);
        $goods->stock += $stock;
        $goods->save();

        // ========== 更新相应位置GoodsStorehouse库存 ==========
        $this->updateHouseStock($goods_id, $storehouse_id, $stock);
    }

    // 更新表GoodsStorehouse里面的库存
    private function updateHouseStock($goods_id, $storehouse_id, $stock) {
        // 先查看该位置有没有存放过该商品
        $house = GoodsStorehouse::where([
            ['goods_id','=',$goods_id],
            ['storehouse_id','=',$storehouse_id]
        ])->find();
        if(isset($house)){
            // 如果该位置对应的商品有记录，则直接更新
            $house->stock += $stock;
            if($house->stock == 0) return $house->force()->delete();
        }else{
            // 如果该位置对应的商品无记录，则新增
            $house = new GoodsStorehouse;
            $house->goods_id = $goods_id;
            $house->storehouse_id = $storehouse_id;
            $house->stock = $stock;
        }
        $house->save();
    }

    // 列表
    public function Mlist(){
        $param = request()->param();
        $limit = intval(getValByKey('limit',$param,10));
        $page = intval(getValByKey('page',$param,1));
        // $keyword = getValByKey('keyword',$param,'');
        $totalCount = $this->count();
        $list = $this->page($page,$limit)->with('goods')->order([
            'time'=>'desc',
            'id'=>'desc'
        ])->select();
        
        // 获取仓库列表
        $storehouse = Storehouse::field('id,name,storehouse_id')->select();
        
        // 给每个数据行加上仓库的路径
        foreach($list as $v){
            $v['storehouse'] = $this->getParentPath($storehouse,$v['storehouse_id'],"storehouse_id");
        }
        return [ 
            'list'=>$list,
            'totalCount'=>$totalCount
        ];
    }

    private function getParentPath($cate,$id,$pid_name){
        $arr=array();
        foreach($cate as $v){
            if(isset($v['id']) && $v['id'] == $id) {
                $arr[] = $v;
                $arr = array_merge($this->getParentPath($cate, $v[$pid_name], $pid_name),$arr);
            }
        }
        return $arr;
    }


}
