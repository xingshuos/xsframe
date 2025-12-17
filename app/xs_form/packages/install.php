<?php
// 目录地址: xs_form/packages/install.php

$sql = "
CREATE TABLE `ims_xs_form_data_basic` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) NOT NULL DEFAULT '0',
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '名称',
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '简介',
  `displayorder` int(10) NOT NULL DEFAULT '0' COMMENT '排序',
  `enabled` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否显示 0否 1是',
  `createtime` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除 0否 1是',
  `isrecommand` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否推荐 0否 1是',
  `isnew` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否最新 0否 1是',
  `ishot` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否最热 0否 1是',
  `type` int(10) NOT NULL DEFAULT '0' COMMENT '类型',
  `author_id` int(10) NOT NULL DEFAULT '0' COMMENT '作者id',
  `education` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '学历多选',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='常用表单保存数据';

CREATE TABLE `ims_xs_form_data_module` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) NOT NULL DEFAULT '0',
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '名称',
  `displayorder` int(10) NOT NULL DEFAULT '0' COMMENT '排序',
  `enabled` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否显示 0否 1是',
  `createtime` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除 0否 1是',
  `date_time` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '日期',
  `start_time` int(11) NOT NULL DEFAULT '0' COMMENT '开始时间',
  `end_time` int(11) NOT NULL DEFAULT '0' COMMENT '结束时间',
  `year` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '年',
  `month` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `day` varchar(10) COLLATE utf8_unicode_ci DEFAULT '',
  `time_str` varchar(20) COLLATE utf8_unicode_ci DEFAULT '',
  `province` varchar(20) COLLATE utf8_unicode_ci DEFAULT '',
  `city` varchar(20) COLLATE utf8_unicode_ci DEFAULT '',
  `area` varchar(30) COLLATE utf8_unicode_ci DEFAULT '',
  `color` varchar(20) COLLATE utf8_unicode_ci DEFAULT '',
  `thumb` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `thumbs` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `video_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `audio_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `content` text COLLATE utf8_unicode_ci,
  `longitude` varchar(30) COLLATE utf8_unicode_ci DEFAULT '',
  `latitude` varchar(30) COLLATE utf8_unicode_ci DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='常用组件表单保存数据';
";

return $sql;