<?php
declare (strict_types = 1);

namespace app\validate\admin;

use app\validate\BaseValidate;

class Manager extends BaseValidate 
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
        'page' => 'require|integer|>:0', 
        'limit' => 'integer|>:0', 
        'id|管理员id' => 'require|integer|>:0|isExist:Manager', // isExist是在BaseValidate中的自定义规则
        'username|管理员用户名'   => 'require|min:5|max:20',
        'password'   => 'require|min:5|max:20',
        'avatar'     => 'url',
        'role_id'    => 'require|integer|>:0',
        'status'     => 'require|integer|in:0,1'
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
        // 'save' => ['username', 'password', 'avatar', 'role_id', 'status'],
        // 'update' => ['id', 'username', 'password', 'avatar', 'role_id', 'status'],
        'delete' => ['id'],
        'index' => ['page'],
        'updateStatus'=>['id','status']
    ];

    // 创建管理员的验证场景(保证名称唯一)
    public function sceneSave(){
        return $this->only(['username', 'password', 'avatar', 'role_id', 'status'])->append('username','unique:Manager');
    }

    // 更新管理员的验证场景(保证名称唯一)
    public function sceneUpdate(){
        $id = request()->param('id');
        return $this->only(['id', 'username', 'password', 'avatar', 'role_id', 'status'])->append('username','unique:Manager,username,'.$id);
    }
    
    // 登录场景
    public function sceneLogin(){
        return $this->only(['username', 'password'])->append('password','checklogin');
    }
 
    // 验证登录
    public function checklogin($value, $rule='', $data='', $field='', $title=''){
        // 验证账号是否存在
        $M = \app\model\Manager::where('username', $data['username'])->find();
        if(!$M) return '用户名不存在';

        // 验证密码
        if (!password_verify($data['password'],$M->password)) {
            return '密码错误';
        }

        // 如果通过，将当前用户实例挂载到request
        request()->UserModel = $M;
        return true;
    }
}
