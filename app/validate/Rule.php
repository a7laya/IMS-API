<?php

namespace app\validate;

class Rule extends BaseValidate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
        'page' => 'require|integer|>:0',
        'id'=>'require|integer|>:0|isExist',
        'rule_id'=>'require|integer|isExist:false|checkRuleId',
        'status'=>'require|in:0,1',
        'name'=>'require',
        'condition'=>'require',
        'menu'=>'require|in:0,1',
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
        'index'=>['page'],
        'save'=>['rule_id','status','name','menu','order'],
        'update'=>['id','rule_id','status','name','menu','order'],
        'delete'=>['id'],
        'updateStatus'=>['id','status'],
        'sortRule'=>['sortdata']
    ];

    // 不能将自己的id设置为父级id
    protected function checkRuleId($value, $rule='', $data=[], $field='', $title=''){
        if(getValByKey('id',$data)== $value) return '不能将自身设为父级';
        return true;
    }
}
