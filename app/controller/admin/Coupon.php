<?php

namespace app\controller\admin;

use think\Request;
use app\controller\common\Base;
use app\model\admin\CouponUser;
use think\model\Relation;

class Coupon extends Base
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
        return showSuccess($this->M->Mcreate());
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
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

    // 领取优惠券
    public function getCoupon(Request $request){
        $user = $request->UserModel;
        $coupon = $request->Model;
        $data = [
            'user_id'=>$user->id,
            'coupon_id'=>$coupon->id
        ];
        $c = $coupon->CouponUser()->where($data)->find();
        // 已经领取过
        if ($c) {
            return ApiException('你已经领取过了');
        }
        // 创建记录
        return showSuccess($coupon->CouponUser()->save($data));
    }

    // 用户优惠券列表
    public function userCoupon(){
        $param = request()->param();
        $user = request()->UserModel;
    	// 未失效
    	$condition = $param['isvalid'] !== 'invalid';
    	// 查询
    	$list = CouponUser::hasWhere('coupon', function($query) use($condition){
    		$query->when($condition,function($query){
    			// 未失效
    			$query->whereBetweenTimeField('start_time', 'end_time')
				->where('status',1);
    		},function($query){
    			// 已失效
    			$query->whereOr('start_time', '>', time())
	    		->whereOr('end_time','<', time())
				->where('status',1);
    		});
		})
		->with(['coupon'])
		->where('user_id',$user->id)
		->page($param['page'],10)
		->order('create_time','desc')
		->select();
        return showSuccess($list);
    }
    
    // 优惠券列表
    public function getList(){
        $param = request()->param();
        $where = [];
        if (request()->UserModel) {
            $where['user_id'] = request()->UserModel->id;
        }
        $list = $this->M->where([
            'status'=>1,
        ])
        ->with([
            'CouponUser'=>function($query) use($where){
                $query->where($where);
            }
        ])
        ->order('create_time','desc')
        ->page($param['page'],10)
        ->select();
        return showSuccess($list);
    }
    
    // 当前订单可用优惠券数量
    public function couponCount(){
    	$param = request()->param();
    	$user = request()->UserModel;
    	// 查询
    	$count = CouponUser::hasWhere('coupon', function($query) use($param){
    		$query->whereBetweenTimeField('start_time', 'end_time')
				->where('status',1)
				->where('min_price','<=',$param['price']);
		})
		->with(['coupon'])
		->where('CouponUser.used',0)
		->where('user_id',$user->id)
		->count();
		return showSuccess($count);
    }

}
