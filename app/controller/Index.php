<?php
namespace app\controller;

use app\BaseController;

class Index extends BaseController
{   
    // 需要自动验证的方法
    // protected $excludeValidateCheck = ['index','hello'];

    // 关闭自动实例化模型
    protected $autoModel = false;


    public function index()
    {   
        // apiException('我是异常1');
        $list = [1,2];
        return showSuccess($list);
    }

    
    public function hello($name = 'ThinkPHP6')
    {
        return 'hello,' . $name;
    }
}
