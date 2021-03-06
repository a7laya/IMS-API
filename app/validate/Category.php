<?php

namespace app\validate;

class Category extends BaseValidate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
        'id'=>'require|integer|>:0|isExist',
        'page'=>'require|integer|>:0',
        'status'=>'require|in:0,1',
        'name'=>'require',
        'category_id'=>'require|integer|>=:0|isExist:false|checkId',
        'order'=>'integer',
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
        'save'=>['name','status','category_id','order'],
        'update'=>['id','name','status','category_id','order'],
        'delete'=>['id'],
        'updateStatus'=>['id','status'],
        'sortCategory'=>['sortdata']
    ];

    // 不能将自己的id设置为父级id
    protected function checkId($value, $rule='', $data=[], $field='', $title=''){
        if($value==0) return true;
        if(getValByKey('id',$data) == $value) return '不能将自身设为父级';
        return true;
    }
}
