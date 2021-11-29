<?php

namespace app\admin\controller;

use app\model\AuthGroup;
use app\model\AuthGroupAccess;
use app\model\AuthRule;

class AuthGroupController extends AuthController
{
    public function authGroupList()
    {
        $req = request()->param();

        $builder = AuthGroup::order('id', 'desc');
        if (isset($req['auth_group_id']) && $req['auth_group_id'] !== ''){
            $builder->where('id', $req['auth_group_id']);
        }

        $data = $builder->paginate(['query' => $req]);

        $this->assign('req', $req);
        $this->assign('data', $data);

        return $this->fetch();
    }

    public function showAuthGroup()
    {
        $req = request()->get();
        $data = [];
        if (!empty($req['id'])){
            $data = AuthGroup::where('id', $req['id'])->find();
        }
        $this->assign('data', $data);

        return $this->fetch();
    }

    public function addAuthGroup()
    {
        $req = request()->post();
        $this->validate($req, [
            'title|角色名称' => 'require|max:80|unique:auth_group',
            'desc|角色描述' => 'max:150',
        ]);

        AuthGroup::create($req);

        return out();
    }

    public function editAuthGroup()
    {
        $req = request()->post();
        $this->validate($req, [
            'id' => 'require|number',
            'title|角色名称' => 'require|max:80|unique:auth_group',
            'desc|角色描述' => 'max:150',
        ]);

        AuthGroup::where('id', $req['id'])->update($req);

        return out();
    }

    public function changeAuthGroup()
    {
        $req = request()->post();

        AuthGroup::where('id', $req['id'])->update([$req['field'] => $req['value']]);

        return out();
    }

    public function delAuthGroup()
    {
        $req = request()->post();
        $this->validate($req, [
            'id' => 'require|number'
        ]);

        $admin_user_id_arr = AuthGroupAccess::where('auth_group_id', $req['id'])->column('admin_user_id');
        if (!empty($admin_user_id_arr)){
            return out(null, 10001, '此角色已有用户，不能删除');
        }
        AuthGroup::where('id', $req['id'])->delete();

        return out();
    }

    public function showAssignAuth()
    {
        $req = request()->param();
        $this->validate($req, [
            'auth_group_id' => 'require|number'
        ]);

        $ruleNodes = AuthRule::where('status', 2)->select()->toArray();
        $ruleNodes = array_column($ruleNodes, 'title', 'type');

        $authRule = AuthRule::where('status', 1)->select()->toArray();
        $authRule = array_column($authRule, null, 'title');
        $authGroup = AuthGroup::find($req['auth_group_id']);
        $rule_array = json_decode($authGroup['rules'], true);

        $node = array();

        if (!empty($rule_array)) {
            foreach ($ruleNodes as $k => $v) {
                $node[$k]['text'] = $v;
                $node[$k]['selectable'] = false;
                $node[$k]['state']['checked'] = $node[$k]['state']['expanded'] = false;

                $no = array();
                $i = 0;
                foreach ($authRule as $k1 => $v1) {
                    if ($k == $authRule[$k1]['type']) {
                        $no[$i]['text'] = $k1;
                        $no[$i]['selectable'] = false;
                        $no[$i]['authRuleName'] = $authRule[$k1]['name'];
                        $no[$i]['state']['checked'] = in_array($authRule[$k1]['name'], $rule_array) ? true : false;
                        if ($no[$i]['state']['checked']) {
                            $node[$k]['state']['expanded'] = true;
                            $node[$k]['state']['checked'] = true;
                        }
                        $i++;
                    }
                }

                $node[$k]['nodes'] = $no;
            }
        }
        else {
            foreach ($ruleNodes as $k => $v) {
                $node[$k]['text'] = $v;
                $node[$k]['selectable'] = false;
                $node[$k]['state']['expanded'] = false;

                $no = array();
                $i = 0;
                foreach ($authRule as $k1 => $v1) {
                    if ($k == $authRule[$k1]['type']) {
                        $no[$i]['text'] = $k1;
                        $no[$i]['selectable'] = false;
                        $no[$i]['authRuleName'] = $authRule[$k1]['name'];
                        $i++;
                    }
                }

                $node[$k]['nodes'] = $no;
            }
        }

        $this->assign('nodeJson', json_encode(array_values($node), JSON_UNESCAPED_UNICODE));
        $this->assign('auth_group_id', $req['auth_group_id']);

        return $this->fetch();
    }

    public function submitAssignAuth()
    {
        $req = request()->post();
        $this->validate($req, [
            'auth_group_id|角色id' => 'require|number',
            'rules|权限' => 'require|array'
        ]);

        $rules = array_unique($req['rules']);
        $rules = json_encode($rules);

        AuthGroup::where('id', $req['auth_group_id'])->update(['rules' => $rules]);

        return out();
    }
}
