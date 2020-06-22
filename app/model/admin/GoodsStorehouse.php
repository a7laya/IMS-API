<?php
/*
 * @Author: your name
 * @Date: 2020-06-17 10:35:35
 * @LastEditTime: 2020-06-17 10:35:36
 * @LastEditors: your name
 * @Description: In User Settings Edit
 * @FilePath: /api.a7laya.com/app/model/admin/GoodsStorehouse.php
 */ 

namespace app\model\admin;

use think\model\Pivot;

use think\model\concern\SoftDelete;

class GoodsStorehouse extends Pivot
{
    //

    use SoftDelete;
    protected $deleteTime = 'delete_time';
}