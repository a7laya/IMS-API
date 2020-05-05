<?php

namespace app\controller\admin;

use app\controller\common\Base;
use think\Request;
class User extends Base
{
    // 不需要验证
    protected $excludeValidateCheck = ['logout'];
    // 定义自动实例化模型
    protected $ModelPath = 'common\\User';
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
        $user_level_id = getValByKey('user_level_id',$param,0);
        $method = $this->M->filterLoginMethod($keyword);
        $where = [
        	[ $method,'like','%'.$keyword.'%' ]
        ];
        
        if($user_level_id != 0){
        	$where[] = ['user_level_id','=',$user_level_id];
        }
        
        $totalCount = $this->M->where($where)->count();
        $list = $this->M->page($param['page'],$limit)
        		->where($where)
		        ->with(['userLevel'])
		        ->order([ 'id'=>'desc' ])
				->select()
				->hidden(['password']);
		$user_level = \app\model\common\UserLevel::field(['id','name'])->select();
        return showSuccess([
        	'list'=>$list,
        	'totalCount'=>$totalCount,
        	'user_level'=>$user_level
        ]);
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        
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
    	$this->M->checkUnique('username','用户名');
    	if(!array_key_exists('password',$param)){
    		return ApiException('密码不能为空');
    	}
    	$this->M->checkUnique('phone','手机');
    	$this->M->checkUnique('email','邮箱');
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
        //return showSuccess($this->M->Mread());
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

    // 修改状态
    public function updateStatus(){
        return showSuccess($this->M->_updateStatus());
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
    	$id = request()->param('id');
    	if($id == 3 || $id == 4){
    		return ApiException('演示数据，禁止删除');
    	}
        return showSuccess($this->M->Mdelete());
    }


    // 登录
    public function login(Request $request){
    	$user = cms_login([
            'data'=>$request->UserModel,
            'tag'=>'user'
        ]);
        return showSuccess($user);
    }


    // 退出
    public function logout(Request $request){
        return showSuccess(cms_logout([
            'tag'=>'user',
            'token'=>$request->header('token')
        ]));
    }
}
