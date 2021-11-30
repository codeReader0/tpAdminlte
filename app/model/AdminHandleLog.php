<?php

namespace app\model;

use think\Model;

class AdminHandleLog extends Model
{
    public function adminUser()
    {
        return $this->belongsTo('AdminUser')->field('id,nickname');
    }

    public function authRule()
    {
        return $this->belongsTo('AuthRule')->field('id,title');
    }
}
