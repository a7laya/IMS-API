<?php

namespace app\controller\admin;

use think\Request;
use app\controller\common\Base;
use think\facade\Db;
use think\facade\Queue;

use Flex\Express\ExpressBird;

use jianyan\excel\Excel;

class Order extends Base
{
    protected $excludeValidateCheck = ['index','excelexport'];
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
    	$type = request()->param('type');
        $orders = request()->UserModel->orders()
                ->with([
                	'orderItems'=>function($query){
                		$query->field(['id','order_id','shop_id','goods_id','num','price','skus_type'])
                		->with([
	            			'goodsItem'=>function ($que){
	            				$que->field(['id','title','cover','sku_type']);
	            			},
	            			'goodsSkus'=>function($q){
        						$q->field(['id','skus']);
        					}
            			]);
                	}
                ])
                ->scope($type)
                ->order('id','desc')
                ->field(['id','user_id','no','total_price','paid_time','refund_status','ship_status','create_time','reviewed'])
                ->select();
        // 过滤
        $orders->each(function($item, $key){
        	$item->order_items->each(function($item2, $key2){
        		if($item2->skus_type === 0){
        			$item2->goods_skus = null;
        		}
        	});
        });
        return showSuccess($orders);
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
        // 启动事务
        $order = Db::transaction(function (){
            $param = request()->param();
            $userModel = request()->UserModel;

            // 1.更新收货地址最后操作时间
            $address =request()->UserAddresses;
            $address->last_used_time = time();
            $address->save();

            // 2.创建一个订单
            $order = $this->M->create([
                'address'=>$address->toArray(),
                'remark'=>getValByKey('remark',$param,''),
                'total_price'=>0,
                'user_id'=>$userModel->id,
                'coupon_user_id'=>getValByKey('coupon_user_id',$param,0)
            ]);

            if (!$order) {
                ApiException('创建订单失败');
            }

            // 订单总金额
            $totalPrice = 0;
            $cartIds = [];
            // 3.遍历用户提交的 SKU，创建一个 OrderItem 并直接与当前订单关联
            $items = request()->items;
            foreach ($items as $value) {
                // 获取模型
                $skuModel = $value['skus_type'] === 0 ?'\app\model\admin\goods':'\app\model\admin\goodsSkus';
                // 获取当前商品
                $sku = $skuModel::find($value['shop_id']);
                // 获取当前商品价格（多规格|单规格）
                $price = $sku->pprice ? $sku->pprice : $sku->sku_value->pprice;
                $goods_id = $value['skus_type'] === 0 ? $sku->id : $sku->goods_id;
                // 创建OrderItem
                $data = [
                    'skus_type'=>$value['skus_type'],
                    'shop_id'=> $value['shop_id'],
                    'num'=>$value['num'],
                    'price'=>$price,
                    'goods_id'=>$goods_id,
                    'user_id'=>$userModel->id
                ];
                $order->orderItems()->save($data);
                // 计算总金额
                $totalPrice += $data['price'] * $data['num'];
                // 减去优惠券
                if (request()->coupon) {
                    $totalPrice = $this->M->getPriceByCoupon($totalPrice);
                    request()->couponUser->changeUsed(1);
                }
                // 获取购物车id
                $cartIds[] = $value['id'];
                // 减库存
                if (!$this->M->decStock($data['num'],$sku)) {
                    ApiException('商品库存不足');
                }
            }
            // 4.更新订单总金额
            $order->total_price = $totalPrice;
            $order->save();
            // 5.将当前用户下单的商品从购物车中移除
            $userModel->carts()->where('id','in',$cartIds)->delete();
            // 触发关闭订单任务
            Queue::later(config('cms.order.delay'),'CloseOrder',[
                'orderId'=>$order->id
            ]);
            // 返回订单
            return $order;
        });
        return showSuccess($order);
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        // 验证是否是本人操作
        $this->M->__checkActionAuth();
        // 获取订单
        $order = request()->Model;
        $order->orderItems->each(function($item) use($order){
            if ($item->skus_type !== 1) {
                $order->append(['orderItems.goodsItem','couponUserItem.coupon']);
            }else{
                $order->append(['orderItems.goodsItem','orderItems.goodsSkus','couponUserItem.coupon']);
            }
        });
        $result = $order->toArray();
        // 未支付（自动取消）
        if(!$order->paid_time){
        	$result['end_time'] = strtotime($result['create_time']) + config('cms.order.delay');
        }
        // 已发货（自动收货）
        if($order->ship_status === 'delivered'){
        	$result['end_time'] = $order->ship_data->express_time + config('cms.order.received_delay');
        }
        return showSuccess($result);
    }


    // 发货
    public function ship(){
        $order = request()->Model;
        ApiException('演示数据，禁止操作');
        // 判断订单未付款
        if (!$order->paid_time) {
            ApiException('订单未付款');
        }
        // 判断订单是否关闭
        if ($order->closed) {
            ApiException('订单已关闭');
        }
        // 判断当前订单已发货
        if ($order->ship_status !== 'pending') {
            ApiException('订单已发货');
        }
        
        // 判断是否已退款
        if ($order->refund_status !== 'pending') {
            ApiException('订单已退款');
        }
        
        // 将订单发货状态改为已发货，并存入物流信息
        $param = request()->param();
        $order->ship_status = 'delivered';
        $order->ship_data = [
            'express_company'=>$param['express_company'],
            'express_no'=>$param['express_no'],
            'express_time'=>time()
        ];
        
        $result = $order->save();
        if($result){
        	// 触发自动确认收货任务
            Queue::later(config('cms.order.received_delay'),'autoReceived',[
                'orderId'=>$order->id
            ]);
        }
        
        return showSuccess($result);
    }


    // 订单收货
    public function received(){
        // 验证是否是用户本人
        $this->M->__checkActionAuth();
        // 获取订单
        $order = request()->Model;
        // 是否已关闭订单
        if ($order->closed) {
            ApiException('订单已关闭');
        }
        // 判断是否已发货
        if ($order->ship_status !== 'delivered') {
            ApiException('订单未发货');
        }
        // 更新为：已收货
        $order->ship_status = 'received';
        return showSuccess($order->save());
    }
    
    // 申请退款
    public function applyRefund(){
        // 验证当前用户
        $this->M->__checkActionAuth();
        // 判断是否已关闭订单
        $order = request()->Model;
        if ($order->closed) {
           ApiException('订单已关闭');
        }
        // 判断订单是否已付款
        if (!$order->paid_time) {
            ApiException('该订单未支付，不可退款');
        }
        // 判断订单状态是否正确
        if ($order->refund_status !== 'pending') {
            ApiException('该订单已申请过退款，请勿重复申请');
        }
        // 将用户输入的退款理由放到订单的 extra 字段中,将订单退款状态改为已申请退款
        $param = request()->param();
        $order->extra = [
            'refund_reason'=>$param['reason']
        ];
        $order->refund_status = 'applied';
        return showSuccess($order->save());
    }

    // 拒绝/同意 申请退款
    public function handleRefund(){
        // 获取当前订单
        $order = request()->Model;
        // 演示数据，禁止操作
        if (in_array($order->id,[256,275,285,302,317,79,148,149,186,221,222,236,244,245,268,282,283])) {
        	ApiException('演示数据，禁止操作');
        }
        // 判断订单状态是否正确
        if ($order->refund_status !== 'applied') {
            ApiException('订单状态不正确');
        }
        $param = request()->param();
        // 是否同意退款
        if ($param['agree']) {
            // 同意
            $this->__refundOrder($order);
        } else {
            // 拒绝退款
            $order->extra = [
                'refund_reason'=> isset($order->extra->refund_reason) ? $order->extra->refund_reason: null,
                'refund_disagree_reason'=>$param['disagree_reason']
            ];
            $order->refund_status = 'pending';
            $order->save();
        }
        return showSuccess($order);
    }

    // 退款逻辑
    public function __refundOrder($order){
        switch ($order->payment_method) {
            case 'alipay':
                // 生成退款单号
                $refundNo = \app\model\admin\Order::setRefundOrderNo();
                // 调用支付宝退款
                $obj = new \app\controller\common\AliPay();
                $alipay = $obj->alipay;
                $res = $alipay->refund([
                    'out_trade_no' => $order->no, // 之前的订单流水号
                    'refund_amount' => $order->total_price, // 退款金额，单位元
                    'out_request_no' => $refundNo, // 退款订单号
                ]);
                // 根据支付宝的文档，如果返回值里有 sub_code 字段说明退款失败
                if ($res->sub_code) {
                    // 将订单的退款状态标记为退款失败
                    $order->refund_no = $refundNo;
                    $order->refund_status ='failed';
                    $order->extra =[
                        'refund_failed_code'=>$res->sub_code
                    ];
                    $order->save();
                } else {
                    // 将订单的退款状态标记为退款成功并保存退款订单号
                    $order->refund_no = $refundNo;
                    $order->refund_status ='success';
                    $order->save();
                }
                break;
            case 'wechat':
                // 生成退款单号
                $refundNo = \app\model\admin\Order::setRefundOrderNo();
                // 调用微信退款
                $obj = new \app\controller\common\wechatPay();
                $wechat = $obj->wechat;
                $res = $wechat->refund([
                	'type' => 'app',
                	'out_trade_no' => $order->no, // 之前的订单流水号
				    'out_refund_no' => $refundNo, // 退款订单号
				    'total_fee' => strval($order->total_price*100), // 退款金额，单位元
				    'refund_fee' => strval($order->total_price*100), // 退款金额，单位元
				    'refund_desc' => $order->extra->refund_reason,
                ]);
                if ($res->return_code !== 'SUCCESS') {
                    // 将订单的退款状态标记为退款失败
                    $order->refund_no = $refundNo;
                    $order->refund_status ='failed';
                    $order->extra =[
                        'refund_failed_code'=>$res->return_msg
                    ];
                    $order->save();
                } else {
                    // 将订单的退款状态标记为退款成功并保存退款订单号
                    $order->refund_no = $refundNo;
                    $order->refund_status ='success';
                    $order->save();
                }
                break;
        }
    }
    
    // 取消订单
    public function closeOrder(){
    	 // 验证当前用户
        $this->M->__checkActionAuth();
        // 判断是否已关闭订单
        $order = request()->Model;
        if ($order->closed) {
           ApiException('订单已关闭');
        }
        // 判断订单是否已付款
        if ($order->paid_time) {
            ApiException('该订单已付款，不可关闭');
        }
        // 开始事务
        $result = Db::transaction(function() use($order){
            // 将订单的 closed 字段标记为 1，即关闭订单
            $order->closed = 1;
            $order->save();
            trace('[自动关闭订单] 设置订单为关闭状态', 'info');
            // 循环遍历订单中的商品 SKU，将订单中的数量加回到 SKU 的库存中去
            $order->orderItems->each(function($v) use($order){
                // 判断单规格还是多规格
                $skuModel = $v->skus_type === 0 ?'\app\model\admin\Goods':'\app\model\admin\GoodsSkus';
                // 根据订单获取当前商品
                $sku = $skuModel::find($v->shop_id);
                if ($sku) {
                    $order->addStock($v->num,$sku);
                } else {
                    $skuType = $v->skus_type === 0 ?'单规格':'多规格';
                }
            });
            // 恢复优惠券使用情况
            if ($order->coupon_user_id) {
               $CouponUser = \app\model\admin\CouponUser::find($order->coupon_user_id);
               $CouponUser->changeUsed(0);
            }
            return true;
        });
        if(!$result){
        	ApiException('关闭订单失败');
        }
        return showSuccess($result);
    }

	// 查看物流信息
	public function getShipInfo(){
		// // 验证当前用户
        $this->M->__checkActionAuth();
        // 判断是否已关闭订单
        $order = request()->Model;
        if ($order->closed) {
           ApiException('订单已关闭');
        }
        // 订单未发货
        if ($order->ship_status !== 'delivered' || $order->refund_status !== 'pending') {
           ApiException('订单状态不正确');
        }
		// 物流号
		if(!$order->ship_data || !$order->ship_data->express_no){
			ApiException('快递单号不存在');
		}
		
        // 其他安全验证
        $appkey = config('cms.ship.appkey');
		$url = "https://api.jisuapi.com/express/query?appkey=$appkey";
		$type = 'auto';
		$number = $order->ship_data->express_no;
		 
		$post = [
			'type'=>$type,
		    'number'=>$number
		];
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		// post数据
		curl_setopt($ch, CURLOPT_POST, 1);
		// post的变量
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		$result = curl_exec($ch);
		curl_close($ch);
		 
		$jsonarr = json_decode($result, true);
		
		return showSuccess($jsonarr);
	}


	// 后台订单列表
	public function orderList(){
		$param = request()->param();
        $limit = intval(getValByKey('limit',$param,10));
        $tab = getValByKey('tab',$param,'all');
        $model = $this->M;
        // 订单类型
        switch ($tab) {
        	case 'nopay': // 待付款
        		$model = $this->M->where('closed',0)
        						->whereNull('payment_method');
        		break;
        	case 'noship': // 待发货
        		$model = $this->M->where('closed',0)
        						->whereNotNull('payment_method')
        						->where('ship_status','pending')
        						->where('refund_status','pending');
        		break;
        	case 'shiped': // 已发货
        		$model = $this->M->where('closed',0)
        						->whereNotNull('payment_method')
        						->where('ship_status','delivered')
        						->where('refund_status','pending');
        		break;
        	case 'received': // 已收货
        		$model = $this->M->where('closed',0)
        						->whereNotNull('payment_method')
        						->where('ship_status','received')
        						->where('refund_status','pending');
        		break;
        	case 'finish': // 已完成
        		$model = $this->M->where('closed',0)
        						->whereNotNull('payment_method')
        						->where('ship_status','received')
        						->where('refund_status','pending');
        		break;
        	case 'closed': // 已关闭
        		$model = $this->M->where('closed',1);
        		break;
        	case 'refunding': // 退款中
        		$model = $this->M->where('closed',0)
        						->where('refund_status','applied');
        		break;
        }
        // 搜索条件
        if (array_key_exists('starttime',$param) && array_key_exists('endtime',$param)) {
        	$model = $model->whereTime('create_time', 'between', [$param['starttime'], $param['endtime']]);
        }
        if (array_key_exists('no',$param)) {
        	$model = $model->where('no','like','%'.$param['no'].'%');
        }
        if (array_key_exists('name',$param)) {
        	$model = $model->where('address->name','like','%'.$param['name'].'%');
        }
        if (array_key_exists('phone',$param)) {
        	$model = $model->where('address->phone','like','%'.$param['phone'].'%');
        }
        
        $totalCount = $model->count();
        $list = $model->page($param['page'],$limit)
		        ->with(['orderItems.goodsItem','user'])
		        ->order([ 'id'=>'desc' ])
				->select();
        return showSuccess([
        	'list'=>$list,
        	'totalCount'=>$totalCount
        ]);
	}


	// 批量删除
    public function deleteAll(){
    	ApiException('演示数据，禁止删除');
        return showSuccess($this->M->MdeleteAll());
    }
    
    // 导出订单
    public function excelexport(){
    	
    	$param = request()->param();
        $tab = getValByKey('tab',$param,'all');
        $model = $this->M;
        // 订单类型
        switch ($tab) {
        	case 'nopay': // 待付款
        		$model = $this->M->where('closed',0)
        						->whereNull('payment_method');
        		break;
        	case 'noship': // 待发货
        		$model = $this->M->where('closed',0)
        						->whereNotNull('payment_method')
        						->where('ship_status','pending')
        						->where('refund_status','pending');
        		break;
        	case 'shiped': // 已发货
        		$model = $this->M->where('closed',0)
        						->whereNotNull('payment_method')
        						->where('ship_status','delivered')
        						->where('refund_status','pending');
        		break;
        	case 'received': // 已收货
        		$model = $this->M->where('closed',0)
        						->whereNotNull('payment_method')
        						->where('ship_status','received')
        						->where('refund_status','pending');
        		break;
        	case 'finish': // 已完成
        		$model = $this->M->where('closed',0)
        						->whereNotNull('payment_method')
        						->where('ship_status','received')
        						->where('refund_status','pending');
        		break;
        	case 'closed': // 已关闭
        		$model = $this->M->where('closed',1);
        		break;
        	case 'refunding': // 退款中
        		$model = $this->M->where('closed',0)
        						->where('refund_status','applied');
        		break;
        }
        // 搜索条件
        if (array_key_exists('starttime',$param) && array_key_exists('endtime',$param)) {
        	$model = $model->whereTime('create_time', 'between', [$param['starttime'], $param['endtime']]);
        }
        
        $list = $model->with(['orderItems.goodsItem','user'])
		        ->order([ 'id'=>'desc' ])
				->select();
		
		$arr = [];
		$list->each(function($item) use(&$arr){
			// 联系方式
			$address = $item->address ="地址：".$item->address->province.$item->address->city.$item->address->district.$item->address->address." \n 姓名：".$item->address->name." \n 手机：".$item->address->phone;
			// 订单商品
			$order_items = '';
			foreach ($item->order_items as $val){
				$order_items .= '商品：'.$val['goods_item']['title']."\n ";
				$order_items .= '数量：'.$val['num']."\n ";
				$order_items .= '价格：'.$val['price']."\n\n ";
			}
			// 支付情况
			$pay = '未支付';
			switch ($item->payment_method) {
				case 'wechat':
					$pay = "支付方式：微信支付 \n 支付时间：".date('Y-m-d H:m:s',$item->paid_time);
					break;
				case 'wechat':
					$pay = "支付宝支付 \n 支付时间：".date('Y-m-d H:m:s',$item->paid_time);
					break;
			}
			// 发后状态
			$ship = '待发货';
			if ($item->ship_status && $item->ship_data) {
				$ship = "快递公司：".$item->ship_data->express_company." \n快递单号：".$item->ship_data->express_no." \n发货时间：".date('Y-m-d H:m:s',$item->ship_data->express_time);
			}
			
			$arr[] = [
				'id'=>$item->id,
				'no'=>$item->no,
				'address'=>$item->address,
				'order_items'=>$order_items,
				'pay'=>$pay,
				'ship'=>$ship,
				'create_time'=>$item->create_time
			];
		});
    	
    	// [名称, 字段名, 类型, 类型规则]
		$header = [
		    ['订单ID', 'id', 'text'],
		    ['订单号', 'no', 'text'],
		    ['收货地址', 'address'], // 规则不填默认text
		    ['商品', 'order_items'],
		    ['支付情况', 'pay'],
		    ['发货情况', 'ship'],
		    ['下单时间', 'create_time'],
		];
		
		// 简单使用
		return Excel::exportData($arr, $header);
		
		// 定制 默认导出xlsx 支持 : xlsx/xls/html/csv
		// return Excel::exportData($list, $header, '测试', 'xlsx');
    }
}
