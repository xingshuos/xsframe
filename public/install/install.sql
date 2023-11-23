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

DROP TABLE IF EXISTS `#__sys_area`;

CREATE TABLE `#__sys_area`
(
    `id`             int(11) unsigned NOT NULL AUTO_INCREMENT,
    `letter`         varchar(3)   DEFAULT '',
    `area_name`      varchar(50)  DEFAULT '',
    `keyword`        varchar(100) DEFAULT NULL,
    `area_parent_id` int(11) unsigned DEFAULT '0',
    `area_sort`      int(6) unsigned DEFAULT '0',
    `area_deep`      tinyint(1) unsigned DEFAULT '1',
    PRIMARY KEY (`id`),
    KEY              `area_parent_id` (`area_parent_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__sys_attachment`;

CREATE TABLE `#__sys_attachment`
(
    `id`         int(10) unsigned NOT NULL AUTO_INCREMENT,
    `uniacid`    int(10) unsigned NOT NULL,
    `uid`        int(10) unsigned NOT NULL,
    `filename`   varchar(100) NOT NULL,
    `fileurl`    varchar(255) NOT NULL,
    `type`       tinyint(3) unsigned NOT NULL DEFAULT '0',
    `createtime` int(10) unsigned NOT NULL,
    `module`     varchar(50)  NOT NULL,
    `group_id`   int(11) DEFAULT NULL,
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
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__sys_member`;

CREATE TABLE `#__sys_member`
(
    `id`            int(10) NOT NULL AUTO_INCREMENT,
    `uniacid`       int(10) DEFAULT '0',
    `username`      varchar(11)    DEFAULT '',
    `password`      varchar(100)   DEFAULT '',
    `salt`          varchar(20)    DEFAULT '',
    `parentid`      int(10) DEFAULT '0',
    `nickname`      varbinary(50) DEFAULT '',
    `avatar`        varchar(255)   DEFAULT '',
    `level`         int(6) DEFAULT '0',
    `groupid`       int(10) DEFAULT '0',
    `credit1`       decimal(10, 0) DEFAULT '0',
    `credit2`       decimal(10, 2) DEFAULT '0.00',
    `credit3`       decimal(10, 2) DEFAULT '0.00',
    `credit4`       decimal(10, 2) DEFAULT '0.00',
    `credit5`       decimal(10, 2) DEFAULT '0.00',
    `realname`      varchar(20)    DEFAULT '',
    `mobile`        varchar(11)    DEFAULT '',
    `idcard`        varchar(30)    DEFAULT '',
    `is_real`       tinyint(1) DEFAULT '0',
    `gender`        tinyint(1) DEFAULT '0',
    `birthyear`     varchar(5)     DEFAULT '',
    `birthmonth`    varchar(5)     DEFAULT '',
    `birthday`      varchar(5)     DEFAULT '',
    `constellation` varchar(100)   DEFAULT '',
    `province`      varchar(50)    DEFAULT '',
    `city`          varchar(50)    DEFAULT '',
    `area`          varchar(50)    DEFAULT '',
    `address`       varchar(100)   DEFAULT '',
    `source`        varchar(20)    DEFAULT '',
    `remark`        varchar(100)   DEFAULT '',
    `create_time`   int(11) DEFAULT '0',
    `update_time`   int(11) DEFAULT '0',
    `is_deleted`    tinyint(1) DEFAULT '0',
    `mobile_verify` tinyint(1) DEFAULT '0',
    `signature`     varchar(255)   DEFAULT '',
    `qq_openid`     varchar(100)   DEFAULT '',
    `wechat_openid` varchar(100)   DEFAULT '',
    `appwx_openid`  varchar(100)   DEFAULT '',
    `wxapp_openid`  varchar(100)   DEFAULT '',
    `wb_openid`     varchar(100)   DEFAULT '',
    `apple_openid`  varchar(64)    DEFAULT '',
    `wx_unionid`    varchar(100)   DEFAULT '',
    `isblack`       tinyint(1) DEFAULT '0',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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