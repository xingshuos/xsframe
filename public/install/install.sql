DROP TABLE IF EXISTS `#__sys_account`;

CREATE TABLE `#__sys_account`
(
    `uniacid`      int(10) NOT NULL AUTO_INCREMENT,
    `name`         varchar(50)  NOT NULL DEFAULT '',
    `logo`         varchar(255) NOT NULL DEFAULT '',
    `description`  varchar(150)          DEFAULT '',
    `settings`     text,
    `createtime`   int(11) NOT NULL DEFAULT '0',
    `displayorder` int(10) NOT NULL DEFAULT '0',
    `status`       tinyint(1) NOT NULL DEFAULT '0',
    `deleted`      tinyint(1) NOT NULL DEFAULT '0',
    `keywords`     varchar(255) NOT NULL DEFAULT '',
    `copyright`    text,
    PRIMARY KEY (`uniacid`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__sys_account_host`;

CREATE TABLE `#__sys_account_host`
(
    `id`             int(10) NOT NULL AUTO_INCREMENT,
    `uniacid`        int(10) NOT NULL DEFAULT '0',
    `host_url`       varchar(50)  NOT NULL DEFAULT '',
    `default_module` varchar(30)  NOT NULL DEFAULT '',
    `default_url`    varchar(100) NOT NULL DEFAULT '',
    `displayorder`   int(10) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    UNIQUE KEY `host_url` (`host_url`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__sys_account_modules`;

CREATE TABLE `#__sys_account_modules`
(
    `id`           int(10) NOT NULL AUTO_INCREMENT,
    `uniacid`      int(10) NOT NULL DEFAULT '0',
    `module`       varchar(100) NOT NULL DEFAULT '',
    `settings`     mediumtext,
    `is_default`   tinyint(1) NOT NULL DEFAULT '0',
    `deleted`      tinyint(1) NOT NULL DEFAULT '0',
    `displayorder` int(10) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    KEY            `module` (`module`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__sys_account_perm_role`;

CREATE TABLE `#__sys_account_perm_role`
(
    `id`           int(11) NOT NULL AUTO_INCREMENT,
    `uniacid`      int(11) DEFAULT '0',
    `rolename`     varchar(255) DEFAULT '',
    `status`       tinyint(3) DEFAULT '0',
    `perms`        text,
    `deleted`      tinyint(3) DEFAULT '0',
    `displayorder` int(10) DEFAULT '0',
    PRIMARY KEY (`id`),
    KEY            `idx_uniacid` (`uniacid`),
    KEY            `idx_status` (`status`),
    KEY            `idx_deleted` (`deleted`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__sys_account_perm_user`;

CREATE TABLE `#__sys_account_perm_user`
(
    `id`           int(11) NOT NULL AUTO_INCREMENT,
    `uniacid`      int(11) DEFAULT '0',
    `uid`          int(11) DEFAULT '0',
    `realname`     varchar(255) DEFAULT '',
    `mobile`       varchar(255) DEFAULT '',
    `roleid`       int(10) DEFAULT '0',
    `perms`        text,
    `mid`          int(10) DEFAULT '0',
    `status`       tinyint(1) DEFAULT '0',
    `createtime`   int(11) DEFAULT '0',
    `deleted`      tinyint(3) DEFAULT '0',
    `displayorder` int(10) DEFAULT '0',
    PRIMARY KEY (`id`),
    KEY            `idx_uniacid` (`uniacid`),
    KEY            `idx_uid` (`uid`),
    KEY            `idx_deleted` (`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__sys_account_users`;

CREATE TABLE `#__sys_account_users`
(
    `id`      int(10) NOT NULL AUTO_INCREMENT,
    `uniacid` int(10) NOT NULL DEFAULT '0',
    `user_id` int(10) NOT NULL DEFAULT '0',
    `module`  varchar(50) NOT NULL DEFAULT '',
    PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__sys_attachment`;

CREATE TABLE `#__sys_attachment`
(
    `id`          int(10) unsigned NOT NULL AUTO_INCREMENT,
    `uniacid`     int(10) unsigned NOT NULL,
    `uid`         int(10) unsigned NOT NULL,
    `filename`    varchar(100) NOT NULL,
    `fileurl`     varchar(255) NOT NULL,
    `type`        tinyint(3) unsigned NOT NULL DEFAULT '0',
    `createtime`  int(10) unsigned NOT NULL,
    `module`      varchar(50)  NOT NULL,
    `group_id`    int(11) DEFAULT NULL,
    `client_name` varchar(30)  NOT NULL DEFAULT 'web',
    PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__sys_attachment_group`;

CREATE TABLE `#__sys_attachment_group`
(
    `id`           int(11) NOT NULL AUTO_INCREMENT,
    `uniacid`      int(11) NOT NULL DEFAULT '0',
    `name`         varchar(25) NOT NULL DEFAULT '',
    `type`         tinyint(3) NOT NULL DEFAULT '0',
    `parentid`     int(10) NOT NULL DEFAULT '0',
    `displayorder` int(10) NOT NULL DEFAULT '0',
    `uid`          int(10) NOT NULL DEFAULT '0',
    `client_name`  varchar(30) NOT NULL DEFAULT 'web',
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__sys_member`;

CREATE TABLE `#__sys_member`
(
    `id`            int(10) NOT NULL AUTO_INCREMENT,
    `uniacid`       int(10) DEFAULT '0',
    `username`      varchar(11)    DEFAULT '' COMMENT '用户账号',
    `password`      varchar(100)   DEFAULT '' COMMENT '密码',
    `salt`          varchar(20)    DEFAULT '' COMMENT '加密盐',
    `parentid`      int(10) DEFAULT '0' COMMENT '上级id',
    `nickname`      varbinary(50) DEFAULT '' COMMENT '昵称',
    `avatar`        varchar(255)   DEFAULT '' COMMENT '用户头像',
    `level`         int(6) DEFAULT '0' COMMENT '用户等级',
    `groupid`       int(10) DEFAULT '0' COMMENT '用户分组id',
    `credit1`       decimal(10, 0) DEFAULT '0' COMMENT '积分',
    `credit2`       decimal(10, 2) DEFAULT '0.00' COMMENT '余额（用户实际充值金额，不允许提现）',
    `credit3`       decimal(10, 2) DEFAULT '0.00' COMMENT 'ios 账户充值金额（用户实际充值金额，不允许提现,苹果官方实际抽成 30%）',
    `credit4`       decimal(10, 2) DEFAULT '0.00' COMMENT '赠送余额（所有方式赠送,支付可以抵扣使用,不是真实金额）',
    `credit5`       decimal(10, 2) DEFAULT '0.00' COMMENT '预留业务扩展使用',
    `realname`      varchar(20)    DEFAULT '' COMMENT '姓名',
    `email`         varchar(30)    DEFAULT '' COMMENT '邮箱',
    `mobile`        varchar(11)    DEFAULT '' COMMENT '手机号',
    `idcard`        varchar(30)    DEFAULT '' COMMENT '身份证号',
    `is_real`       tinyint(1) DEFAULT '0' COMMENT '是否实名认证 0否  1是 2认证通过',
    `gender`        tinyint(1) DEFAULT '0' COMMENT '性别 0未知 1男 2女',
    `birthyear`     varchar(5)     DEFAULT '' COMMENT '生日年',
    `birthmonth`    varchar(5)     DEFAULT '' COMMENT '生日月',
    `birthday`      varchar(5)     DEFAULT '' COMMENT '生日日',
    `constellation` varchar(100)   DEFAULT '' COMMENT '星座',
    `province`      varchar(50)    DEFAULT '' COMMENT '省',
    `city`          varchar(50)    DEFAULT '' COMMENT '市',
    `area`          varchar(50)    DEFAULT '' COMMENT '县',
    `address`       varchar(100)   DEFAULT '' COMMENT '地址',
    `source`        varchar(20)    DEFAULT '' COMMENT '来源（wechat,wxapp,h5,ios,android,douyin,pc）',
    `remark`        varchar(100)   DEFAULT '' COMMENT '会员备注',
    `create_time`   int(11) DEFAULT '0' COMMENT '创建时间',
    `update_time`   int(11) DEFAULT '0' COMMENT '更新时间',
    `is_deleted`    tinyint(1) DEFAULT '0' COMMENT '是否删除 0否 1是',
    `mobile_verify` tinyint(1) DEFAULT '0' COMMENT '手机号是否验证',
    `signature`     varchar(255)   DEFAULT '' COMMENT '个性签名',
    `qq_openid`     varchar(100)   DEFAULT '' COMMENT 'appQQ登录openid',
    `wechat_openid` varchar(100)   DEFAULT '' COMMENT '微信公众号登录openid',
    `wx_unionid`    varchar(100)   DEFAULT '' COMMENT '微信开放平台唯一用户id',
    `appwx_openid`  varchar(100)   DEFAULT '' COMMENT 'app微信登录openid',
    `wxapp_openid`  varchar(100)   DEFAULT '' COMMENT '小程序账号登录',
    `wb_openid`     varchar(100)   DEFAULT '' COMMENT 'app微博登录openid',
    `apple_openid`  varchar(64)    DEFAULT '' COMMENT 'ios登录',
    `jt_openid`     varchar(100)   DEFAULT '' COMMENT '鲸探APP授权登录openid',
    `ali_openid`    varchar(100)   DEFAULT '' COMMENT '支付宝授权登录',
    `isblack`       tinyint(1) DEFAULT '0' COMMENT '是否拉入黑名单 0否 1是',
    `job`           varchar(30)    DEFAULT '' COMMENT '工作职务',
    PRIMARY KEY (`id`) USING BTREE,
    KEY             `idx_jt_openid` (`jt_openid`),
    KEY             `idx_username` (`username`),
    KEY             `idx_mobile` (`mobile`),
    KEY             `idx_wechat_openid` (`wechat_openid`),
    KEY             `idx_ali_openid` (`ali_openid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='系统会员表';

DROP TABLE IF EXISTS `#__sys_member_address`;

CREATE TABLE `#__sys_member_address`
(
    `id`        int(11) NOT NULL AUTO_INCREMENT,
    `uniacid`   int(11) DEFAULT '0',
    `mid`       int(10) DEFAULT '0' COMMENT '用户id',
    `realname`  varchar(20)          DEFAULT '' COMMENT '姓名',
    `mobile`    varchar(11)          DEFAULT '' COMMENT '手机',
    `province`  varchar(30)          DEFAULT '' COMMENT '省',
    `city`      varchar(30)          DEFAULT '' COMMENT '市',
    `area`      varchar(30)          DEFAULT '' COMMENT '区',
    `street`    varchar(50) NOT NULL DEFAULT '' COMMENT '街道',
    `address`   varchar(300)         DEFAULT '' COMMENT '地址',
    `isdefault` tinyint(1) DEFAULT '0' COMMENT '是否默认 0否 1是',
    `datavalue` varchar(50)          DEFAULT '' COMMENT '数据值',
    `zip_code`  varchar(20)          DEFAULT '' COMMENT '邮编',
    `deleted`   tinyint(1) DEFAULT '0' COMMENT '是否删除 0否 1是',
    `lng`       varchar(20)          DEFAULT '' COMMENT '经度',
    `lat`       varchar(20)          DEFAULT '' COMMENT '纬度',
    PRIMARY KEY (`id`),
    KEY         `idx_uniacid` (`uniacid`),
    KEY         `idx_mid` (`mid`),
    KEY         `idx_isdefault` (`isdefault`),
    KEY         `idx_deleted` (`deleted`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__sys_member_credits_record`;

CREATE TABLE `#__sys_member_credits_record`
(
    `id`         int(11) NOT NULL AUTO_INCREMENT,
    `uniacid`    int(11) NOT NULL COMMENT '项目',
    `uid`        int(10) unsigned NOT NULL COMMENT '用户id',
    `credittype` varchar(10)    NOT NULL COMMENT '字段类型',
    `num`        decimal(10, 2) NOT NULL COMMENT '数值',
    `module`     varchar(30)    NOT NULL COMMENT '应用',
    `remark`     varchar(200)   NOT NULL COMMENT '备注',
    `operator`   int(10) NOT NULL DEFAULT '0' COMMENT '操作人',
    `createtime` int(10) unsigned NOT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`),
    KEY          `uniacid` (`uniacid`),
    KEY          `uid` (`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__sys_member_level`;

CREATE TABLE `#__sys_member_level`
(
    `id`         int(11) NOT NULL AUTO_INCREMENT,
    `uniacid`    int(11) NOT NULL,
    `level`      int(11) DEFAULT '0' COMMENT '级别',
    `levelname`  varchar(50)    DEFAULT '' COMMENT '等级名称',
    `ordermoney` decimal(10, 2) DEFAULT '0.00' COMMENT '订单金额达到多少升级',
    `ordercount` int(10) DEFAULT '0' COMMENT '订单数量达到多少升级',
    `discount`   decimal(10, 2) DEFAULT '0.00' COMMENT '折扣',
    `enabled`    tinyint(3) DEFAULT '0' COMMENT '是否启用 0否 1是',
    `createtime` int(11) DEFAULT '0' COMMENT '创建时间',
    `deleted`    tinyint(1) DEFAULT '0' COMMENT '是否删除 0否 1是',
    PRIMARY KEY (`id`),
    KEY          `idx_uniacid` (`uniacid`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='会员等级表';

DROP TABLE IF EXISTS `#__sys_modules`;

CREATE TABLE `#__sys_modules`
(
    `id`             int(11) NOT NULL AUTO_INCREMENT,
    `type`           varchar(30)  DEFAULT '',
    `name`           varchar(50)  DEFAULT '',
    `identifie`      varchar(20)  DEFAULT '',
    `version`        varchar(20)  DEFAULT '',
    `author`         varchar(20)  DEFAULT '',
    `logo`           varchar(255) DEFAULT '',
    `ability`        varchar(150) DEFAULT '',
    `description`    varchar(255) DEFAULT '',
    `create_time`    int(11) DEFAULT '0',
    `update_time`    int(11) DEFAULT '0',
    `name_initial`   varchar(1)   DEFAULT '',
    `status`         tinyint(1) DEFAULT '0',
    `is_cloud`       tinyint(1) DEFAULT '0',
    `is_install`     tinyint(1) DEFAULT '0',
    `is_deleted`     tinyint(1) DEFAULT '0',
    `wechat_support` tinyint(1) DEFAULT '0',
    `wxapp_support`  tinyint(1) DEFAULT '0',
    `pc_support`     tinyint(1) DEFAULT '0',
    `app_support`    tinyint(1) DEFAULT '0',
    `h5_support`     tinyint(1) DEFAULT '0',
    `aliapp_support` tinyint(1) DEFAULT '0',
    `bdapp_support`  tinyint(1) DEFAULT '0',
    `uniapp_support` tinyint(1) DEFAULT '0',
    PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__sys_paylog`;

CREATE TABLE `#__sys_paylog`
(
    `id`         bigint(11) unsigned NOT NULL AUTO_INCREMENT,
    `uniacid`    int(11) NOT NULL,
    `type`       tinyint(1) NOT NULL DEFAULT '0',
    `ordersn`    varchar(128)   NOT NULL,
    `fee`        decimal(10, 2) NOT NULL,
    `status`     tinyint(4) NOT NULL,
    `module`     varchar(50)    NOT NULL,
    `createtime` int(11) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    KEY          `idx_tid` (`ordersn`),
    KEY          `idx_uniacid` (`uniacid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__sys_queue`;

CREATE TABLE `#__sys_queue`
(
    `id`         int(10) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
    `uniacid`    int(10) NOT NULL DEFAULT '0' COMMENT '商户uniacid',
    `module`     varchar(20) NOT NULL DEFAULT '' COMMENT '应用标识',
    `key`        varchar(50) NOT NULL COMMENT '队列key值',
    `tag`        text COMMENT '业务数据 可以是json或者serialize 格式',
    `status`     tinyint(1) DEFAULT '0' COMMENT '执行状态 0未执行 1已执行 -1执行失败 2已执行（执行后删除）',
    `createtime` int(11) DEFAULT '0' COMMENT '创建时间',
    `sign`       varchar(100)         DEFAULT '' COMMENT '唯一标识防止重复添加（特殊情境下可能需要）',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='系统队列表'


DROP TABLE IF EXISTS `#__sys_settings`;

CREATE TABLE `#__sys_settings`
(
    `key`   varchar(200) NOT NULL,
    `value` text         NOT NULL,
    PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__sys_sitemap_url`;

CREATE TABLE `#__sys_sitemap_url`
(
    `id`         int(10) NOT NULL AUTO_INCREMENT,
    `uniacid`    int(10) NOT NULL DEFAULT '0',
    `url`        varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
    `createtime` int(11) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `#__sys_users`;

CREATE TABLE `#__sys_users`
(
    `id`         int(10) NOT NULL AUTO_INCREMENT,
    `username`   varchar(20) NOT NULL,
    `password`   varchar(64) NOT NULL,
    `salt`       varchar(10) NOT NULL DEFAULT '',
    `role`       varchar(10) NOT NULL DEFAULT '0',
    `status`     tinyint(1) NOT NULL DEFAULT '0',
    `createtime` int(11) NOT NULL DEFAULT '0',
    `logintime`  int(11) NOT NULL DEFAULT '0',
    `lastip`     varchar(30) NOT NULL DEFAULT '',
    `deleted`    tinyint(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

