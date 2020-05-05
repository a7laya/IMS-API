<?php

namespace app\controller\admin;

use think\Request;
use app\controller\common\Base;
use think\facade\Db;
class OrderItem extends Base
{
    // 商品评价
    public function sendReview(){
        // 获取当前订单商品
        $orderItem = request()->Model;
        $order = $orderItem->order;
        // 是否是用户本人
        $user = request()->UserModel;
        if ($order->user_id !== $user->id) {
           ApiException('非法操作');
        }
        // 判断是否已经评价过了
        if ($orderItem->review_time) {
            ApiException('该订单已评价过了');
        }
        // 开启事务
        $result = Db::transaction(function () use($order,$orderItem){
            $param  = request()->param();
            // 更新orderItem
            $orderItem->rating = getValByKey('rating',$param,1);
            $orderItem->review =  getValByKey('review',$param,null);
            $orderItem->review_time = time();
            $orderItem->save();

            // 判断order下所有订单是否都已评价
            $reviewed = true;
            $res = $this->M->where([
                'order_id'=>$order->id,
                'review_time'=>null
            ])->find();
            if (!$res) {
                $order->reviewed = 1;
                $order->save();
            }
            return true;
        });
        return showSuccess($result);
    }
    // 评论列表
    public function index(){
    	$param = request()->param();
        $limit = intval(getValByKey('limit',$param,10));
        
        $title = getValByKey('title',$param,'');
        
        $list = \app\model\admin\OrderItem::hasWhere('goodsItem',function ($query) use($title){
        	$query->where('title', 'like', '%'.$title.'%');
        })
        ->whereNotNull('review_time')
        ->with(['goodsItem','user'])
        ->page($param['page'],$limit)
        ->order([ 'id'=>'desc' ])
        ->select();
        
        $totalCount = \app\model\admin\OrderItem::hasWhere('goodsItem',function ($query) use($title){
        	$query->where('title', 'like', '%'.$title.'%');
        })
        ->whereNotNull('review_time')
        ->count();
        
        return showSuccess([
        	'list'=>$list,
        	'totalCount'=>$totalCount
        ]);
    }
    
    // 客服回复
    public function review(){
    	// 获取当前订单商品
        $orderItem = request()->Model;
        $order = $orderItem->order;
        // 判断是否已经评价过了
        if (!$orderItem->review_time) {
            ApiException('该订单还没有被评价过');
        }
        $param  = request()->param();
        // 更新orderItem
        if($orderItem->extra === null){
        	$arr = [];
        	$arr[] = [
        		'isuser'=>false,
        		'data'=>$param['data'],
        		'good_num'=>0	
        	];
        	$orderItem->extra = $arr;
        } else {
        	$arr = $orderItem->extra;
        	for ($i = 0; $i < count($arr); $i++) {
        		 if (!$arr[$i]['isuser']) {
        		 	$arr[$i]['data'] = $param['data'];
        		 }
        	}
        	$orderItem->extra = $arr;
        }
        return showSuccess($orderItem->save());
    }
    
    public function updateStatus(){
        return showSuccess($this->M->_updateStatus());
    }
}
