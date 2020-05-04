<?php
declare (strict_types = 1);

namespace app\validate;

use think\Validate;

class BaseValidate extends Validate
{   
    /**
     * 自定义验证规则 - 验证记录是否存在
     * @var array
     */	
    protected function isExist($value, $rule='', $data='', $field='', $title='记录'){
        // halt($value, $rule, $data, $field, $title);
        // $value： 传入的值
        // $rule： ‘isExist:’ 后面的值 ‘Manager’
        // $$data: 传入的全部数据 数组 包含验证的字段
        // $field: 验证的字段‘id’
        // $title: 别名‘管理员id’
        if(!$value){ //
            return true;
        }
        $model = '\app\model\\'.$rule;
        
        // 找到要修改的记录集
        $m = $model::find($value);
        
        if(!$m){
            return '该'.$title.'不存在';
        }
        // 将找到的$m挂载到request 可以在控制器中$request->Model进行调用
        request()->Model = $m;

        return true;
    }

    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [];
}
