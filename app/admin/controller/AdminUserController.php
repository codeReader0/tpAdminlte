<?php

namespace app\admin\controller;

use app\model\AdminUser;
use app\model\AuthGroup;
use app\model\AuthGroupAccess;
use think\facade\Session;

class AdminUserController extends AuthController
{
    public function adminUserList()
    {
        $req = request()->param();

        $builder = AdminUser::order('id', 'desc');
        if (isset($req['admin_user_id']) && $req['admin_user_id'] !== ''){
            $builder->where('id', $req['admin_user_id']);
        }
        if (isset($req['account']) && $req['account'] !== ''){
            $builder->where('account', $req['account']);
        }
        if (isset($req['status']) && $req['status'] !== ''){
            $builder->where('status', $req['status']);
        }

        $data = $builder->paginate(['query' => $req]);

        $this->assign('req', $req);
        $this->assign('data', $data);

        return $this->fetch();
    }

    public function showAdminUser()
    {
        $req = request()->get();
        $data = [];
        if (!empty($req['id'])){
            $data = AdminUser::where('id', $req['id'])->find();
            $auth_group_id = AuthGroupAccess::where('admin_user_id', $req['id'])->value('auth_group_id');
            $data['auth_group_id'] = $auth_group_id;
        }
        $this->assign('data', $data);

        $authGroup = AuthGroup::select();
        $this->assign('authGroup', $authGroup);

        return $this->fetch();
    }

    public function addAdminUser()
    {
        $req = request()->post();
        $this->validate($req, [
            'account|账号' => 'require|max:50|unique:admin_user',
            'password|密码' => 'require|max:100',
            'nickname|昵称' => 'require|max:50',
            'auth_group_id|角色' => 'require|number',
        ]);

        $req['password'] = md5(sha1($req['password']));
        $adminUser = AdminUser::create($req);

        AuthGroupAccess::create(['admin_user_id' => $adminUser['id'], 'auth_group_id' => $req['auth_group_id']]);

        return out();
    }

    public function editAdminUser()
    {
        $req = request()->post();
        $this->validate($req, [
            'id' => 'require|number',
            'account|账号' => 'require|max:50|unique:admin_user',
            'password|密码' => 'max:100',
            'nickname|昵称' => 'require|max:50',
            'auth_group_id|角色' => 'require|number',
        ]);

        if (!empty($req['password'])){
            $req['password'] = md5(sha1($req['password']));
        }
        else {
            unset($req['password']);
        }

        AdminUser::where('id', $req['id'])->update($req);

        AuthGroupAccess::where('admin_user_id', $req['id'])->delete();
        AuthGroupAccess::create(['admin_user_id' => $req['id'], 'auth_group_id' => $req['auth_group_id']]);

        return out();
    }

    public function changeAdminUser()
    {
        $req = request()->post();

        AdminUser::where('id', $req['id'])->update([$req['field'] => $req['value']]);

        return out();
    }

    public function delAdminUser()
    {
        $req = request()->post();
        $this->validate($req, [
            'id' => 'require|number'
        ]);

        AdminUser::where('id', $req['id'])->delete();

        return out();
    }

    public function showUpdatePassword()
    {
        return $this->fetch();
    }

    public function updatePassword()
    {
        $req = request()->post();
        $this->validate($req, [
            'password|新密码' => 'require|confirm'
        ]);

        $admin_user = Session::get('admin_user');
        $password = md5(sha1($req['password']));
        AdminUser::where('id', $admin_user['id'])->update(['password' => $password]);

        Session::clear();

        return out();
    }
}
