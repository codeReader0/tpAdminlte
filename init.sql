CREATE TABLE `tp_admin_handle_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `admin_user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '后台用户ID',
  `auth_rule_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '权限ID',
  `request_body` varchar(5000) NOT NULL DEFAULT '' COMMENT '请求内容',
  `response_body` varchar(5000) NOT NULL DEFAULT '' COMMENT '响应内容',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `admin_user_id` (`admin_user_id`) USING BTREE,
  KEY `auth_rule_id` (`auth_rule_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='后台操作日志表';


CREATE TABLE `tp_admin_user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `account` varchar(50) NOT NULL DEFAULT '' COMMENT '账号',
  `password` varchar(100) NOT NULL DEFAULT '' COMMENT '密码(加密方式： md5(shal(''123'')))',
  `nickname` varchar(50) NOT NULL DEFAULT '' COMMENT '昵称',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '状态(0.禁用 1.正常)',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `account` (`account`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='后台用户表';


CREATE TABLE `tp_auth_group` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '角色名称',
  `desc` varchar(200) NOT NULL DEFAULT '' COMMENT '角色描述',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '状态(0.禁用 1.正常)',
  `rules` text COMMENT '规则name集合(json格式)',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='后台角色表';

CREATE TABLE `tp_auth_group_access` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `admin_user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '后台用户ID',
  `auth_group_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '角色ID',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `aid_2` (`admin_user_id`,`auth_group_id`) USING BTREE,
  KEY `aid` (`admin_user_id`) USING BTREE,
  KEY `group_id` (`auth_group_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户角色关联表';

CREATE TABLE `tp_auth_rule` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '规则标识',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '规则名称',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '状态(0.禁用 1.正常)',
  `type` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '类型分组',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `name` (`name`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='后台权限表';
