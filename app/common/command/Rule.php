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
use think\Db;

class Rule extends Command
{
    protected function configure()
    {
        $this->setName('rule')->setDescription('生成后台权限命令 命令：php think rule');
    }

    protected function execute(Input $input, Output $output)
    {
        Db::execute('TRUNCATE '.config('database.prefix').'auth_rule');

        $rule = config('rule.');
        $i = 1;
        $list = [];
        foreach ($rule as $k => $v) {
            $arr = explode('/', $v[0]['name']);
            $list[] = [
                'name' => strtolower($arr[0]),
                'title' => $k,
                'status' => 2,
                'type' => $i,
            ];
            foreach ($v as $k1 => $v1) {
                $list[] = [
                    'name' => strtolower($v1['name']),
                    'title' => $v1['title'],
                    'status' => 1,
                    'type' => $i,
                ];
            }

            $i++;
        }

        if (!AdminUser::where('id', 1)->count()) {
            AdminUser::create([
                'account' => '111111',
                'password' => md5(sha1('111111')),
                'nickname' => '超级管理员',
            ]);
        }

        $authRule = new AuthRule();
        $authRule->saveAll($list);

        $authRuleArray = AuthRule::where('status', 1)->column('name');
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
