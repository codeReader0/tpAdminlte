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

        $builder = AdminHandleLog::with('adminUser,authRule')->order('id', 'desc');
        if (isset($req['admin_user_id']) && $req['admin_user_id'] !== ''){
            $builder->where('admin_user_id', $req['admin_user_id']);
        }
        if (isset($req['auth_rule_id']) && $req['auth_rule_id'] !== ''){
            $builder->where('auth_rule_id', $req['auth_rule_id']);
        }
        if (isset($req['start_date']) && $req['start_date'] !== ''){
            $builder->where('create_time', '>', strtotime($req['start_date']));
        }
        if (isset($req['end_date']) && $req['end_date'] !== ''){
            $builder->where('create_time', '<', strtotime($req['end_date']));
        }
        if (isset($req['request_body']) && $req['request_body'] !== ''){
            $builder->whereLike('request_body', '%'.$req['request_body'].'%');
        }
        if (isset($req['response_body']) && $req['response_body'] !== ''){
            $builder->whereLike('response_body', '%'.$req['response_body'].'%');
        }

        $data = $builder->paginate(['query' => $req]);

        $this->assign('data', $data);

        $adminUsers = AdminUser::select();
        $this->assign('adminUsers', $adminUsers);

        $authRules = AuthRule::where('status', 1)->select();
        $this->assign('authRules', $authRules);

        $this->assign('req', $req);

        return $this->fetch();
    }
}
