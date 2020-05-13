<?php

namespace app\model\admin;

use app\model\common\BaseModel;
use app\model\admin\RoleRule;
use app\model\admin\Rule;
/**
 * @mixin think\Model
 */
class Role extends BaseModel
{
    // 当前角色的所有权限
    public function rules(){
        return $this->belongsToMany('Rule','role_rule');
    }

    // 给角色授予权限
    public function setRules($ruleId){
        // // 获取当前角色的所有权限Id
        // $Ids = RoleRule::where('role_id',$this->id)->field('rule_id')->select();
        // // 判断当前权限是否已经授予
        // $arr = [];
        // // 数组
        // if (is_array($ruleId)) {
        //     foreach ($ruleId as $value) {
        //         // 还未授予，并且存在
        //         $rule = Rule::find($value);
        //         if ($Ids->where('rule_id',$value)->isEmpty() && $rule){
        //         	$arr[] = $value;
        //         } 
        //     }
        // }else{
        // 	$rule = Rule::find($ruleId);
        //     if ($Ids->where('rule_id',$ruleId)->isEmpty() && $rule) $arr[] = $ruleId;
        // }
        // return count($arr)>0 ? $this->rules()->attach($arr) : true;
        
        // 获取当前角色的所有权限Id
        $Ids = RoleRule::where('role_id',$this->id)->column('rule_id');
        // 需要添加的
        $addIds = array_diff($ruleId,$Ids);
        // 需要删除的
        $delIds = array_diff($Ids,$ruleId);
        
        if (count($addIds)>0) {
        	$RoleRule = new RoleRule();
        	$addData = [];
        	foreach ($addIds as $value) {
        		$addData[] = [
        			'rule_id'=>$value,
        			'role_id'=>$this->id
        		];
        	}
        	$RoleRule->saveAll($addData);
        	// $this->rules()->attach($addIds);
        }
        
        if (count($delIds) > 0) {
        	RoleRule::where([
        		['rule_id','in',$delIds],
        		['role_id','=',$this->id]
        	])->delete();
        	// $this->rules()->detach($delIds);
        }
        
        return true;
    }

    // 取消角色权限
    public function delRules($ruleId){
        return $this->rules()->detach($ruleId);
    }


    // 角色-管理员 一对多关系
    public function managers(){
        return $this->hasMany('Manager');
    }


    /**
     * 删除之前操作
     * 1. 清除当前角色的所有权限
     * 
     * @param [type] $role
     * @return void
     */
    public static function onBeforeDelete($role){
        // 清除当前角色的所有权限
        $ruleIds = $role->rules->map(function($v){
            return $v->id;
        })->toArray();
        if (count($ruleIds)) $role->delRules($ruleIds);
    } 
}
