<?php
/**
 * Created by PhpStorm.
 * User: zilongs
 * Date: 19-8-14
 * Time: 下午5:31
 */

namespace app\common\command;

use app\model\AdminUser;
use app\model\AuthGroup;
use app\model\AuthGroupAccess;
use app\model\AuthRule;
use think\console\Command;
use think\console\Input;
use think\console\Output;

class Rule extends Command
{
    protected function configure()
    {
        $this->setName('rule')->setDescription('生成后台权限命令 命令：php think rule');
    }

    protected function execute(Input $input, Output $output)
    {
        $list = [
            [
                'name' => 'AdminUser',
                'title' => '后台账号及角色管理',
                'status' => 2,
                'type' => 1,
            ],
            [
                'name' => 'AdminUser/adminUserList',
                'title' => '查看后台账号列表',
                'type' => '1',
            ],
            [
                'name' => 'AdminUser/addAdminUser',
                'title' => '添加后台用户',
                'type' => '1',
            ],
            [
                'name' => 'AdminUser/editAdminUser',
                'title' => '编辑后台用户',
                'type' => '1',
            ],
            [
                'name' => 'AdminUser/delAdminUser',
                'title' => '删除后台用户',
                'type' => '1',
            ],
            [
                'name' => 'AdminUser/changeAdminUser',
                'title' => '改变后台用户状态',
                'type' => '1',
            ],
            [
                'name' => 'AuthGroup/authGroupList',
                'title' => '查看角色权限管理列表',
                'type' => '1',
            ],
            [
                'name' => 'AuthGroup/addAuthGroup',
                'title' => '添加后台角色',
                'type' => '1',
            ],
            [
                'name' => 'AuthGroup/editAuthGroup',
                'title' => '编辑后台角色',
                'type' => '1',
            ],
            [
                'name' => 'AuthGroup/delAuthGroup',
                'title' => '删除后台角色',
                'type' => '1',
            ],
            [
                'name' => 'AuthGroup/submitAssignAuth',
                'title' => '分配角色权限',
                'type' => '1',
            ],
        ];

        db()->execute('TRUNCATE '.config('database.prefix').'auth_rule');

        if (!AdminUser::where('id', 1)->count()) {
            AdminUser::create([
                'account' => '111111',
                'password' => md5(sha1('111111')),
                'nickname' => '超级管理员',
            ]);
        }

        $authRule = new AuthRule();
        $authRule->saveAll($list);

        $authRuleArray = AuthRule::where('status', 1)->column('id');
        $rules = json_encode($authRuleArray);

        if($authGroup = AuthGroup::get(1)) {
            $authGroup->rules = $rules;
            $authGroup->save();
        }
        else {
            $authGroup = new AuthGroup(['title' => '超级管理员', 'desc' => '超级管理员', 'rules' => $rules]);
            $authGroup->save();

            $authGroupAccess = new AuthGroupAccess(['admin_user_id' => 1, 'auth_group_id' => 1]);
            $authGroupAccess->save();
        }
    }
}
