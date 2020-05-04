<?php
declare (strict_types = 1);

namespace app\controller\admin;

use think\Request;
// 继承基础控制器
use app\BaseController;
 
class Manager extends BaseController
{   
 

    // 当前方法对应的模型 $this->M,在BaseController中挂载 

    // 关闭自动实例化模型
    // protected $autoModel = false;

    // 重新定义模型路径
    // protected $modelPath = 'Manager';

    // 是否开启自动验证
    // protected $autoValidate = false;

    // 自定义验证场景 如果场景规则为'save1'
    // protected $autoValidateScenes = [
    //     'save' => 'save1'
    // ];
    
    // 需要自动验证的方法
    protected $excludeValidateCheck = ['index', 'save','update', 'updateStatus', 'delete', 'login'];


    /**
     * 显示管理员列表
     *
     * @return \think\Response
     * 
     * @desc 1.定义路由 2.定义validate(在controller配置$excludeValidateCheck) 
     * 
     */
    public function index()
    {   
        // 获取参数  getValByKey公共方法写在common.php里面
        $param = $this->request->param();
        $page = $param['page']; // 当前页面
        $limit = getValByKey('limit', $param, 10); // 每页限制条数,默认10条
        $keyword = getValByKey('keyword', $param, ''); // 关键字, 默认为''

        // 组织查询条件
        $where = [
            ['username', 'like', '%'.$keyword.'%']
        ];

        // 计算总数,主要用于分页
        $totalCount = $this->M->where($where)->count();

        // 获取列表数据
        $list = $this->M->page($page, $limit)
                        ->where($where)
                        ->with('role') // 关联到Manager模型中的 public function role()
                        ->order('id', 'desc')
                        ->select()
                        ->hidden(['password']);
        $role = \app\model\Role::field(['id', 'name'])->select();
        return showSuccess([
            'list' => $list,
            'totalCount' => $totalCount,
            'role' => $role
        ]);

    }
    

    /**
     * 创建管理员
     *
     * @param  \think\Request  $request
     * @return \think\Response
     * @desc 1.定义路由 2.定义validate(在controller配置$excludeValidateCheck) 3.$this->M->save($param)
     */
    public function save(Request $request)
    {
        // halt($request)
        
        // $param = $request->param();
        // 过滤参数
        $param = $request->only(['username','password','avatar','role_id','status']);

        $res = $this->M->save($param);
        return showSuccess($res);
    }


    /**
     * 更新管理员
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     * @desc 1.定义路由 2.定义validate(在controller配置$excludeValidateCheck) 3.$this->M->save($param)
     */
    public function update(Request $request, $id)
    {
        // 过滤参数
        $param = $request->only(['id','username','password','avatar','role_id','status']);        
        // 找到要修改的记录集 在validate的验证$id的时候 通过BaseValidate中的isExist方法挂载在request()->Model
        $res = $request->Model->save($param);
        return showSuccess($res);
    }

    /**
     * 修改管理员状态(启用|禁用)
     */
    public function updateStatus() {  
        // 找到要修改的记录集 在validate的验证$id的时候 挂载在request()->Model
        $manager = $this->request->Model;
        // 不能禁用自己
        if($this->request->UserModel->id === $manager->id){
            return showSuccess('不能禁用自己');
        }
        $manager->status = $this->request->param('status');
        return showSuccess($manager->save());
    }

    /**
     * 删除管理员
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {   
        /**
         * 验证器里面设置了该场景的id验证
         * 同时将该id所在的记录存入request->Model
         */ 
        $manager = $this->request->Model;

        // 不能删除自己
        if($this->request->UserModel->id == $manager->id){
            ApiException('不能删除自己');
        }

        // 不能删除超级管理员
        if($manager->super === 1){
            ApiException('不能删除超级管理员');
        }
        
        return showSuccess($manager->delete());
    }

     /**
     * 登录
     *
     * @param  string  $username
     * @param  string  $password
     * @return array $user
     */
    public function login(Request $request)
    {
        $user = cms_login([
            'data'=>$request->UserModel
        ]);
        return showSuccess($user);
    }

    /**
     * 登出
     */
    public function logout()
    {
        $res = cms_logout([
            'token' => $this->request->header('token')
        ]);

        return showSuccess($res);
    }


}
