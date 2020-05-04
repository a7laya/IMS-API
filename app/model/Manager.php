<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * @mixin think\Model
 */
class Manager extends Model
{
    // 修改器
    public function setPasswordAttr($value, $data) {
        return password_hash($value, PASSWORD_DEFAULT);
    }

    // 用户属于哪个角色 反向一对多
    public function role()
    {
        return $this->belongsTo('Role');
        // 数据库设计的时候已经把关联的外键字段设计为role_id,所以可以省略掉第二个参数
        // return $this->belongsTo('Role', 'role_id');
    }
}
