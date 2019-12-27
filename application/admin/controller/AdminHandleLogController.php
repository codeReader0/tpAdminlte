<?php

namespace app\admin\controller;

use app\model\AdminHandleLog;
use app\model\AdminUser;
use app\model\AuthRule;

class AdminHandleLogController extends AuthController
{
    public function adminHandleLogList()
    {
        $req = request()->param();

        $bulider = AdminHandleLog::with('adminUser,authRule')->order('id', 'desc');
        if (isset($req['admin_user_id']) && $req['admin_user_id'] !== ''){
            $bulider->where('admin_user_id', $req['admin_user_id']);
        }
        if (isset($req['auth_rule_id']) && $req['auth_rule_id'] !== ''){
            $bulider->where('auth_rule_id', $req['auth_rule_id']);
        }
        if (isset($req['start_date']) && $req['start_date'] !== ''){
            $bulider->where('create_time', '>', strtotime($req['start_date']));
        }
        if (isset($req['end_date']) && $req['end_date'] !== ''){
            $bulider->where('create_time', '<', strtotime($req['end_date']));
        }
        if (isset($req['request_body']) && $req['request_body'] !== ''){
            $bulider->whereLike('request_body', '%'.$req['request_body'].'%');
        }
        if (isset($req['response_body']) && $req['response_body'] !== ''){
            $bulider->whereLike('response_body', '%'.$req['response_body'].'%');
        }

        $data = $bulider->paginate(['query' => $req]);

        $this->assign('data', $data);

        $adminUsers = AdminUser::all();
        $this->assign('adminUsers', $adminUsers);

        $authRules = AuthRule::all(['status' => 1]);
        $this->assign('authRules', $authRules);

        $this->assign('req', $req);

        return $this->fetch();
    }
}
