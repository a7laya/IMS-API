<?php

namespace app\controller\admin;

use think\Request;
use app\controller\common\Base;
class Goods extends Base
{
	protected $excludeValidateCheck = ['hotList','create'];
    
    // 审核商品
    public function checkGoods(){
    	$request = request();
        return showSuccess($request->Model->save([
            'ischeck'=>$request->param('ischeck')
        ]));
    }
    
    // 后台商品列表
    public function index()
    {
    	
    	$param = request()->param();
        $limit = intval(getValByKey('limit',$param,10));
        $tab = getValByKey('tab',$param,'all');
        $model = $this->M;
        // 订单类型
        switch ($tab) {
        	case 'checking': // 审核中
        		$model = $this->M->where('ischeck',0)
        						 ->whereNull('delete_time');
        		break;
        	case 'saling': // 销售中
        		$model = $this->M->where('ischeck',1)
        						 ->where('status',1);
        		break;
        	case 'off': // 已下架
        		$model = $this->M->where('status',0);
        		break;
        	case 'min_stock': // 库存预警
        		$model = $this->M->where('status',0)
        						 ->whereColumn('stock','<=','min_stock');
        		break;
        	case 'delete': // 回收站
        		$model = $this->M->onlyTrashed();
        		break;	
        }
        // 搜索条件
        if (array_key_exists('category_id',$param)) {
        	$model = $model->where('category_id',$param['category_id']);
        }
        if (array_key_exists('title',$param)) {
        	$model = $model->where('title','like','%'.$param['title'].'%');
        }
        
        $totalCount = $model->count();
        $list = $model->page($param['page'],$limit)
		        ->with(['category','goodsBanner','goodsAttrs','goodsSkus','goodsSkusCard.goodsSkusCardValue'])
		        ->order([ 'id'=>'desc' ])
				->select();
		// 分类
        $cates = (new \app\model\admin\Category())->select();
        $cates = list_to_tree($cates->toArray(),'category_id');
        return showSuccess([
        	'list'=>$list,
        	'totalCount'=>$totalCount,
        	'cates'=>$cates
        ]);
    	
        return showSuccess($this->M->Mlist());
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
    	// 分类
        $cates = (new \app\model\admin\Category())->select();
        $cates = list_to_tree($cates->toArray(),'category_id');
        // 运费模板
        $express = (new \app\model\admin\Express())->Mlist();
        // 商品类型列表
        $type = (new \app\model\admin\GoodsType())->with(['goodsTypeValues'=>function($q){
        	$q->where('status',1);
        }])->where('status',1)->select();
        // 获取goods_id为0的商品规格列表
        $goodsSkusCard = (new \app\model\admin\GoodsSkusCard())->with(['goodsSkusCardValue'])->where('goods_id',0)->select();
        return showSuccess(compact('cates','express','type','goodsSkusCard'));
    }

	// 后端商品详情
	public function adminread($id)
    {
    	$goods = request()->Model->append(['goodsBanner','goodsAttrs','goodsSkus','goodsSkusCard.goodsSkusCardValue']);
    	// 商品类型列表
        $types = (new \app\model\admin\GoodsType())->with(['goodsTypeValues'=>function($q){
        	$q->where('status',1);
        }])->where('status',1)->select();
        
        $goods->types = $types;
        return showSuccess($goods);
    }

	// 获取当前商品的轮播图
	public function banners(){
        return showSuccess(request()->Model->goodsBanner);
	}
	
	// 更新当前商品的轮播图
	public function updateBanners(){
		// 删除之前
		$goods_id = request()->Model->id;
		request()->Model->goodsBanner()->where([
			'goods_id'=>$goods_id
		])->delete();
		$banners = request()->param('banners');
		$data = array_map(function($item) use($goods_id){
			return [
				'url'=>$item,
				'goods_id'=>$goods_id
			];
		},$banners);
		$res = request()->Model->goodsBanner()->saveAll($data);
		return showSuccess($res);
	}

	// 更新商品属性
	public function updateAttrs(){
		// 删除之前
		$goods_id = request()->Model->id;
		request()->Model->goodsAttrs()->where([ 'goods_id'=>$goods_id ])->delete();
		// 创建新的
		$goods_attrs = request()->param('goods_attrs');
		$res = request()->Model->goodsAttrs()->saveAll($goods_attrs);
		return showSuccess($res);
	}

