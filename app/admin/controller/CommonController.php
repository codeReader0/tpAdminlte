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
use think\facade\Db;
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

            return redirect(url('admin/Home/index'));
        }

        return $this->fetch();
    }

    public function logout()
    {
        Session::clear();
        return redirect(url('admin/Common/login'));
    }

    public function doc()
    {
        $database = env('database.database');
        $prefix = env('database.prefix');
        $exclude_tables = "'".$prefix."admin_handle_log','".$prefix."auth_group','".$prefix."auth_group_access','".$prefix."auth_rule','".$prefix."admin_user'";

        $sql = "select TABLE_NAME name,TABLE_COMMENT comment from INFORMATION_SCHEMA.TABLES where TABLE_SCHEMA='".$database."' and TABLE_NAME not in (".$exclude_tables.")";
        $tables = Db::query($sql);
        $map1 = $map2 = [];
        $i = round(count($tables)/2);
        foreach ($tables as $k => $v) {
            $name = str_replace($prefix, '', $v['name']);
            if ($k >= $i) {
                $map1[$v['name']] = $name.'('.$v['comment'].')';
            }
            else {
                $map2[$v['name']] = $name.'('.$v['comment'].')';
            }
        }

        $data1 = [];
        foreach ($map1 as $k => $v){
            $sql = "select COLUMN_NAME name, DATA_TYPE type, COLUMN_COMMENT comment from INFORMATION_SCHEMA.COLUMNS where table_schema = '".$database."' AND table_name = '".$k."'";
            $comment = Db::query($sql);
            $data1[$v] = $comment;
        }

        $data2 = [];
        foreach ($map2 as $k => $v){
            $sql = "select COLUMN_NAME name, DATA_TYPE type, COLUMN_COMMENT comment from INFORMATION_SCHEMA.COLUMNS where table_schema = '".$database."' AND table_name = '".$k."'";
            $comment = Db::query($sql);
            $data2[$v] = $comment;
        }

        $this->assign('data1', $data1);
        $this->assign('data2', $data2);

        return $this->fetch();
    }
}
