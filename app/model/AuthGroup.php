<?php

namespace app\model;

use think\Model;

class AuthGroup extends Model
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
}
