<?php
/*
 * @Author: your name
 * @Date: 2020-05-29 15:19:27
 * @LastEditTime: 2020-05-29 15:43:13
 * @LastEditors: Please set LastEditors
 * @Description: In User Settings Edit
 * @FilePath: /api.a7laya.com/app/validate/SysField.php
 */ 
namespace app\validate;
class SysField extends BaseValidate
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
        'name|字段名称'=>'require|unique:SysField',
        'value|字段值'=>'require',
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
        'save'=>['name','value'],
        'update'=>['id','name','value'],
        'delete'=>['id'],
        'deleteAll'=>['ids'],
    ];

}
