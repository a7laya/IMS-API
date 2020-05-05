<?php

namespace app\validate;

class Skus extends BaseValidate
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
        'type'=>'require|in:0,1,2',
        'order'=>'integer|>=:0',
        'ids'=>'require|array'
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
        'save'=>['name','status','type','order'],
        'update'=>['id','name','status','type','order'],
        'delete'=>['id'],
        'updateStatus'=>['id','status'],
        'deleteAll'=>['ids']
    ];
}
