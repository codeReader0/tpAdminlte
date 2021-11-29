<?php
/**
 * Created by PhpStorm.
 * User: zilongs
 * Date: 18-7-10
 * Time: 上午11:41
 */

namespace app\admin\controller;

use app\common\controller\BaseController;
use app\model\AdminUser;
use think\facade\Session;

class CommonController extends BaseController
{
    public function login()
    {
        if (request()->isPost()){
            $req = request()->post();

            $adminUser = AdminUser::where('account', $req['account'])->find();
            if (empty($adminUser)){
                $this->error('账号不存在');
            }
            if (md5(sha1($req['password'])) != $adminUser['password']){
                $this->error('密码错误');
            }
            if($adminUser['status'] == 0){
                $this->error('账号已被禁用');
            }

            Session::set('admin_user', $adminUser);

            $this->redirect('admin/Home/index');
        }

        return $this->fetch();
    }

    public function logout()
    {
        Session::clear();
        $this->redirect('admin/Common/login');
    }
}
