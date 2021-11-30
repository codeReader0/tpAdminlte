<?php

namespace app\model;

use think\Model;

class AdminUser extends Model
{
    public function getStatusTextAttr($value, $data)
    {
        $map = [0 => '禁用',1 => '正常',];
        return $map[$data['status']];
    }

    public function authGroup()
    {
        return $this->belongsToMany('authGroup', 'auth_group_access', 'auth_group_id', 'admin_user_id');
    }

    public static function checkAuth($admin_user_id, $path = '')
    {
        $adminUser = AdminUser::with('authGroup')->field('id')->where('id', $admin_user_id)->find();
        if (empty($path)) {
            $controller = request()->controller();
            $action = request()->action();
            $path = $controller . '/' . $action;
        }
        $path = strtolower($path);
        $path_common_map = config('app.path_common_map');
        $path_common_map = array_map('strtolower', $path_common_map);

        $rules = $path_common_map;
        foreach ($adminUser['auth_group'] as $k => $v) {
            $tmp = json_decode($v['rules'], true);
            $rules = array_merge($rules, $tmp);
        }
        $rules = array_values(array_unique($rules));

        if (in_array($path, $rules)) {
            return true;
        }

        return false;
    }
}
