<?php

namespace app\model;

use think\Model;

class AdminUser extends Model
{
    public function getCreateDateAttr($value, $data)
    {
        return date('Y-m-d H:i', $data['create_time']);
    }

    public function getStatusTextAttr($value, $data)
    {
        $map = [0 => '禁用',1 => '正常',];
        return $map[$data['status']];
    }

    public function authGroup()
    {
        return $this->belongsToMany('authGroup', 'auth_group_access', 'auth_group_id', 'admin_user_id');
    }
}