	// 更新商品规格
	public function updateSkus(){
		$params = request()->param();
		$goods = request()->Model;
		$GoodsSkus = new \app\model\admin\GoodsSkus();
		// 单规格
		if ($params['sku_type'] == 0) {
			// 原本多规格
			if ($goods->sku_type == 1) {
		        $GoodsSkus->where('goods_id',$goods->id)->delete();
			}
			$goods->sku_type = 0;
			$goods->sku_value = $params['sku_value'];
			$res = $goods->save();
			return showSuccess($res);
		}
		// 多规格
		$goods->sku_type = 1;
		$goods->save();
		// 清除多规格
        $GoodsSkus->where('goods_id',$goods->id)->delete();
		// 创建新的
		$res = $GoodsSkus->saveAll($params['goodsSkus']);
		return showSuccess($res);
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

    // 前端商品详情
    public function read($id)
    {
    	$goods = request()->Model->append(['goodsBanner','goodsAttrs','goodsSkus','goodsSkusCard.goodsSkusCardValue']);
    	
    	$goods->hotComments = $goods->comments()->with(['user'=>function($query){
    		$query->field(['id','nickname','avatar']);
    	}])->field(['id','rating','review','review_time','goods_num','user_id'])->order('goods_num','desc')->limit(3)->select();
    	
    	$goods->hotList = $this->M->hotList();
    	
        return showSuccess($goods);
    }
	

    // 更新
    public function update(Request $request, $id)
    {	
    	$param = $request->param();
    	if (count($param) <= 1) {
    		ApiException('参数错误');
    	}
        return $request->Model->save($param);
    }


    public function updateStatus(){
        return showSuccess($this->M->_updateStatus());
    }
    
    // 删除
    public function delete($id)
    {
        return showSuccess($this->M->Mdelete());
    }
    
    
    // 彻底删除
    public function destroy(){
    	ApiException('演示数据，禁止删除');
    	$ids = request()->param('ids');
    	$res = $this->M->onlyTrashed()->where('id','in',$ids)->select();
    	$res->each(function($item){
    		$item->force()->delete();
    	});
    	return showSuccess('ok');
    }
    
    // 批量恢复
    public function restore(){
    	$ids = request()->param('ids');
    	$res = $this->M->onlyTrashed()->where('id','in',$ids)->select();
    	$res->each(function($item){
    		$item->restore();
    	});
    	return showSuccess('ok');
    }
    
    // 批量删除
    public function deleteAll(){
    	// ApiException('演示数据，禁止删除');
    	$ids = request()->param('ids');
    	$res = $this->M->where('id','in',$ids)->select();
    	$res->each(function($item){
    		$item->delete();
    	});
    	return showSuccess('ok');
    }
    
    // 上架/下架
    public function changeStatus(){
    	$params = request()->param();
    	$res = $this->M->where('id','in',$params['ids'])->update([
    		'status'=>$params['status']
    	]);
    	return showSuccess($res);
    }
    
    
    // 商品评论
    public function comments(){
    	$params = request()->param();
    	
    	$where = [];
    	
    	if (array_key_exists('comment_type',$params)) {
    		$type = [
    			'good'=>'4,5',
    			'middle'=>'3',
    			'bad'=>'1,2'
    		];
    		$where = [
    			['rating','in',$type[$params['comment_type']]]
    		];
    	}
    	
    	$page = array_key_exists('page',$params) ? (int)$params['page'] : 1;
    	
    	$comments = request()->Model->comments()->with(['user'=>function($query){
    		$query->field(['id','nickname','avatar']);
    	}])
    	->field(['id','rating','review','review_time','goods_num','user_id','extra'])
    	->where($where)
    	->order('goods_num','desc')
    	->page($page,10)
    	->select();
    	
    	$total = request()->Model->comments()->count();
    	
    	$good = request()->Model->comments()->where('rating','in','4,5')->count();
    	
    	return showSuccess([
    		'list'=>$comments,
    		'total'=>$total,
    		'good_rate'=>$total > 0 ? ($good/$total) : 0
    	]);
    }
    
    
    // 搜索商品
    public function search(){
        $params = request()->param();
        // 条件
        $where = [
            [ 'title','like','%'.$params['title'].'%' ]
        ];
        
        if(array_key_exists('price',$params)){
        	$price = explode(',',$params['price'],2);
        	$where[] = [
        		'min_price',$price[0],$price[1]
        	];
        }
        // 排序
        $order = [];
        
        if(array_key_exists('all',$params)){
        	$order['sale_count'] = $params['all'];
        	$order['min_price'] = $params['all'];
        }
        
        if(array_key_exists('sale_count',$params)){
        	$order['sale_count'] = $params['sale_count'];
        }
        
		if(array_key_exists('min_price',$params)){
        	$order['min_price'] = $params['min_price'];
        }

        // 分页
        $page = getValByKey('page',$params,1);
        $list = $this->M
        ->field(['id','title','cover','min_price','desc'])
        ->withCount(['comments','comments'=>function($query,&$alias){
        	$query->where('rating','in','4,5');
        	$alias = 'comments_good_count';
        }])
        ->where($where)
        ->order($order)
        ->page($page,10)
        ->select();
        
        return showSuccess($list);
    }
    
    // 热门推荐
    public function hotList(){
    	$hotList = $this->M->hotList();
    	return showSuccess($hotList);
    }
}
