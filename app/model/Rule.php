<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * @mixin think\Model
 */
class Rule extends Model
{
    // 权限-角色 多对多关系  中间表-role_rule 
    public function roles(){
        return $this->belongsToMany('Role', 'role_rule');
    }
}
