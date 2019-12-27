<?php
/**
 * Created by PhpStorm.
 * User: zilongs
 * Date: 18-7-10
 * Time: 上午11:42
 */

namespace app\admin\controller;

use app\common\controller\BaseController;
use app\model\AdminUser;
use auth\Auth;
use think\facade\Session;

class AuthController extends BaseController
{
    public function initialize()
    {
        if (!Session::has('admin_user')){
            $this->redirect('admin/Common/login');
        }
        else {
            $adminUser = Session::get('admin_user');
            $adminUser = AdminUser::field('status')->where('id', $adminUser['id'])->find();
            if (empty($adminUser)){
                $this->error('您的账号已经被删除', 'admin/Common/login');
            }
            if ($adminUser['status'] == 0){
                $this->error('您的账号已经被冻结', 'admin/Common/login');
            }
            $controller = request()->controller();
            $action = request()->action();
            $path = $controller . '/' . $action;
            $path_common_map = config('path_common_map');

            if (strpos($action, 'show') === false){
                $auth = new Auth();
                if(!in_array($path, $path_common_map) && !$auth->check($path, Session::get('admin_user')->id)){
                    $this->error('您没有权限访问');
                }
            }
        }
    }
}