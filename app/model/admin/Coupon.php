<?php

namespace app\model\admin;

use app\model\common\BaseModel;

/**
 * @mixin think\Model
 */
class Coupon extends BaseModel
{
	protected $globalScope = ['orderId'];
	
	public function scopeOrderId($query)
    {
        $query->order('id','desc');
    }
	
    // 关联领取情况
    public function CouponUser(){
        return $this->hasMany('CouponUser');
    }

    // 列表
    public function Mlist(){
        $param = request()->param();
        return $this->page($param['page'],10)->select();
    }
    // 创建
    public function Mcreate(){
        return $this->create(request()->param());
    }
    // 修改
    public function Mupdate(){
        $param = request()->param();
        return request()->Model->save($param);
    }
    // 删除
    public function Mdelete(){
        return request()->Model->delete();
    }

}
