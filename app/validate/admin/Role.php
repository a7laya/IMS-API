<?php
declare (strict_types = 1);

namespace app\validate\admin; 

use app\validate\BaseValidate;

class Role extends BaseValidate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
    protected $rule = [
        'id|角色id' => 'require|integer|>:0|isExist:Role', // isExist是在BaseValidate中的自定义规则
        'page' => 'require|integer|>:0',
        'status' => 'require|integer|in:0,1',
        'name|角色名称' => 'require',
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [];

    /**
     * 定义验证场景
     * 格式：'场景名'	=>	['需验证的字段']
     * 验证场景未指定则按全部规则进行验证
     * @var array
     */	
    protected $scene = [
        'index' => ['page'],
        // 'save' => ['name', 'status'],
        // 'update' => ['id', 'name', 'status']
        'updateStatus' => ['id','status'],
        'delete' => ['id'],
        'setRules' => ['id']
    ];

    // 创建角色的验证场景(保证名称唯一)
    public function sceneSave(){
        return $this->only(['name', 'status'])->append('name','unique:Role');
    }

    // 更新管理员的验证场景(保证名称唯一)
    public function sceneUpdate(){
        $id = request()->param('id');
        return $this->only(['id','name', 'status'])->append('name','unique:Role,name,'.$id);
    }
}
