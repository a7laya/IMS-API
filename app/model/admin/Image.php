<?php

namespace app\model\admin;

use app\model\common\BaseModel;
/**
 * @mixin think\Model
 */
class Image extends BaseModel
{
    // 图片属于哪个相册
    public function imageClass(){
        return $this->belongsTo('ImageClass');
    }

    // 默认相册0 图片列表
    public function Mlist(){
        $param = request()->param();
        $order = getValByKey('order',$param,'desc');
        return $this->where('image_class_id',0)->order([
	        		'id'=>$order
        		])->page($param['page'],10)->select();
    }

    // 增加（上传oss和记录数据） 
    public function Mcreate(){
        // 获取数据
        $file = request()->file('img');
        $classId = getValByKey('image_class_id',request()->param(),0);
        if($classId == 0) ApiException('先建一个相册');
        // 验证并上传图片
        $result = uploadImage($file);
        // 写入数据库 - oss
        if (!is_array($file)){  //单图上传
            $data = [
                'url'=>$result['url'],
                'name'=>'未命名',
                'path'=>$result['name'],
                'image_class_id'=>$classId
            ];
            return $this->create($data);
        }
        
        

        // 多图上传 - oss
        $data = [];
        foreach ($result as $v) {
            $data[] = [
                'url'=>$v['url'],
                'name'=>'未命名',
                'path'=>$v['name'],
                'image_class_id'=>$classId
            ];
        }
        return $this->saveAll($data);

        
    }
    // 修改（修改昵称）
    public function Mupdate(){
        return request()->Model->save([
            'name'=>request()->param('name')
        ]);
    }
    // 删除（删除oss和数据表数据）
    public function Mdelete(){
        $image = request()->Model;
        // 删除oss图片
        // (new \app\lib\file\Oss())->delete($image->path);
        // 删除本地图片
        $url = "./storage/".$image->path;
        if(file_exists($url)) unlink($url);
        // 删除数据
        return $image->delete();
    }

    public function MdeleteAll(){
        $param = request()->param('ids');
        // 找到所有数据
        $data = $this->where('id','in',$param)->select();
        // 删除所有数据
        $data->each(function($v){
            $url = "./storage/".$v->path;
            if(file_exists($url)) unlink($url);
            $v->delete();
        });
        // $Oss = new \app\lib\file\Oss();
        // $data->each(function($v) use($Oss){
        //     // 删除oss上的附件
        //     $Oss->delete($v->path);
        //     // 删除当前数据
        //     $v->delete();
        // });
        return true;
    }
    
}
