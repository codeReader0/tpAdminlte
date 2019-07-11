<?php

return [
    '用户管理' => [
        'icon' => 'fa-user',
        'url' => 'admin/User/userList',
    ],
    '后台账号及角色管理' => [
        'icon' => 'fa-users',
        'url' => [
            '账号管理' => [
                'icon' => 'fa-circle-o',
                'url' => 'admin/AdminUser/adminUserList',
            ],
            '角色管理' => [
                'icon' => 'fa-circle-o',
                'url' => 'admin/AuthGroup/authGroupList',
            ],
        ],
    ],
];
