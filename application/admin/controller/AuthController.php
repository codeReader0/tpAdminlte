<?php
/**
 * Created by PhpStorm.
 * User: zilongs
 * Date: 18-7-10
 * Time: 上午11:42
 */

namespace app\admin\controller;

use app\common\controller\BaseController;
use think\facade\Session;

class AuthController extends BaseController
{
    public function initialize()
    {
        if (!Session::has('admin_user')){
            $this->redirect('admin/Common/login');
        }
    }
}