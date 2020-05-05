<?php

namespace app\controller\admin;

use think\Request;
// 引入基类控制器
use app\controller\common\Base;

class Manager extends Base
{

    // 不需要验证
    protected $excludeValidateCheck = ['logout'];
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $param = request()->param();
        $limit = intval(getValByKey('limit',$param,10));
        $keyword = getValByKey('keyword',$param,'');
        $where = [
        	[ 'username','like','%'.$keyword.'%' ]
        ];
        
        $totalCount = $this->M->where($where)->count();
        $list = $this->M->page($param['page'],$limit)
        		->where($where)
        		->with('role')
		        ->order([ 'id'=>'desc' ])
				->select()
				->hidden(['password']);
		$role = \app\model\admin\Role::field(['id','name'])->select();
        return showSuccess([
        	'list'=>$list,
        	'totalCount'=>$totalCount,
        	'role'=>$role
        ]);
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save()
    {
    	$param = request()->param();
    	if (!array_key_exists('password',$param) || $param['password'] == '') {
    		ApiException('密码不能为空');
    	}
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
        
        $user = request()->Model->append(['role.rules'])->toArray();
        return showSuccess($user);
    }

    // 设置给用户设置权限
    public function setRole(){
        $roleId = request()->param('role_id');
        $user = request()->Model;
        return showSuccess($this->M->setRole($user,$roleId));
    }

    // 用户是否有某个权限
    public function hasRule(){
        $user = $request->UserModel;
        $rule_id = request()->param('rule_id');
        return showSuccess($this->M->hasRule($user,$rule_id));
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
    	$param = request()->param();
    	// 超级管理员和演示数据禁止操作
    	if ($request->Model->super || $request->Model->id == 9) {
    		ApiException('演示数据，禁止操作');
    	}
    	if (array_key_exists('password',$param) && $param['password'] == '') {
    		unset($param['password']);
    	}
        $res = request()->Model->save($param);
        return showSuccess($res);
    }

    // 修改状态
    public function updateStatus(Request $request)
    {
    	if ($request->Model->super) {
    		ApiException('超级管理员禁止操作');
    	}
    	if ($request->Model->id == 9) {
    		ApiException('演示数据，禁止操作');
    	}
        return showSuccess($this->M->_UpdateStatus());
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
    	// 超级管理员不能删除
    	$manager = request()->Model;
    	if($manager->super || $manager->id == 3){
    		ApiException('超级管理员不能删除');
    	}
    	if ($manager->id == 9) {
    		ApiException('演示数据，禁止操作');
    	}
        return showSuccess($this->M->Mdelete());
    }


    // 管理员登录
    public function login(Request $request){
        $user = cms_login([
            'data'=>$request->UserModel
        ]);
        // 获取当前用户所有权限
        $data = $this->M->where('id',$user['id'])->with([
        	'role'=>function($query){
        		$query->with([
        			'rules'=>function($q){
        				$q->order('order','desc')
        				->order('id','asc')
        				->where('status',1);
        			}
        		]);
        	}
        ])->find()->toArray();
        $data['token'] = $user['token'];
        
        $data['tree'] = [];
        // 规则名称，按钮级别显示
        $data['ruleNames'] = [];
        // 无限级分类
        $rules = $data['role']['rules'];
        // 超级管理员
        if($data['super'] === 1){
        	$rules = \app\model\admin\Rule::where('status',1)->select()->toArray();
        }
        $data['tree'] = list_to_tree2($rules,'rule_id','child',0,function($item){
        	return $item['menu'] === 1;
        });
        // 权限规则数组
    	foreach ($data['role']['rules'] as $v) {
    		if($v['condition'] && $v['name']){
    			$data['ruleNames'][] = $v['name'];
    		}
    	}
        return showSuccess($data);
    }


    // 管理员退出
    public function logout(Request $request){
        return showSuccess(cms_logout([
            'token'=>$request->header('token')
        ]));
    }

}
