<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * @mixin think\Model
 */
class Role extends Model{
    
    // 角色-管理员 一对多关系
    public function managers(){
        return $this->hasMany('Manager');
    }

    // 角色-权限 多对多关系  中间表-role_rule 
    public function rules(){
        // belongsToMany(模型,中间表)
        return $this->belongsToMany('Rule','role_rule');
    }

    // 
    public function setRules($ruleIds){

    }
}
