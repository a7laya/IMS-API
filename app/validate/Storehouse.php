<?php

namespace app\validate;

class Storehouse extends BaseValidate
{
  
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
        'id'=>'require|integer|>:0|isExist',
        'storehouse_id'=>'require|integer|isExist:false|checkStorehouseId',
        'status'=>'require|in:0,1',
        'name'=>'require',
        'order'=>'require|integer',
        // 'method'=>'in:GET,POST,PUT,DELETE',
        'sortdata'=>'require'
    ];
     
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [];

    protected $scene = [
        'save'=>['storehouse_id','status','name'],
        'update'=>['id','storehouse_id','status','name'],
        'delete'=>['id'],
        'updateStatus'=>['id','status'],
        'sortStorehouse'=>['sortdata']
    ];

    // 不能将自己的id设置为父级id
    protected function checkStorehouseId($value, $rule='', $data=[], $field='', $title=''){
        if($value==0) return true;
        if(getValByKey('id',$data) == $value) return '不能将自身设为父级';
        return true;
    }
}
