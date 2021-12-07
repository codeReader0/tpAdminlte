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
use think\facade\Session;

class AuthController extends BaseController
{
    public $adminUser = null;

    public function initialize()
    {
        if (!Session::has('admin_user')){
            $this->redirect('/admin/Common/login');
        }
        else {
            $adminUser = $this->adminUser = Session::get('admin_user');
            $adminUser = AdminUser::field('id,status')->where('id', $adminUser['id'])->find();
            if (empty($adminUser)){
                $this->error('您的账号已经被删除', 'admin/Common/login');
            }
            if ($adminUser['status'] == 0){
                $this->error('您的账号已经被冻结', 'admin/Common/login');
            }

            if (config('app.is_open_auth')){
                $action = request()->action();
                if (strpos($action, 'show') === false){
                    if (!AdminUser::checkAuth($adminUser['id'])) {
                        $this->error('您没有权限访问');
                    }
                }
            }
        }
    }
}