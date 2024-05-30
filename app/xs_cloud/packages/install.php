<?php

$sql = "
   
CREATE TABLE `ims_cloud_app` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(30) DEFAULT '' COMMENT '模块类型（business:主要业务;customer:客户关系;activity:营销及活动;services:常用服务及工具;biz:行业解决方案;sale:营销类;tool:工具类;help:辅助类;enterprise:企业应用;h5game:H5游戏;other:其他;）',
  `name` varchar(50) DEFAULT '' COMMENT '应用名称',
  `identifier` varchar(20) DEFAULT '' COMMENT '应用标识',
  `version` varchar(20) DEFAULT '' COMMENT '当前版本号',
  `author` varchar(20) DEFAULT '' COMMENT '开发者名称',
  `logo` varchar(255) DEFAULT '' COMMENT '应用logo',
  `ability` varchar(150) DEFAULT '' COMMENT '模块功能简述',
  `description` varchar(255) DEFAULT '' COMMENT '应用简介',
  `create_time` int(11) DEFAULT '0' COMMENT '安装时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  `name_initial` varchar(1) DEFAULT '' COMMENT '首字母',
  `status` tinyint(1) DEFAULT '0' COMMENT '是否启用 0否 1是',
  `wechat_support` tinyint(1) DEFAULT '0' COMMENT '公众号 是否支持 0否 1是',
  `wxapp_support` tinyint(1) DEFAULT '0' COMMENT '小程序 是否支持 0否 1是',
  `pc_support` tinyint(1) DEFAULT '0' COMMENT 'pc 是否支持 0否 1是',
  `app_support` tinyint(1) DEFAULT '0' COMMENT 'APP 是否支持 0否 1是',
  `h5_support` tinyint(1) DEFAULT '0' COMMENT 'h5 是否支持 0否 1是',
  `aliapp_support` tinyint(1) DEFAULT '0' COMMENT 'aliapp 是否支持 0否 1是',
  `bdapp_support` tinyint(1) DEFAULT '0' COMMENT 'baiduapp 是否支持 0否 1是',
  `uniapp_support` tinyint(1) DEFAULT '0' COMMENT 'uniapp 是否支持 0否 1是',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `ims_cloud_app_version` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) NOT NULL DEFAULT '0',
  `identifier` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '应用唯一标识',
  `version` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '应用版本号（1.0.1）',
  `content` text COLLATE utf8_unicode_ci NOT NULL COMMENT '更新内容（富文本支持换行）',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否上架 0否 1是',
  `createtime` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除 0否 1是',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='应用版本日志信息';

CREATE TABLE `ims_cloud_frame_version` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `version` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '版本号',
  `title` varchar(150) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '标题（可自定义）',
  `content` text COLLATE utf8_unicode_ci NOT NULL COMMENT '更新内容（最好富文本避免特殊情况）',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '上架状态 0未上架 1已上架',
  `createtime` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除 0否 1是',
  PRIMARY KEY (`id`),
  KEY `idx_version` (`version`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='云框架版本记录';

";

return $sql;