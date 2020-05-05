<?php

namespace app\controller\web;

use app\controller\common\Base;
class AppIndexCategory extends Base
{
    // 不需要验证
    protected $excludeValidateCheck = ['index'];
    protected $ModelPath = 'admin\AppIndexCategory';
    
    // 获取分类和首页数据
    public function index(){
        $category = $this->M->select();
        $data =  count($category) > 0 ? $category[0]->indexData()->limit(5)->select() : [];
        return showSuccess([
            'category'=>$category,
            'data'=>$data
        ]);
    }
    // 获取分类下的数据
    public function read()
    {
        $param = request()->param();
        $data = request()->Model->indexData()->page($param['page'],5)->select();
        return showSuccess($data);
    }
}
