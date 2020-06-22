<?php
/*
 * @Author: your name
 * @Date: 2020-06-10 08:13:14
 * @LastEditTime: 2020-06-11 16:24:15
 * @LastEditors: Please set LastEditors
 * @Description: In User Settings Edit
 * @FilePath: /api.a7laya.com/app/validate/StorehouseIn.php
 */ 

namespace app\validate;

class StorehouseIn extends BaseValidate
{
  
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
        'id'=>'require|integer|>:0|isExist',
        'ids'=>'require|array',
        'goods_id'=>'require|integer',
        'storehouse_id'=>'require|integer',
        'stock'=>'require|integer',
        'operator'=>'require',
        'time'=>'require',
        // 'method'=>'in:GET,POST,PUT,DELETE',
        'spend'=>'require|integer',
        'sku_type'=>'require|in:0,1'
    ];
     
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [];

    protected $scene = [
        'save'=>['storehouse_id','goods_id','stock', 'operator', 'time', 'spend'],
        'update'=>['id','storehouse_id','name'],
        'delete'=>['id'],
        'deleteAll'=>['ids']
    ];

}
