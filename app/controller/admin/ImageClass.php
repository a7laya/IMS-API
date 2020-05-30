<?php

namespace app\controller\admin;

use think\Request;
use app\controller\common\Base;

class ImageClass extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        return showSuccess($this->M->Mlist());
    }

    /**
     * 根据图片关键字找到对应相册
     * @param keyword 图片名称的关键字
     * @param page 页码
     * @param limit 每页数量
     * @return \think\Response
     */
    public function find(Request $request)
    {   
        $param = $request->param();
        $model = $this->M;
        $limit = intval(getValByKey('limit',$param,10000));
        $keyword = getValByKey('keyword',$param,'');
        $model = $model->withCount([
            'images'=>function($q) use($keyword){
                $q->where([
                    ["name",'like','%'.$keyword.'%']
                ]);  
                // $q->where([
                //     ["images_count",'>',0]
                // ]);  
            }
        ]);
        
        $totalCount = $model->count();
        $list = $model->page($param['page'],$limit)->order([
            'order'=>'desc',
            'id'=>'desc'
        ])->select();

        return showSuccess([
        	'list'=>$list,
            'totalCount'=>$totalCount,
        ]);
    }

    // 根据相册显示图片
    public function images(){
        return showSuccess($this->M->MimageList());
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        return showSuccess($this->M->Mcreate());
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        return showSuccess($this->M->Mupdate());
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {   
        // 先判断改相册里面下是否有图片
        $count = \app\model\admin\Image::where('image_class_id', $id)->count();
        if($count>0) ApiException('相册里面还有图片，不能删除。');
        return showSuccess($this->M->Mdelete());
    }
}
