-- -----------------------------
-- Veitool MySQL Data Transfer 
-- 
-- Host     : 127.0.0.1
-- Port     : 3306
-- Database : xsframe
-- 
-- Part : #1
-- Date : 2025-02-08 17:17:53
-- -----------------------------

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `ims_cloud_app`;
CREATE TABLE `ims_cloud_app` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) DEFAULT '0' COMMENT '商户id',
  `mid` int(10) NOT NULL DEFAULT '0' COMMENT '开发者id',
  `type` varchar(30) DEFAULT '' COMMENT '模块类型（business:主要业务;customer:客户关系;activity:营销及活动;services:常用服务及工具;biz:行业解决方案;sale:营销类;tool:工具类;help:辅助类;enterprise:企业应用;h5game:H5游戏;other:其他;）',
  `name` varchar(50) DEFAULT '' COMMENT '应用名称',
  `identifier` varchar(20) DEFAULT '' COMMENT '应用标识',
  `version` varchar(20) DEFAULT '' COMMENT '当前版本号',
  `author` varchar(20) DEFAULT '' COMMENT '开发者名称',
  `logo` varchar(255) DEFAULT '' COMMENT '应用logo',
  `ability` varchar(150) DEFAULT '' COMMENT '模块功能简述',
  `description` varchar(255) DEFAULT '' COMMENT '应用简介',
  `create_time` int(11) DEFAULT '0' COMMENT '上传时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  `name_initial` varchar(1) DEFAULT '' COMMENT '首字母',
  `status` tinyint(1) DEFAULT '0' COMMENT '是否上架 0否 1是',
  `wechat_support` tinyint(1) DEFAULT '0' COMMENT '公众号 是否支持 0否 1是',
  `wxapp_support` tinyint(1) DEFAULT '0' COMMENT '小程序 是否支持 0否 1是',
  `pc_support` tinyint(1) DEFAULT '0' COMMENT 'pc 是否支持 0否 1是',
  `app_support` tinyint(1) DEFAULT '0' COMMENT 'APP 是否支持 0否 1是',
  `h5_support` tinyint(1) DEFAULT '0' COMMENT 'h5 是否支持 0否 1是',
  `aliapp_support` tinyint(1) DEFAULT '0' COMMENT 'aliapp 是否支持 0否 1是',
  `bdapp_support` tinyint(1) DEFAULT '0' COMMENT 'baiduapp 是否支持 0否 1是',
  `uniapp_support` tinyint(1) DEFAULT '0' COMMENT 'uniapp 是否支持 0否 1是',
  `harmonyos_support` tinyint(1) DEFAULT '0' COMMENT '鸿蒙OS 是否支持 0否 1是',
  `dyapp_support` tinyint(1) DEFAULT '0' COMMENT '抖音小程序是否支持 0否 1是',
  `deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除 0否 1是',
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_identifier` (`identifier`)
) ENGINE=MyISAM AUTO_INCREMENT=64 DEFAULT CHARSET=utf8 COMMENT='云应用表';

INSERT INTO `ims_cloud_app` VALUES('1','1','1','business','云更新','xs_cloud','1.0.1','GuiHai','images/1/xs_cloud/2024/07/MH2Sos1LTEY6a66y.jpg','云更新（官网）','云更新（官网）','1699862078','1721312207','Y','1','0','0','1','0','0','0','0','0','0','0','0');
INSERT INTO `ims_cloud_app` VALUES('2','1','1','business','开发者中心','xs_developer','1.0.1','GuiHai','images/1/xs_cloud/2024/07/uQPPOj33oh4ZU3W7.jpg','开发者中心（官网）','开发者中心（官网）','1699862078','1721312177','K','1','0','0','1','0','0','0','0','0','0','0','0');
INSERT INTO `ims_cloud_app` VALUES('3','1','1','business','应用商城','xs_store','1.0.1','GuiHai','images/1/xs_cloud/2024/07/MctgtbQ206676Caw.jpg','应用商城（官网）','应用商城（官网）','1699862078','1721312193','Y','1','0','0','1','0','0','0','0','0','0','0','0');
INSERT INTO `ims_cloud_app` VALUES('4','1','1','business','应用测试','xs_test','1.0.1','GuiHai','images/1/xs_cloud/2024/07/tIhpy46Epyw3wj7m.jpg','应用测试（官网）','应用测试（官网）','1700640086','1721312401','Y','1','1','1','1','1','1','1','1','1','0','0','0');
INSERT INTO `ims_cloud_app` VALUES('5','1','1','business','开发文档','xs_doc','1.0.1','GuiHai','images/1/xs_cloud/2024/07/XDgqcqnp2pDP2PA2.jpg','开发文档（官网）','开发文档（官网）','1700721255','1721311956','K','1','0','0','1','0','1','0','0','0','0','0','0');
INSERT INTO `ims_cloud_app` VALUES('6','1','1','business','鲸探H5','jt_h5','1.0.1','GuiHai','app/jt_h5/icon.png','鲸探H5','鲸探H5','1701168270','1701168270','J','1','0','0','0','0','1','0','0','0','0','0','0');
INSERT INTO `ims_cloud_app` VALUES('7','1','1','business','智能邮筒','jt_mail','1.0.1','GuiHai','images/1/xs_developer/2024/09/N4ZwM3MmrmxWXWXc.png','智能邮筒','智能邮筒','1704866908','1709708768','Z','1','0','0','0','0','1','0','0','0','0','0','0');
INSERT INTO `ims_cloud_app` VALUES('8','1','5','business','数字乡村','xs_country','1.0.2','Lee','app/xs_country/icon.png','数字乡村','数字乡村','1704866908','1709708768','S','1','0','1','0','0','1','0','0','0','0','0','0');
INSERT INTO `ims_cloud_app` VALUES('26','1','15','business','数字商协会管理','xs_council','1.0.7','Lee','','理事会','搜雪商协会系统','1718262812','1722262117','S','1','0','1','0','0','1','0','0','1','0','0','0');
INSERT INTO `ims_cloud_app` VALUES('27','1','1','business','微文章','xs_article','1.0.4','GuiHai','','轻量级、高效、易用、好看、简单实用的微信文章管理系统','微信文章','1718352248','1718352248','x','1','1','0','0','0','1','0','0','0','0','0','0');
INSERT INTO `ims_cloud_app` VALUES('28','1','2','business','活动报名','xs_activity','1.0.3','Lee','images/1/xs_cloud/2024/06/w75L26Eay5e44esl.jpeg','活动报名','活动报名','1718879055','1722261977','H','1','0','0','1','0','1','0','0','1','0','0','0');
INSERT INTO `ims_cloud_app` VALUES('29','1','2','business','问卷调查','xs_question','2.0.1','Lee','','搜雪问卷调查','问卷调查','1719551047','1722261685','x','1','0','0','1','0','1','0','0','0','0','0','0');
INSERT INTO `ims_cloud_app` VALUES('30','1','10','business','搜雪招商系统','xs_courtship','1.0.1','Lee','','搜雪招商系统','搜雪招商系统','1719826765','1722260991','x','1','0','0','1','0','1','0','0','1','0','0','0');
INSERT INTO `ims_cloud_app` VALUES('31','1','11','business','权益商城','vip_store','1.0.4','Lee','images/1/xs_cloud/2024/07/ur6nMKygM2Czy9mc.jpg','权益商城','权益商城','1720173349','1722262435','Q','1','0','1','1','0','0','0','0','0','0','0','0');
INSERT INTO `ims_cloud_app` VALUES('32','1','8','business','福利消费券系统','xs_couponcheck','1.1.1','Jiang','images/1/xs_cloud/2024/07/qXxRt9g2TEyEu2eD.jpeg','优惠券核销','优惠券核销','1720424586','1720618828','F','1','1','0','0','0','1','0','0','0','0','0','0');
INSERT INTO `ims_cloud_app` VALUES('33','1','5','business','简单官网小程序','xs_website','2.3','Lee','','简单官网小程序','简单官网小程序','1720681132','1720795326','J','1','0','1','1','0','0','0','0','1','0','0','0');
INSERT INTO `ims_cloud_app` VALUES('34','1','2','business','大转盘抽奖','xs_luck','1.0.3','Lee','','大转盘抽奖','大转盘抽奖','1721095369','1722261577','x','1','0','0','0','0','1','0','0','1','0','0','0');
INSERT INTO `ims_cloud_app` VALUES('35','1','10','station','工位预约','xs_station','1.0.0','fengzi','','工位预约','','1721097431','1721097431','x','1','0','1','1','0','0','0','0','1','0','0','0');
INSERT INTO `ims_cloud_app` VALUES('36','1','3','tool','支付宝设置','xs_alipay','1.0.1','GuiHai','images/1/xs_developer/2024/07/Tu7FcG3W31p0W9z3.png','商户支付宝配置参数快速配置','管理商户支付宝的配置项及参数信息','1721210445','1721210445','x','1','0','0','1','0','0','0','0','0','0','0','0');
INSERT INTO `ims_cloud_app` VALUES('37','1','3','tool','短信配置-阿里云','xs_sms','1.0.1','GuiHai','images/1/xs_store/2024/08/VpyO3wMOh5JHYyp4.jpeg','管理商户短信配置的配置项及参数信息','管理商户短信配置的配置项及参数信息','1721210494','1721210494','x','1','0','0','1','0','0','0','0','0','0','0','0');
INSERT INTO `ims_cloud_app` VALUES('38','1','3','tool','邮件设置','xs_smtp','1.0.1','GuiHai','images/1/xs_developer/2024/07/H99kX9AhUlU6AyVo.png','管理商户邮件的配置项及参数信息','管理商户邮件的配置项及参数信息','1721210587','1721210587','x','1','0','0','1','0','0','0','0','0','0','0','0');
INSERT INTO `ims_cloud_app` VALUES('39','1','3','tool','公众号配置','xs_wechat','1.0.1','GuiHai','images/1/xs_store/2024/08/JQ2tiKikK9K842xJ.jpg','管理商户公众号的配置项及参数信息','管理商户公众号的配置项及参数信息','1721210655','1721210655','x','1','0','0','1','0','0','0','0','0','0','0','0');
INSERT INTO `ims_cloud_app` VALUES('40','1','3','tool','企业微信设置','xs_weixin','1.0.1','GuiHai','images/1/xs_store/2024/08/Ur8LJt8t3ysqjjEo.jpg','管理商户企业微信的配置项及参数信息','管理商户企业微信的配置项及参数信息','1721210699','1721210699','x','1','0','0','1','0','0','0','0','0','0','0','0');
INSERT INTO `ims_cloud_app` VALUES('41','1','3','tool','微信小程序设置','xs_wxapp','1.0.2','GuiHai','images/1/xs_developer/2024/09/spcddyrYJ88P88v1.png','管理商户微信小程序的配置项及参数信息','管理商户微信小程序的配置项及参数信息','1721210751','1721210751','x','1','0','0','1','0','0','0','0','0','0','0','0');
INSERT INTO `ims_cloud_app` VALUES('42','1','3','tool','微信支付设置','xs_wxpay','1.0.1','GuiHai','','管理商户微信支付的配置项及参数信息','管理商户微信支付的配置项及参数信息','1721210796','1721210796','x','1','0','0','1','0','0','0','0','0','0','0','0');
INSERT INTO `ims_cloud_app` VALUES('43','1','8','business','积分消费','xs_scoreconsume','1.0.3','Jiang','','积分消费','积分消费','1721211657','1721211657','x','1','0','0','1','0','1','0','0','0','0','0','0');
INSERT INTO `ims_cloud_app` VALUES('44','1','5','business','自适应官网','official_website','1.2','Lee','','自适应官网','自适应官网','1721361365','1721361365','o','1','0','0','1','0','1','0','0','1','0','0','0');
INSERT INTO `ims_cloud_app` VALUES('45','1','3','tool','附件管理-阿里云OSS配置','xs_attachment','1.0.1','GuiHai','images/1/xs_developer/2024/07/R3LxXcoXSs704C2z.png','管理商户附件（文件）的配置项及基本管理','管理商户附件（文件）的配置项及基本管理','1721370893','1721370893','x','1','0','0','1','0','0','0','0','0','0','0','0');
INSERT INTO `ims_cloud_app` VALUES('46','1','3','tool','天目云-传播大脑','xs_mind','1.0.1','Lee','images/1/xs_store/2024/07/HNy5Iz70aaaNai5w.jpg','天目云-传播大脑的配置项及参数信息','传播大脑接口文档,使用传播大脑APP的融媒体可以打通用户体系,以及积分体系,用于融媒体的应用能够迅速接入APP','1721873367','1721893337','x','1','1','1','1','0','1','0','0','1','0','0','0');
INSERT INTO `ims_cloud_app` VALUES('47','1','3','tool','表单案例','xs_form','1.0.1','GuiHai','images/1/xs_developer/2024/07/ztntDIZitCAF0cdd.png','后台常用表单案例管理','表单案例应用专为初级开发者及使用者精心打造，旨在通过亲身体验，领略后台表单的非凡魅力与卓越展示效果。该应用集合了广泛应用的基础表单示例与常见表单组件案例，极大地简化了开发流程，提升了工作效率，让开发者能够更加轻松高效地构建项目！','1721983612','1721983612','x','1','0','0','1','0','0','0','0','0','0','0','0');
INSERT INTO `ims_cloud_app` VALUES('48','1','5','business','短剧宣发系统','xs_shortmovies','1.1','Lee','images/1/xs_store/2024/07/FjSpZ78X78s33AEd.jpeg','星数短剧','通过短剧宣发系统，制作团队可以更加高效地管理内容、分析数据、推广作品，从而在竞争激烈的短视频市场中脱颖而出。获得私域的流量,从而增加收入','1722246121','1722260926','x','1','0','1','1','0','0','0','0','1','0','0','0');
INSERT INTO `ims_cloud_app` VALUES('49','1','5','tool','七牛云-接口配置','xs_qiniu','1.0.1','Lee','','七牛云-接口配置项及参数信息','七牛云接口配置','1722569321','1722569321','x','1','0','0','1','0','0','0','0','1','0','0','0');
INSERT INTO `ims_cloud_app` VALUES('50','1','5','tool','又拍云-接口配置','xs_youpai','1.0.1','Lee','images/1/xs_developer/2024/08/EMpf2H9iP1xM1OX4.png','又拍云-接口配置项及参数信息','又拍云接口配置','1722577378','1722577378','x','1','1','1','1','1','1','0','0','0','0','0','0');
INSERT INTO `ims_cloud_app` VALUES('51','1','8','business','景区票务','xs_scenicspot','1.1.5','Jiang','images/1/xs_developer/2024/08/l7bb30tJWw0r798r.jpg','景区票务','景区票务','1722579282','1722579282','x','1','0','1','0','0','0','0','0','0','0','0','0');
INSERT INTO `ims_cloud_app` VALUES('52','1','5','business','轻商城(鲸探APP)','easy_shop','1.0.3','Lee','images/1/xs_store/2024/06/Z1883E85eZ8c38cd.png','打造简易实用的商城系统','摒弃了很多花里胡哨的功能,帮助用户从最简单的操作开始\r\n一个小时就可以学会用小程序来经营熟悉的客户\r\n客户买得方便,少受干扰','1722579282','1722579282','J','0','1','0','0','0','0','0','0','0','0','0','0');
INSERT INTO `ims_cloud_app` VALUES('53','1','5','business','微商城--简单快速产品上网','small_shop','2.0','Hui','images/1/xs_developer/2024/08/KHMQmC968ik6PvmK.png','微商城','摒弃了很多花里胡哨的功能,帮助用户从最简单的操作开始\r\n一个小时就可以学会用小程序来经营熟悉的客户\r\n客户买得方便,少受干扰','1722929804','1722929804','s','1','1','1','0','0','0','0','0','1','0','0','0');
INSERT INTO `ims_cloud_app` VALUES('54','1','5','tool','公众号自定义菜单','wx_menu','1.0.1','Lee','images/1/xs_developer/2024/08/v1Yu7tU5tu5Gb2BS.jpg','管理商户微信公众号子菜单配置项及参数信息','微信公众号自定义子菜单','1723082524','1723082524','w','1','1','0','1','0','0','0','0','0','0','0','0');
INSERT INTO `ims_cloud_app` VALUES('55','0','0','caller','访客预约登记','xs_caller','1.0.3','fengzi','images/1/xs_store/2024/08/jOZHK77h7izwmoNV.jpg','访客预约登记','访客预约登记，处理','1724039301','1724051037','F','1','0','1','1','0','0','0','0','0','0','0','0');
INSERT INTO `ims_cloud_app` VALUES('56','1','8','business','校园投稿','xs_contribute','1.0.5','Jiang','images/1/xs_developer/2024/08/zzI622LvW9Dei52I.jpeg','校园投稿','校园投稿系统','1724123836','1724123836','x','1','0','0','0','0','1','0','0','0','0','0','0');
INSERT INTO `ims_cloud_app` VALUES('57','1','11','business','公域转私域流量积分商城','exchange_shop','2.6','Hui','images/1/xs_developer/2024/08/Q0jVz2QQZQqiz6ii.png','公域转私域流量积分商城','公域转私域流量积分商城','1724738836','1724760626','e','1','0','1','1','0','0','0','0','1','0','0','0');
INSERT INTO `ims_cloud_app` VALUES('58','1','3','tool','银联商务支付接入系统','xs_unionpay','2.0.1','GuiHai','images/1/xs_developer/2024/09/AG9793kPpLO0z7mg.png','银联支付管理与案例','目前支持银联商务的配置与调用功能，后续将支持更多银联支付对接与案例功能','1725615709','1725615709','x','1','1','1','1','1','1','0','0','1','0','0','0');
INSERT INTO `ims_cloud_app` VALUES('59','1','3','business','收钱吧','xs_shouqianba','1.0.1','GuiHai','images/1/xs_developer/2024/09/SN5J555cdz05NqW4.png','收钱吧','无缝对接收钱吧接口，使用简单只需要简单几行配置就能实现对接，省去n多的对接烦恼','1727259746','1727259746','x','1','0','1','1','0','1','0','0','0','0','0','0');
INSERT INTO `ims_cloud_app` VALUES('60','1','5','business','产业服务平台（助力地方产业经济发展）','xs_industry','2.0','Lee','images/1/xs_store/2024/10/fqoKRz0icog5fZaj.jpg','产业服务','依托产业集群或优势产业，专门属地的政府提供企业成长全生命周期成长关怀系统。营造一个数字化的产业服务平台，利于招商，引商，稳商，助力地方产业经济高质量发展','1728459770','1731031110','x','1','0','0','0','0','1','0','0','0','0','0','0');
INSERT INTO `ims_cloud_app` VALUES('61','1','11','tool','抖音小程序设置','xs_douyin','1.2','OnesThink','images/1/xs_developer/2024/10/r22J2IyWYAyYDOjo.png','抖音小程序的配置项及参数信息','抖音小程序设置','1728973523','1728973523','x','1','0','0','0','0','1','0','0','1','0','0','0');
INSERT INTO `ims_cloud_app` VALUES('62','1','5','business','数字供应链系统','exchange_cloud','1.0','Hui','images/1/xs_developer/2024/11/g0jVeifKeirvVDRc.png','数字供应链系统','数字供应链系统','1730427725','1730427725','e','1','0','0','1','0','0','0','0','0','0','0','0');
INSERT INTO `ims_cloud_app` VALUES('63','1','8','business','微课程','xs_microlecture','1.0.0','Jiang','images/1/xs_developer/2024/10/wjvj6uVvJYjjA9j6.png','微课程','微课程','1731465271','1731465271','x','1','0','0','0','0','1','0','0','0','0','0','0');

DROP TABLE IF EXISTS `ims_cloud_app_log`;
CREATE TABLE `ims_cloud_app_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) DEFAULT '0' COMMENT '商户id',
  `mid` int(10) NOT NULL DEFAULT '0' COMMENT '=cloud_member.id',
  `host_url` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '主机域名',
  `host_ip` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '主机IP',
  `createtime` int(11) NOT NULL DEFAULT '0' COMMENT '下载时间',
  `identifier` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '应用标识符',
  `version` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '下载版本',
  `php_version` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'php版本',
  `deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除 0否 1是',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=316 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='下载记录';

INSERT INTO `ims_cloud_app_log` VALUES('5','1','5','www.xsframe.com','127.0.0.1','1718360736','xs_council','1.0.2','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('4','1','1','www.xsframe.com','127.0.0.1','1718265039','xs_council','1.0.1','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('6','1','5','www.xsframe.com','127.0.0.1','1718360771','xs_council','1.0.2','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('7','1','5','www.xsframe.com','127.0.0.1','1718360806','xs_council','1.0.2','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('8','1','5','www.xsframe.com','127.0.0.1','1718360820','xs_council','1.0.2','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('9','1','5','www.xsframe.com','127.0.0.1','1718360843','xs_council','1.0.2','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('10','1','5','www.xsframe.com','127.0.0.1','1718361027','xs_council','1.0.2','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('11','1','5','www.xsframe.com','127.0.0.1','1718361029','xs_council','1.0.2','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('12','1','5','www.xsframe.com','127.0.0.1','1718361045','xs_council','1.0.2','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('13','1','5','www.xsframe.com','127.0.0.1','1718361095','xs_council','1.0.2','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('14','1','5','www.xsframe.com','127.0.0.1','1718361421','xs_article','1.0.1','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('15','1','5','www.xsframe.com','127.0.0.1','1718361445','xs_article','1.0.1','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('16','1','5','www.xsframe.com','127.0.0.1','1718361498','xs_council','1.0.2','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('17','1','5','www.xsframe.com','127.0.0.1','1718361717','xs_council','1.0.2','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('18','1','5','www.xsframe.com','127.0.0.1','1718361744','xs_article','1.0.1','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('19','1','1','www.xsframe.cn','115.194.190.119','1718590361','xs_council','1.0.2','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('20','1','1','ceshi.xsframe.cn','115.194.190.119','1718780729','xs_council','1.0.3','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('21','1','1','ceshi.xsframe.cn','115.194.190.119','1718781237','xs_council','1.0.3','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('22','1','1','ceshi.xsframe.cn','115.194.190.119','1718781246','xs_council','1.0.3','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('23','1','1','ceshi.xsframe.cn','115.194.190.119','1718781265','xs_council','1.0.3','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('24','1','1','ceshi.xsframe.cn','115.194.190.119','1718781302','xs_council','1.0.3','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('25','1','1','ceshi.xsframe.cn','115.194.190.119','1718781354','xs_council','1.0.3','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('26','1','5','www.xsframe.cn','115.194.190.119','1718781610','xs_council','1.0.3','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('27','1','5','www.xsframe.cn','115.194.190.119','1718781713','xs_council','1.0.3','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('28','1','5','www.xsframe.cn','115.194.190.119','1718782110','xs_council','1.0.3','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('29','1','5','www.xsframe.cn','115.194.190.119','1718782434','xs_council','1.0.4','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('30','1','1','ceshi.xsframe.cn','115.194.190.119','1718785581','xs_article','1.0.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('31','1','1','ceshi.xsframe.cn','115.194.190.119','1718786773','xs_article','1.0.2','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('32','1','1','ceshi.xsframe.cn','115.194.190.119','1718787809','xs_article','1.0.3','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('33','1','1','ceshi.xsframe.cn','115.194.190.119','1718787818','xs_article','1.0.3','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('34','1','1','ceshi.xsframe.cn','115.194.190.119','1718787828','xs_article','1.0.3','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('35','1','1','ceshi.xsframe.cn','115.194.190.119','1718787919','xs_article','1.0.3','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('36','1','5','www.xsframe.cn','115.194.190.119','1718790084','xs_council','1.0.5','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('37','1','1','demo.xsframe.cn','125.120.228.151','1718848140','xs_article','1.0.3','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('38','1','1','demo.xsframe.cn','125.120.228.151','1718849296','xs_article','1.0.4','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('39','1','1','demo.xsframe.cn','125.120.228.151','1718851415','xs_country','1.0.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('40','1','5','www.xsframe.cn','125.120.228.151','1718879282','xs_activity','1.0.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('41','1','5','www.xsframe.cn','125.120.228.151','1718935895','xs_activity','1.0.2','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('42','1','5','www.xsframe.cn','125.120.228.151','1718948575','xs_country','1.0.2','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('43','1','5','www.xsframe.cn','58.101.41.60','1719329735','xs_article','1.0.4','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('44','1','5','www.xsframe.cn','58.101.41.60','1719330653','xs_article','1.0.4','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('45','1','5','www.xsframe.cn','60.163.237.75','1719551444','xs_question','1.0.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('46','1','5','www.xsframe.cn','60.163.237.75','1719553759','xs_question','1.0.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('47','1','5','www.xsframe.cn','60.163.237.75','1719553992','xs_question','1.0.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('48','1','5','www.xsframe.cn','60.163.237.75','1719554458','xs_question','1.0.2','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('49','1','5','www.xsframe.cn','60.163.237.75','1719555277','xs_council','1.0.7','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('50','1','5','www.xsframe.cn','60.163.237.75','1719556333','xs_activity','1.0.3','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('51','1','5','www.xsframe.cn','60.163.237.75','1719557298','xs_activity','1.0.3','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('52','1','5','www.xsframe.cn','60.163.237.75','1719558261','xs_question','1.0.3','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('53','1','5','www.xsframe.cn','60.163.237.75','1719827003','xs_courtship','1.0.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('54','1','5','www.xsframe.cn','115.194.190.116','1719907440','xs_question','2.0.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('55','1','5','www.xsframe.cn','115.194.190.116','1720173426','vip_store','1.0.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('56','1','5','www.xsframe.cn','115.194.190.116','1720174329','vip_store','1.0.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('57','1','5','www.xsframe.cn','115.194.190.116','1720174343','vip_store','1.0.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('58','1','5','www.xsframe.cn','115.194.190.116','1720174431','vip_store','1.0.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('59','1','5','www.xsframe.cn','115.194.190.116','1720175560','vip_store','1.0.3','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('60','1','5','www.xsframe.cn','115.194.190.116','1720416915','vip_store','1.0.4','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('61','1','5','www.xsframe.cn','115.194.190.116','1720417577','vip_store','1.0.4','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('62','1','5','www.xsframe.cn','115.194.190.116','1720425009','xs_couponcheck','1.0.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('63','1','5','www.xsframe.cn','115.194.190.116','1720426396','xs_couponcheck','1.0.2','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('64','1','5','www.xsframe.cn','115.194.190.116','1720428410','xs_couponcheck','1.0.3','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('65','1','5','www.xsframe.cn','115.194.190.116','1720428766','xs_couponcheck','1.0.4','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('66','1','5','www.xsframe.cn','115.194.190.116','1720429278','xs_couponcheck','1.0.5','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('67','1','5','www.xsframe.cn','115.194.190.116','1720430879','xs_couponcheck','1.0.6','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('68','1','5','www.xsframe.cn','115.194.190.116','1720434048','xs_couponcheck','1.0.7','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('69','1','5','www.xsframe.cn','115.194.190.116','1720434545','xs_couponcheck','1.0.7','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('70','1','5','www.xsframe.cn','115.194.190.116','1720434838','xs_couponcheck','1.0.8','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('71','1','5','www.xsframe.cn','115.194.190.116','1720437077','xs_couponcheck','1.0.9','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('72','1','5','www.xsframe.cn','115.194.190.116','1720437491','xs_couponcheck','1.1.0','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('73','1','5','www.xsframe.cn','115.194.190.116','1720492670','xs_couponcheck','1.1.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('74','1','5','www.xsframe.cn','115.194.189.176','1720681286','xs_website','2.0','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('75','1','5','www.xsframe.cn','115.194.189.176','1720747066','xs_website','2.0','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('76','1','5','www.xsframe.cn','115.194.189.176','1720747116','xs_website','2.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('77','1','5','xcx.souxue.cc','115.194.189.176','1720747614','xs_website','2.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('78','1','5','xcx.souxue.cc','115.194.189.176','1720747683','xs_website','2.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('79','1','5','xcx.souxue.cc','115.194.189.176','1720747745','xs_website','2.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('80','1','5','xcx.souxue.cc','115.194.189.176','1720748285','xs_website','2.2','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('81','1','1','ceshi.xsframe.cn','115.194.189.176','1720886577','xs_country','1.0.2','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('82','1','1','ceshi.xsframe.cn','115.194.189.176','1720886595','xs_country','1.0.2','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('83','1','5','www.xsframe.cn','125.120.224.219','1721030259','xs_website','2.3','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('84','1','1','ceshi.xsframe.cn','125.120.224.219','1721051002','xs_council','1.0.7','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('85','1','1','ceshi.xsframe.cn','125.120.224.219','1721051024','xs_council','1.0.7','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('86','1','5','www.xsframe.cn','115.194.189.46','1721101768','xs_luck','1.0.2','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('87','1','5','www.xsframe.cn','115.194.189.46','1721101868','xs_luck','1.0.2','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('88','1','5','www.xsframe.cn','115.194.189.46','1721101894','xs_luck','1.0.2','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('89','1','5','www.xsframe.cn','115.194.189.46','1721101901','xs_luck','1.0.2','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('90','1','5','www.xsframe.cn','115.194.189.46','1721101979','xs_luck','1.0.2','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('91','1','5','www.xsframe.cn','115.194.189.46','1721102540','xs_luck','1.0.2','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('92','1','5','www.xsframe.cn','115.194.189.46','1721109555','xs_luck','1.0.2','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('93','1','5','www.xsframe.cn','115.194.189.46','1721109607','xs_luck','1.0.2','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('94','1','5','www.xsframe.cn','115.194.189.46','1721110867','xs_luck','1.0.3','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('95','1','5','www.xsframe.cn','115.194.189.46','1721111120','xs_luck','1.0.3','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('96','1','5','www.xsframe.cn','115.194.189.46','1721111977','xs_luck','1.0.3','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('97','1','5','www.xsframe.cn','115.194.189.46','1721212269','xs_scoreconsume','1.0.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('98','1','2','ceshi.xsframe.cn','115.194.189.46','1721298658','xs_test','1.0.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('99','1','2','ceshi.xsframe.cn','115.194.189.46','1721308977','xs_wechat','1.0.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('100','1','2','ceshi.xsframe.cn','115.194.189.46','1721308982','xs_weixin','1.0.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('101','1','2','ceshi.xsframe.cn','115.194.189.46','1721308986','xs_wxpay','1.0.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('102','1','2','ceshi.xsframe.cn','115.194.189.46','1721309458','xs_sms','1.0.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('103','1','5','www.xsframe.cn','115.194.189.46','1721359569','xs_scoreconsume','1.0.2','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('104','1','5','www.xsframe.cn','115.194.189.46','1721361468','official_website','1.0.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('105','1','5','www.xsframe.cn','115.194.189.46','1721382187','xs_scoreconsume','1.0.3','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('106','1','2','ceshi.xsframe.cn','60.177.32.234','1721448251','xs_smtp','1.0.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('107','1','5','www.xsframe.cn','36.27.86.159','1721697904','official_website','1.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('108','1','5','www.xsframe.cn','122.235.248.95','1721817511','official_website','1.2','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('109','1','2','ceshi.xsframe.cn','58.100.218.60','1721833125','xs_wxpay','1.0.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('110','1','2','ceshi.xsframe.cn','58.100.218.60','1721833130','xs_smtp','1.0.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('111','1','2','ceshi.xsframe.cn','58.243.250.205','1721961475','xs_mind','1.0.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('112','1','1','www.xsframe.cn','122.235.248.95','1721988828','xs_alipay','1.0.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('113','1','1','www.xsframe.cn','122.235.248.95','1721988831','xs_smtp','1.0.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('114','1','1','www.xsframe.cn','122.235.248.95','1721988833','xs_weixin','1.0.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('115','1','1','www.xsframe.cn','36.27.86.183','1722236183','xs_attachment','1.0.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('116','1','1','www.xsframe.cn','36.27.86.183','1722236185','xs_sms','1.0.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('117','1','1','www.xsframe.cn','36.27.86.183','1722246208','xs_shortmovies','1.0.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('118','1','1','www.xsframe.cn','36.27.86.183','1722246866','xs_shortmovies','1.0.2','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('119','1','1','www.xsframe.cn','36.27.86.183','1722309843','xs_shortmovies','1.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('120','1','2','ceshi.xsframe.cn','36.27.86.183','1722405532','xs_alipay','1.0.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('121','1','1','www.xsframe.cn','183.158.207.96','1722579561','xs_scenicspot','1.0.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('122','1','1','www.xsframe.cn','183.158.207.96','1722582132','xs_scenicspot','1.0.2','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('123','1','1','www.xsframe.cn','183.158.207.96','1722582345','xs_scenicspot','1.0.3','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('124','1','1','www.xsframe.cn','183.158.207.96','1722582646','xs_scenicspot','1.0.4','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('125','1','1','www.xsframe.cn','183.158.207.96','1722583276','xs_scenicspot','1.0.5','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('126','1','1','www.xsframe.cn','183.158.207.96','1722583570','xs_scenicspot','1.0.6','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('127','1','1','www.xsframe.cn','183.158.207.96','1722584345','xs_scenicspot','1.0.7','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('128','1','1','www.xsframe.cn','183.158.207.96','1722585236','xs_scenicspot','1.0.8','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('129','1','1','www.xsframe.cn','183.158.207.96','1722585758','xs_scenicspot','1.0.9','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('130','1','1','www.xsframe.cn','183.158.207.96','1722586283','xs_scenicspot','1.1.0','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('131','1','1','www.xsframe.cn','183.158.207.96','1722586921','xs_scenicspot','1.1.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('132','1','2','ceshi.xsframe.cn','125.119.222.93','1722777579','xs_wxapp','1.0.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('133','1','1','www.xsframe.cn','125.119.222.93','1722851882','xs_scenicspot','1.1.2','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('134','1','1','www.xsframe.cn','125.119.222.93','1722852004','easy_shop','1.0.3','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('135','1','1','www.xsframe.cn','125.119.222.93','1722852034','easy_shop','1.0.3','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('136','1','1','www.xsframe.cn','125.119.222.93','1722852115','easy_shop','1.0.3','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('137','1','1','www.xsframe.cn','125.119.222.93','1722910375','xs_scenicspot','1.1.3','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('138','1','1','www.xsframe.cn','125.119.222.93','1722910694','xs_scenicspot','1.1.4','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('139','1','1','www.xsframe.cn','125.119.222.93','1722911948','xs_scenicspot','1.1.5','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('140','1','1','www.xsframe.cn','125.119.222.93','1722930143','small_shop','1.0.2','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('141','1','1','www.xsframe.cn','125.119.222.93','1722937060','small_shop','1.0.3','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('142','1','1','www.xsframe.cn','125.119.222.93','1722939504','small_shop','1.0.3','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('143','1','1','www.xsframe.cn','125.119.222.93','1722940592','small_shop','1.0.4','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('144','1','2','ceshi.xsframe.cn','183.238.15.146','1722950384','small_shop','1.0.4','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('145','1','1','www.xsframe.cn','183.238.15.146','1722960215','xs_youpai','1.0.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('146','1','1','www.xsframe.cn','125.119.222.93','1722997011','small_shop','1.0.5','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('147','1','1','www.xsframe.cn','125.121.4.119','1723082927','wx_menu','1.0.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('148','1','2','ceshi.xsframe.cn','115.198.54.167','1723165630','small_shop','1.0.5','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('149','1','2','ceshi.xsframe.cn','115.198.54.167','1723169970','small_shop','1.0.6','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('150','1','10','localhost.xsframe.com','127.0.0.1','1723624759','xs_sms','1.0.1','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('151','1','2','ceshi.xsframe.cn','115.198.56.88','1724055413','small_shop','1.0.7','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('152','1','2','ceshi.xsframe.cn','115.198.56.88','1724120678','small_shop','1.0.8','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('153','1','1','www.xsframe.cn','115.198.56.88','1724124428','xs_contribute','1.0.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('154','1','1','www.xsframe.cn','115.198.57.186','1724138707','xs_contribute','1.0.2','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('155','1','1','www.xsframe.cn','115.198.57.186','1724140006','xs_contribute','1.0.3','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('156','1','1','www.xsframe.cn','115.198.57.186','1724140605','xs_contribute','1.0.4','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('157','1','1','iabc.cicaf.com','115.198.57.186','1724221818','xs_sms','1.0.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('158','1','1','www.xsframe.cn','115.198.57.186','1724376636','xs_contribute','1.0.5','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('159','1','1','www.xsframe.cn','115.198.203.142','1724401966','xs_caller','1.0.2','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('160','1','2','ceshi.xsframe.cn','115.198.203.142','1724574517','xs_article','1.0.4','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('161','1','1','www.xsframe.cn','115.198.203.142','1724635088','xs_caller','1.0.3','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('162','1','1','www.xsframe.cn','122.231.243.46','1724923310','exchange_shop','1.0','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('163','1','1','www.xsframe.cn','122.233.235.174','1725271663','small_shop','1.0.8','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('164','1','1','www.xsframe.cn','122.233.235.174','1725271692','xs_article','1.0.4','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('165','1','1','www.xsframe.com','127.0.0.1','1725272005','xs_article','1.0.4','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('166','1','1','www.xsframe.com','127.0.0.1','1725272063','xs_article','1.0.4','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('167','1','1','www.xsframe.com','127.0.0.1','1725272077','xs_article','1.0.4','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('168','1','1','www.xsframe.com','127.0.0.1','1725272098','xs_article','1.0.4','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('169','1','1','www.xsframe.com','127.0.0.1','1725272115','xs_article','1.0.4','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('170','1','1','www.xsframe.com','127.0.0.1','1725272165','xs_article','1.0.4','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('171','1','1','www.xsframe.com','127.0.0.1','1725273500','xs_article','1.0.4','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('172','1','1','www.xsframe.com','127.0.0.1','1725273801','xs_article','1.0.4','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('173','1','1','mxp.le-3d.com','58.100.75.78','1725794914','xs_sms','1.0.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('174','1','17','jiurui.xsframe.cn','115.205.208.28','1726734545','vip_store','1.0.4','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('175','1','17','jiurui.xsframe.cn','115.205.208.28','1726735141','exchange_shop','1.0','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('176','1','17','jiurui.xsframe.cn','115.205.208.28','1726736002','xs_wxapp','1.0.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('177','1','17','jiurui.xsframe.cn','115.205.208.28','1726737410','xs_wechat','1.0.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('178','1','17','jiurui.xsframe.cn','115.205.208.28','1726740224','xs_wxpay','1.0.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('179','1','21','www.xs.com','127.0.0.1','1727349658','xs_wxpay','1.0.1','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('180','1','21','www.xs.com','127.0.0.1','1727349662','xs_wxapp','1.0.1','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('181','1','17','jiurui.xsframe.cn','125.120.86.242','1727529544','exchange_shop','1.2','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('182','1','17','jiurui.xsframe.cn','125.120.86.242','1727529568','exchange_shop','1.2','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('183','1','17','jiurui.xsframe.cn','125.120.86.242','1727529607','exchange_shop','1.2','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('184','1','17','jiurui.xsframe.cn','124.90.106.149','1727529642','exchange_shop','1.2','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('185','1','17','jiurui.xsframe.cn','124.90.106.149','1727529676','exchange_shop','1.2','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('186','1','17','jiurui.xsframe.cn','124.90.106.149','1727529767','exchange_shop','1.2','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('187','1','2','ceshi.xsframe.cn','122.224.203.146','1728491685','xs_industry','1.0.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('188','1','2','ceshi.xsframe.cn','124.240.42.66','1729096991','xs_industry','1.0.2','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('189','1','1','mxp.le-3d.com','115.192.35.52','1731565041','xs_microlecture','1.0.0','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('190','1','35','xs.01stack.cn','122.233.42.244','1731580208','exchange_shop','2.6','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('191','1','35','xs.01stack.cn','115.192.35.52','1731582010','exchange_shop','2.6','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('192','1','35','xs.01stack.cn','115.192.35.52','1731582047','exchange_shop','2.6','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('193','1','35','xs.01stack.cn','115.192.35.52','1731582087','exchange_shop','2.6','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('194','1','35','xs.01stack.cn','115.192.35.52','1731582172','exchange_shop','2.6','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('195','1','35','xs.01stack.cn','122.233.42.244','1731590881','exchange_shop','2.6','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('196','1','35','xs.01stack.cn','122.233.42.244','1731591396','exchange_shop','2.6','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('197','1','35','xs.01stack.cn','115.192.32.76','1731595604','exchange_shop','2.6','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('198','1','35','xs.01stack.cn','115.192.32.76','1731595991','exchange_shop','2.6','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('199','1','2','ceshi.xsframe.cn','115.192.32.76','1731599902','xs_industry','2.0','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('200','1','35','xs.01stack.cn','124.90.104.238','1731609293','xs_form','1.0.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('201','1','35','xs.01stack.cn','124.90.104.238','1731609623','exchange_shop','2.6','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('202','1','35','xs.01stack.cn','115.192.35.52','1731632896','exchange_shop','2.6','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('203','1','35','xs.01stack.cn','115.192.35.52','1731633087','exchange_shop','2.6','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('204','1','1','www.xsframe.com','127.0.0.1','1731633487','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('205','1','1','www.xsframe.com','127.0.0.1','1731633526','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('206','1','1','www.xsframe.com','127.0.0.1','1731633534','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('207','1','1','www.xsframe.com','127.0.0.1','1731633645','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('208','1','1','www.xsframe.com','127.0.0.1','1731633663','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('209','1','1','www.xsframe.com','127.0.0.1','1731633863','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('210','1','1','www.xsframe.com','127.0.0.1','1731633958','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('211','1','2','ceshi.xsframe.cn','115.192.35.52','1731634826','exchange_shop','2.6','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('212','1','35','xs.01stack.cn','115.192.35.52','1731635839','xs_wxpay','1.0.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('213','1','1','www.xsframe.com','127.0.0.1','1731649433','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('214','1','1','www.xsframe.com','127.0.0.1','1731649524','xs_article','1.0.4','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('215','1','1','www.xsframe.com','127.0.0.1','1731649529','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('216','1','1','www.xsframe.com','127.0.0.1','1731649628','xs_article','1.0.4','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('217','1','1','www.xsframe.com','127.0.0.1','1731649744','xs_article','1.0.4','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('218','1','1','www.xsframe.com','127.0.0.1','1731650188','xs_article','1.0.4','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('219','1','1','www.xsframe.com','127.0.0.1','1731650700','xs_article','1.0.4','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('220','1','1','www.xsframe.com','127.0.0.1','1731651314','xs_article','1.0.4','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('221','1','1','www.xsframe.com','127.0.0.1','1731651316','xs_article','1.0.4','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('222','1','1','www.xsframe.com','127.0.0.1','1731651317','xs_article','1.0.4','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('223','1','1','www.xsframe.com','127.0.0.1','1731651322','xs_article','1.0.4','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('224','1','1','www.xsframe.com','127.0.0.1','1731651339','xs_article','1.0.4','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('225','1','1','www.xsframe.com','127.0.0.1','1731651372','xs_article','1.0.4','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('226','1','1','www.xsframe.com','127.0.0.1','1731651514','xs_article','1.0.4','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('227','1','1','www.xsframe.com','127.0.0.1','1731651514','xs_article','1.0.4','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('228','1','1','www.xsframe.com','127.0.0.1','1731651532','xs_article','1.0.4','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('229','1','1','www.xsframe.com','127.0.0.1','1731651532','xs_article','1.0.4','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('230','1','1','www.xsframe.com','127.0.0.1','1731651566','xs_article','1.0.4','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('231','1','1','www.xsframe.com','127.0.0.1','1731651567','xs_article','1.0.4','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('232','1','1','www.xsframe.com','127.0.0.1','1731651571','xs_article','1.0.4','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('233','1','1','www.xsframe.com','127.0.0.1','1731651571','xs_article','1.0.4','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('234','1','1','www.xsframe.com','127.0.0.1','1731651775','xs_article','1.0.4','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('235','1','1','www.xsframe.com','127.0.0.1','1731651775','xs_article','1.0.4','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('236','1','1','www.xsframe.com','127.0.0.1','1731651792','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('237','1','1','www.xsframe.com','127.0.0.1','1731651792','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('238','1','1','www.xsframe.com','127.0.0.1','1731652443','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('239','1','1','www.xsframe.com','127.0.0.1','1731652473','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('240','1','1','www.xsframe.com','127.0.0.1','1731652503','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('241','1','1','www.xsframe.com','127.0.0.1','1731652533','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('242','1','1','www.xsframe.com','127.0.0.1','1731652534','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('243','1','1','www.xsframe.com','127.0.0.1','1731652563','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('244','1','1','www.xsframe.com','127.0.0.1','1731652564','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('245','1','1','www.xsframe.com','127.0.0.1','1731652593','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('246','1','1','www.xsframe.com','127.0.0.1','1731652594','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('247','1','1','www.xsframe.com','127.0.0.1','1731652623','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('248','1','1','www.xsframe.com','127.0.0.1','1731652624','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('249','1','1','www.xsframe.com','127.0.0.1','1731652653','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('250','1','1','www.xsframe.com','127.0.0.1','1731652654','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('251','1','1','www.xsframe.com','127.0.0.1','1731652683','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('252','1','1','www.xsframe.com','127.0.0.1','1731652684','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('253','1','1','www.xsframe.com','127.0.0.1','1731652684','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('254','1','1','www.xsframe.com','127.0.0.1','1731652713','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('255','1','1','www.xsframe.com','127.0.0.1','1731652714','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('256','1','1','www.xsframe.com','127.0.0.1','1731652715','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('257','1','1','www.xsframe.com','127.0.0.1','1731652744','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('258','1','1','www.xsframe.com','127.0.0.1','1731652745','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('259','1','1','www.xsframe.com','127.0.0.1','1731652775','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('260','1','1','www.xsframe.com','127.0.0.1','1731652775','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('261','1','1','www.xsframe.com','127.0.0.1','1731652805','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('262','1','1','www.xsframe.com','127.0.0.1','1731652805','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('263','1','1','www.xsframe.com','127.0.0.1','1731652834','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('264','1','1','www.xsframe.com','127.0.0.1','1731652865','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('265','1','1','www.xsframe.com','127.0.0.1','1731652894','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('266','1','1','www.xsframe.com','127.0.0.1','1731652924','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('267','1','1','www.xsframe.com','127.0.0.1','1731652954','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('268','1','35','xs.01stack.cn','115.192.35.52','1731654762','exchange_shop','2.6','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('269','1','35','xs.01stack.cn','115.192.35.52','1731654792','exchange_shop','2.6','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('270','1','35','xs.01stack.cn','115.192.35.52','1731654822','exchange_shop','2.6','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('271','1','35','xs.01stack.cn','115.192.35.52','1731654852','exchange_shop','2.6','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('272','1','35','xs.01stack.cn','115.192.35.52','1731654853','exchange_shop','2.6','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('273','1','35','xs.01stack.cn','115.192.35.52','1731654882','exchange_shop','2.6','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('274','1','35','xs.01stack.cn','115.192.35.52','1731654883','exchange_shop','2.6','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('275','1','35','xs.01stack.cn','115.192.35.52','1731654912','exchange_shop','2.6','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('276','1','35','xs.01stack.cn','115.192.35.52','1731654913','exchange_shop','2.6','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('277','1','35','xs.01stack.cn','115.192.35.52','1731654943','exchange_shop','2.6','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('278','1','35','xs.01stack.cn','115.192.35.52','1731654972','exchange_shop','2.6','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('279','1','1','www.xsframe.com','127.0.0.1','1731654975','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('280','1','35','xs.01stack.cn','115.192.35.52','1731655003','exchange_shop','2.6','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('281','1','1','www.xsframe.com','127.0.0.1','1731655004','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('282','1','1','www.xsframe.com','127.0.0.1','1731655005','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('283','1','1','www.xsframe.com','127.0.0.1','1731655035','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('284','1','1','www.xsframe.com','127.0.0.1','1731655039','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('285','1','1','www.xsframe.com','127.0.0.1','1731655065','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('286','1','1','www.xsframe.com','127.0.0.1','1731655095','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('287','1','1','www.xsframe.com','127.0.0.1','1731655125','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('288','1','1','www.xsframe.com','127.0.0.1','1731655155','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('289','1','1','www.xsframe.com','127.0.0.1','1731655185','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('290','1','1','www.xsframe.com','127.0.0.1','1731655215','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('291','1','1','www.xsframe.com','127.0.0.1','1731655246','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('292','1','1','www.xsframe.com','127.0.0.1','1731655961','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('293','1','1','www.xsframe.com','127.0.0.1','1731656047','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('294','1','1','www.xsframe.com','127.0.0.1','1731656163','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('295','1','1','www.xsframe.com','127.0.0.1','1731656165','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('296','1','35','xs.01stack.cn','115.192.35.52','1731656411','exchange_shop','2.6','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('297','1','35','xs.01stack.cn','115.192.35.52','1731656412','exchange_shop','2.6','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('298','1','1','www.xsframe.com','127.0.0.1','1731664392','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('299','1','1','www.xsframe.com','127.0.0.1','1731664393','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('300','1','35','xs.01stack.cn','115.192.35.52','1731670125','xs_smtp','1.0.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('301','1','35','xs.01stack.cn','115.192.35.52','1731670125','xs_smtp','1.0.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('302','1','19','demo.com','127.0.0.1','1731722240','xs_form','1.0.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('303','1','19','demo.com','127.0.0.1','1731722240','xs_form','1.0.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('304','1','2','ceshi.xsframe.cn','125.120.80.161','1733123551','exchange_shop','2.6','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('305','1','2','ceshi.xsframe.cn','125.120.80.161','1733123551','exchange_shop','2.6','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('306','1','13','xstest.baguatan.cn','125.120.80.161','1733218593','xs_mind','1.0.1','7.4.30','0');
INSERT INTO `ims_cloud_app_log` VALUES('307','1','13','xstest.baguatan.cn','125.120.80.161','1733218593','xs_mind','1.0.1','7.4.30','0');
INSERT INTO `ims_cloud_app_log` VALUES('308','1','37','fanchenyang.xsframe.cn','125.120.80.161','1733389422','xs_mind','1.0.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('309','1','37','fanchenyang.xsframe.cn','125.120.80.161','1733389422','xs_mind','1.0.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('310','1','37','fanchenyang.xsframe.cn','125.120.80.161','1733389615','xs_alipay','1.0.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('311','1','37','fanchenyang.xsframe.cn','125.120.80.161','1733389615','xs_alipay','1.0.1','7.4.33','0');
INSERT INTO `ims_cloud_app_log` VALUES('312','1','1','www.xsframe.com','127.0.0.1','1734510311','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('313','1','1','www.xsframe.com','127.0.0.1','1734510331','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('314','1','1','www.xsframe.com','127.0.0.1','1734510356','exchange_shop','2.6','7.4.3','0');
INSERT INTO `ims_cloud_app_log` VALUES('315','1','1','www.xsframe.com','127.0.0.1','1734510356','exchange_shop','2.6','7.4.3','0');

DROP TABLE IF EXISTS `ims_cloud_app_version`;
CREATE TABLE `ims_cloud_app_version` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) NOT NULL DEFAULT '0',
  `identifier` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '应用唯一标识',
  `version` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '应用版本号（1.0.1）',
  `content` text COLLATE utf8_unicode_ci NOT NULL COMMENT '更新内容（富文本支持换行）',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否上架 0否 1是',
  `createtime` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除 0否 1是',
  `examine_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否审核 0否 1已通过 2未通过',
  `examine_time` int(11) NOT NULL DEFAULT '0' COMMENT '审核时间',
  `examine_remark` varchar(150) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '审核备注信息',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=153 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='应用版本日志信息';

INSERT INTO `ims_cloud_app_version` VALUES('23','1','xs_council','1.0.3','<p>修复部分已知问题</p><p>提升系统稳定性</p>','1','1718780360','0','1','1718780460','');
INSERT INTO `ims_cloud_app_version` VALUES('22','1','xs_article','1.0.1','','1','1718352248','0','1','1718352363','');
INSERT INTO `ims_cloud_app_version` VALUES('21','1','xs_council','1.0.2','<p>更新了部分静态文件</p>','1','1718268921','0','1','1718268967','');
INSERT INTO `ims_cloud_app_version` VALUES('20','1','xs_council','1.0.1','','1','1718262812','0','1','1718263644','');
INSERT INTO `ims_cloud_app_version` VALUES('24','1','xs_council','1.0.4','<p>修复部分错误，</p><p>提升系统稳定性</p>','1','1718782338','0','1','1718782359','');
INSERT INTO `ims_cloud_app_version` VALUES('25','1','xs_article','1.0.2','<p>修复上拉图片不显示的问题</p>','1','1718786572','0','1','1718786632','');
INSERT INTO `ims_cloud_app_version` VALUES('26','1','xs_article','1.0.3','<p>优化访问入口，优化用户体验</p>','1','1718787752','0','1','1718787786','');
INSERT INTO `ims_cloud_app_version` VALUES('27','1','xs_council','1.0.5','<p>修复了已知问题</p><p>提升了使用体验</p>','1','1718790039','0','1','1718790073','');
INSERT INTO `ims_cloud_app_version` VALUES('28','1','xs_article','1.0.4','<p>优化用户体验</p>','1','1718848902','0','1','1718849260','');
INSERT INTO `ims_cloud_app_version` VALUES('37','1','xs_council','1.0.6','<p>ceshi</p>','1','1718865293','0','1','1718879126','');
INSERT INTO `ims_cloud_app_version` VALUES('38','1','xs_activity','1.0.1','','1','1718879055','0','1','1718933326','');
INSERT INTO `ims_cloud_app_version` VALUES('39','1','xs_activity','1.0.2','<p>新版更新</p>','1','1718933362','0','1','1718933372','');
INSERT INTO `ims_cloud_app_version` VALUES('40','1','xs_country','1.0.2','<p>修复了分享错误</p><p>优化了应用性能</p>','1','1718947531','0','1','1718947571','');
INSERT INTO `ims_cloud_app_version` VALUES('41','1','xs_question','1.0.1','','1','1719551047','0','1','1719551083','');
INSERT INTO `ims_cloud_app_version` VALUES('42','1','xs_question','1.0.2','<p>修复部分系统问题</p><p>优化用户体验</p>','1','1719554415','0','1','1719554438','');
INSERT INTO `ims_cloud_app_version` VALUES('43','1','xs_council','1.0.7','<p>修复了部分系统问题</p><p>优化了用户体验</p>','1','1719555242','0','1','1719555261','');
INSERT INTO `ims_cloud_app_version` VALUES('44','1','xs_activity','1.0.3','<p>修复了系列问题</p><p>提升了系统稳定性</p>','1','1719556227','0','1','1719556319','');
INSERT INTO `ims_cloud_app_version` VALUES('45','1','xs_question','1.0.3','<p>新增用户统计</p><p>优化显示</p>','1','1719558234','0','1','1719558248','');
INSERT INTO `ims_cloud_app_version` VALUES('46','1','xs_courtship','1.0.1','','1','1719826765','0','1','1719826829','');
INSERT INTO `ims_cloud_app_version` VALUES('47','1','xs_question','2.0.1','<p>提升系统稳定性</p>','1','1719907383','0','1','1719907403','');
INSERT INTO `ims_cloud_app_version` VALUES('48','1','vip_store','1.0.1','','1','1720173349','0','1','1720174143','');
INSERT INTO `ims_cloud_app_version` VALUES('49','1','vip_store','1.0.2','<p>优化了部分系统问题</p>','1','1720174095','0','1','1720174143','');
INSERT INTO `ims_cloud_app_version` VALUES('50','1','vip_store','1.0.3','<p>修复跳转逻辑</p><p>优化后台管理</p>','1','1720175461','0','1','1720175505','');
INSERT INTO `ims_cloud_app_version` VALUES('51','1','vip_store','1.0.4','<p>优化了后台管理界面</p>','1','1720416848','0','1','1720416877','');
INSERT INTO `ims_cloud_app_version` VALUES('52','1','xs_couponcheck','1.0.1','','1','1720424586','0','1','1720424709','');
INSERT INTO `ims_cloud_app_version` VALUES('53','1','xs_couponcheck','1.0.2','<p>新增后台访问入口</p>','1','1720426174','0','1','1720426383','');
INSERT INTO `ims_cloud_app_version` VALUES('54','1','xs_couponcheck','1.0.3','<p>优化H5入口<br/></p>','1','1720428231','0','1','1720428388','');
INSERT INTO `ims_cloud_app_version` VALUES('55','1','xs_couponcheck','1.0.4','<p>入口问题优化</p>','1','1720428706','0','1','1720428742','');
INSERT INTO `ims_cloud_app_version` VALUES('56','1','xs_couponcheck','1.0.5','<p>入口问题优化</p>','1','1720429232','0','1','1720429253','');
INSERT INTO `ims_cloud_app_version` VALUES('57','1','xs_couponcheck','1.0.6','<p>前端验证码优化</p>','1','1720430839','0','1','1720430863','');
INSERT INTO `ims_cloud_app_version` VALUES('58','1','xs_couponcheck','1.0.7','','1','1720434018','0','1','1720434030','');
INSERT INTO `ims_cloud_app_version` VALUES('59','1','xs_couponcheck','1.0.8','','1','1720434805','0','1','1720434829','');
INSERT INTO `ims_cloud_app_version` VALUES('60','1','xs_couponcheck','1.0.9','','1','1720437039','0','1','1720437070','');
INSERT INTO `ims_cloud_app_version` VALUES('61','1','xs_couponcheck','1.1.0','','1','1720437463','0','1','1720437470','');
INSERT INTO `ims_cloud_app_version` VALUES('62','1','xs_couponcheck','1.1.1','','1','1720492550','0','1','1720492657','');
INSERT INTO `ims_cloud_app_version` VALUES('63','1','xs_website','2.0','','1','1720681132','0','1','1720681154','');
INSERT INTO `ims_cloud_app_version` VALUES('64','1','xs_website','2.1','<p>优化了后台管理</p><p>提升了系统稳定性</p>','1','1720747033','0','1','1720747100','');
INSERT INTO `ims_cloud_app_version` VALUES('65','1','xs_website','2.2','<p>优化后台显示</p><p>优化接口效率</p>','1','1720748235','0','1','1720748254','');
INSERT INTO `ims_cloud_app_version` VALUES('66','1','xs_website','2.3','<p>修复了前端用户头像上传逻辑</p>','1','1721030209','0','1','1721030246','');
INSERT INTO `ims_cloud_app_version` VALUES('67','1','xs_luck','1.0.1','','1','1721095369','0','1','1721096508','');
INSERT INTO `ims_cloud_app_version` VALUES('68','1','xs_station','1.0.0','','1','1721097431','0','1','1721097448','');
INSERT INTO `ims_cloud_app_version` VALUES('71','1','xs_luck','1.0.2','<p>优化了系统</p><p>提升了系统稳定性</p>','1','1721109518','0','1','1721109527','');
INSERT INTO `ims_cloud_app_version` VALUES('73','1','xs_luck','1.0.3','<p>优化了用户体验</p><p>提升了系统稳定性</p>','1','1721111096','0','1','1721111108','');
INSERT INTO `ims_cloud_app_version` VALUES('74','1','xs_alipay','1.0.1','<p>管理商户支付宝的配置项及参数信息</p>','1','1721210445','0','1','1721210837','');
INSERT INTO `ims_cloud_app_version` VALUES('75','1','xs_sms','1.0.1','<p>管理商户短信配置的配置项及参数信息</p>','1','1721210494','0','1','1721210835','');
INSERT INTO `ims_cloud_app_version` VALUES('76','1','xs_smtp','1.0.1','<p>管理商户邮件的配置项及参数信息</p>','1','1721210587','0','1','1721210832','');
INSERT INTO `ims_cloud_app_version` VALUES('77','1','xs_wechat','1.0.1','<p>管理商户公众号的配置项及参数信息</p>','1','1721210655','0','1','1721210828','');
INSERT INTO `ims_cloud_app_version` VALUES('78','1','xs_weixin','1.0.1','<p>管理商户企业微信的配置项及参数信息</p>','1','1721210699','0','1','1721210826','');
INSERT INTO `ims_cloud_app_version` VALUES('79','1','xs_wxapp','1.0.1','<p>管理商户微信小程序的配置项及参数信息</p>','1','1721210751','0','1','1721210825','');
INSERT INTO `ims_cloud_app_version` VALUES('80','1','xs_wxpay','1.0.1','<p>管理商户微信支付的配置项及参数信息</p>','1','1721210796','0','1','1721210822','');
INSERT INTO `ims_cloud_app_version` VALUES('81','1','xs_scoreconsume','1.0.1','','1','1721211657','0','1','1721211702','');
INSERT INTO `ims_cloud_app_version` VALUES('82','1','xs_scoreconsume','1.0.2','<p>优化下单扣款后的界面显示</p>','1','1721359499','0','1','1721359520','');
INSERT INTO `ims_cloud_app_version` VALUES('83','1','official_website','1.0.1','','1','1721361365','0','1','1721361410','');
INSERT INTO `ims_cloud_app_version` VALUES('84','1','xs_attachment','1.0.1','<p>目前支持阿里云oss的上传管理（整站统配都可以使用）</p>','1','1721370893','0','1','1721370929','');
INSERT INTO `ims_cloud_app_version` VALUES('85','1','xs_scoreconsume','1.0.3','','1','1721382063','0','1','1721382146','');
INSERT INTO `ims_cloud_app_version` VALUES('86','1','official_website','1.1','<p>更新了后台设置问题</p>','1','1721697872','0','1','1721697895','');
INSERT INTO `ims_cloud_app_version` VALUES('87','1','official_website','1.2','<p>更新了前台显示</p>','1','1721817463','0','1','1721817498','');
INSERT INTO `ims_cloud_app_version` VALUES('88','1','xs_mind','1.0.1','','1','1721873367','0','1','1721873420','');
INSERT INTO `ims_cloud_app_version` VALUES('89','1','xs_form','1.0.1','<p>上架表单组件</p>','1','1721983612','0','1','1721983900','');
INSERT INTO `ims_cloud_app_version` VALUES('90','1','xs_shortmovies','1.0.1','','1','1722246121','0','1','1722246155','');
INSERT INTO `ims_cloud_app_version` VALUES('91','1','xs_shortmovies','1.0.2','<p>优化了内容显示</p>','1','1722246829','0','1','1722246850','');
INSERT INTO `ims_cloud_app_version` VALUES('92','1','xs_shortmovies','1.1','<p>更新优化</p>','1','1722309803','0','1','1722309827','');
INSERT INTO `ims_cloud_app_version` VALUES('93','1','xs_qiniu','1.0.1','','1','1722569321','0','1','1722569356','');
INSERT INTO `ims_cloud_app_version` VALUES('94','1','xs_youpai','1.0.1','','1','1722577378','0','1','1722577404','');
INSERT INTO `ims_cloud_app_version` VALUES('95','1','xs_scenicspot','1.0.1','','1','1722579282','0','1','1722579350','');
INSERT INTO `ims_cloud_app_version` VALUES('96','1','xs_scenicspot','1.0.2','','1','1722582064','0','1','1722582080','');
INSERT INTO `ims_cloud_app_version` VALUES('97','1','xs_scenicspot','1.0.3','','1','1722582330','0','1','1722582335','');
INSERT INTO `ims_cloud_app_version` VALUES('98','1','xs_scenicspot','1.0.4','','1','1722582635','0','1','1722582640','');
INSERT INTO `ims_cloud_app_version` VALUES('99','1','xs_scenicspot','1.0.5','','1','1722583262','0','1','1722583269','');
INSERT INTO `ims_cloud_app_version` VALUES('100','1','xs_scenicspot','1.0.6','','1','1722583562','0','1','1722583566','');
INSERT INTO `ims_cloud_app_version` VALUES('101','1','xs_scenicspot','1.0.7','','1','1722584335','0','1','1722584340','');
INSERT INTO `ims_cloud_app_version` VALUES('102','1','xs_scenicspot','1.0.8','','1','1722585224','0','1','1722585231','');
INSERT INTO `ims_cloud_app_version` VALUES('103','1','xs_scenicspot','1.0.9','','1','1722585749','0','1','1722585753','');
INSERT INTO `ims_cloud_app_version` VALUES('104','1','xs_scenicspot','1.1.0','','1','1722586275','0','1','1722586280','');
INSERT INTO `ims_cloud_app_version` VALUES('105','1','xs_scenicspot','1.1.1','','1','1722586904','0','1','1722586914','');
INSERT INTO `ims_cloud_app_version` VALUES('106','1','easy_shop','1.0.1','上架极简商城','1','1722586904','0','1','1722586914','');
INSERT INTO `ims_cloud_app_version` VALUES('107','1','easy_shop','1.0.2','<p>优化用户体验</p>','1','1722851307','0','1','1722851343','');
INSERT INTO `ims_cloud_app_version` VALUES('108','1','xs_scenicspot','1.1.2','','1','1722851823','0','1','1722851854','');
INSERT INTO `ims_cloud_app_version` VALUES('109','1','easy_shop','1.0.3','<p>提升系统稳定性</p><p>优化用户体验</p>','1','1722851909','0','1','1722851968','');
INSERT INTO `ims_cloud_app_version` VALUES('110','1','xs_scenicspot','1.1.3','','1','1722910325','0','1','1722910364','');
INSERT INTO `ims_cloud_app_version` VALUES('111','1','xs_scenicspot','1.1.4','','1','1722910683','0','1','1722910688','');
INSERT INTO `ims_cloud_app_version` VALUES('112','1','xs_scenicspot','1.1.5','','1','1722911933','0','1','1722911940','');
INSERT INTO `ims_cloud_app_version` VALUES('113','1','small_shop','1.0.2','','1','1722929804','0','1','1722930077','');
INSERT INTO `ims_cloud_app_version` VALUES('114','1','small_shop','1.0.3','<p>优化了订单支付流程</p>','1','1722937030','0','1','1722937050','');
INSERT INTO `ims_cloud_app_version` VALUES('115','1','small_shop','1.0.4','<p>优化了支付流程</p>','1','1722940564','0','1','1722940584','');
INSERT INTO `ims_cloud_app_version` VALUES('116','1','small_shop','1.0.5','<p>优化了前端显示</p>','1','1722996981','0','1','1722997001','');
INSERT INTO `ims_cloud_app_version` VALUES('117','1','wx_menu','1.0.1','','1','1723082524','0','1','1723082567','');
INSERT INTO `ims_cloud_app_version` VALUES('118','1','small_shop','1.0.6','<p>优化了小程序包的大小</p>','1','1723169923','0','1','1723169957','');
INSERT INTO `ims_cloud_app_version` VALUES('119','1','xs_caller','1.0.0','','1','1724039301','0','1','1724049947','');
INSERT INTO `ims_cloud_app_version` VALUES('120','1','xs_caller','1.0.1','','1','1724050072','0','1','1724070593','');
INSERT INTO `ims_cloud_app_version` VALUES('121','1','small_shop','1.0.7','<p>增加配置</p>','1','1724055378','0','1','1724055401','');
INSERT INTO `ims_cloud_app_version` VALUES('122','1','small_shop','1.0.8','<p>优化了系统稳定性</p>','1','1724120639','0','1','1724120666','');
INSERT INTO `ims_cloud_app_version` VALUES('123','1','xs_contribute','1.0.1','','1','1724123836','0','1','1724123914','');
INSERT INTO `ims_cloud_app_version` VALUES('124','1','xs_contribute','1.0.2','<p>优化登录相关问题</p>','1','1724138647','0','1','1724138692','');
INSERT INTO `ims_cloud_app_version` VALUES('125','1','xs_contribute','1.0.3','<p>发布帖子优化</p>','1','1724139959','0','1','1724139995','');
INSERT INTO `ims_cloud_app_version` VALUES('126','1','xs_contribute','1.0.4','<p>优化发布帖子</p>','1','1724140577','0','1','1724140595','');
INSERT INTO `ims_cloud_app_version` VALUES('127','1','xs_contribute','1.0.5','<p>修复部分显示问题</p>','1','1724376598','0','1','1724376627','');
INSERT INTO `ims_cloud_app_version` VALUES('128','1','xs_caller','1.0.2','<p>完善首页banner和标题的自定义</p>','1','1724400655','0','1','1724400996','');
INSERT INTO `ims_cloud_app_version` VALUES('129','1','xs_caller','1.0.3','<p>优化小程序预约界面</p>','1','1724634956','0','1','1724634991','');
INSERT INTO `ims_cloud_app_version` VALUES('130','1','exchange_shop','1.0','','1','1724738836','0','1','1724738870','');
INSERT INTO `ims_cloud_app_version` VALUES('131','1','xs_unionpay','1.0.1','<p><img src=\"https://www.xsframe.cn/attachment/images/1/xs_developer/2024/09/ep8tX3iu8HU3IdzT.jpg\" width=\"100%\" alt=\"1725615656485.jpg\"/></p>','1','1725615709','0','1','1725615770','');
INSERT INTO `ims_cloud_app_version` VALUES('132','1','exchange_shop','1.2','<p>引入多门店</p><p>新增会员模块</p><p>新增优惠券模块</p>','1','1726827325','0','1','1726827340','');
INSERT INTO `ims_cloud_app_version` VALUES('133','1','jt_mail','1.0.1','<p>智能邮筒应用上架</p>','1','1727088783','0','1','1727088816','');
INSERT INTO `ims_cloud_app_version` VALUES('134','1','xs_shouqianba','1.0.1','','1','1727259746','0','1','1727259815','');
INSERT INTO `ims_cloud_app_version` VALUES('135','1','xs_unionpay','2.0.1','<p>重构支付逻辑，只需简单几行代码即可接入银联商务支付</p>','1','1727434412','0','1','1727434563','');
INSERT INTO `ims_cloud_app_version` VALUES('136','1','exchange_shop','2.0','<p>升级用户体验</p><p>增加商户</p><p>增加优惠券</p><p>增加直播预告<br/></p><p>增加活动预告</p><p>增加积分兑换单页<br/></p><p>升级页面模板更漂亮了</p><p>更多更新等你发觉</p>','1','1727710683','0','1','1727710706','');
INSERT INTO `ims_cloud_app_version` VALUES('137','1','xs_industry','1.0.1','','1','1728459770','0','1','1728459820','');
INSERT INTO `ims_cloud_app_version` VALUES('138','1','xs_industry','1.0.2','<p>新增体验模式</p>','1','1728619330','0','1','1728619350','');
INSERT INTO `ims_cloud_app_version` VALUES('139','1','exchange_shop','2.1','<ol class=\" list-paddingleft-2\" style=\"list-style-type: decimal;\"><li><p>门店功能优化<br/></p></li><li><p>新增余额支付，余额充值</p></li><li><p>分销功能上线</p></li><li><p>新增用户签到</p></li><li><p>新增用户积分记录，余额记录</p></li><li><p>优化了部分细节提升了系统稳定性<br/></p></li></ol>','1','1728712205','0','1','1728712248','');
INSERT INTO `ims_cloud_app_version` VALUES('140','1','exchange_shop','2.2','<p>更新了前端金刚圈显示</p><p>优化了分页加载</p><p>提升系统稳定性</p>','1','1728897033','0','1','1728897053','');
INSERT INTO `ims_cloud_app_version` VALUES('141','1','xs_douyin','1.0','','1','1728973523','0','1','1728973542','');
INSERT INTO `ims_cloud_app_version` VALUES('142','1','xs_douyin','1.1','<p>接入抖音支付</p>','1','1729136342','0','1','1729136372','');
INSERT INTO `ims_cloud_app_version` VALUES('143','1','exchange_shop','2.3','<p>优化前端显示</p>','1','1729240530','0','1','1729240551','');
INSERT INTO `ims_cloud_app_version` VALUES('144','1','xs_wxapp','1.0.2','<p>增加一键下载小程序源码功能</p>','1','1729678357','0','1','1729678398','');
INSERT INTO `ims_cloud_app_version` VALUES('145','1','exchange_shop','2.4','<p>新增试用，许愿池功能</p><p>余额充值新增卡券卡密充值</p><p>卡包功能上线</p><p>系统优化提升稳定性</p>','1','1730185198','0','1','1730185245','');
INSERT INTO `ims_cloud_app_version` VALUES('146','1','xs_douyin','1.2','<p>接入抖音支付，打通相关接口</p><p>优化了后台配置<br/></p>','1','1730187716','0','1','1730187735','');
INSERT INTO `ims_cloud_app_version` VALUES('147','1','small_shop','2.0','<p>增加抖音版本</p><p>接入抖音支付</p><p>提升系统稳定性</p>','1','1730192281','0','1','1730192303','');
INSERT INTO `ims_cloud_app_version` VALUES('148','1','exchange_shop','2.5','<ol class=\" list-paddingleft-2\" style=\"list-style-type: decimal;\"><li><p>增加云仓功能，用户打开云仓即可下载同步云仓商品</p></li><li><p>解决了一系列bug，提升了系统稳定性</p></li></ol><p><br/></p>','1','1730363836','0','1','1730363883','');
INSERT INTO `ims_cloud_app_version` VALUES('149','1','exchange_cloud','1.0','','1','1730427725','0','1','1730428092','');
INSERT INTO `ims_cloud_app_version` VALUES('150','1','xs_industry','2.0','<p>新增房源管理</p><p>优化前端显示</p>','1','1731031158','0','1','1731031179','');
INSERT INTO `ims_cloud_app_version` VALUES('151','1','xs_microlecture','1.0.0','','1','1731465271','0','1','1731465327','');
INSERT INTO `ims_cloud_app_version` VALUES('152','1','exchange_shop','2.6','<p>新增足迹，收藏</p><p>优化前端显示</p><p>修复了部分系统问题</p><p>提升了小程序稳定性</p>','1','1731567411','0','1','1731567434','');

DROP TABLE IF EXISTS `ims_cloud_frame_log`;
CREATE TABLE `ims_cloud_frame_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) DEFAULT '0' COMMENT '商户id',
  `mid` int(10) NOT NULL DEFAULT '0' COMMENT '下载用户',
  `host_url` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '主机域名',
  `host_ip` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '主机IP',
  `createtime` int(11) NOT NULL DEFAULT '0' COMMENT '下载时间',
  `version` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '下载版本',
  `php_version` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'php版本',
  `deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除 0否 1是',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=94 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='下载记录';

INSERT INTO `ims_cloud_frame_log` VALUES('1','1','1','www.xsframe.cn','115.199.171.240','1718121849','1.0.4','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('2','1','5','kaihua.baguatan.cn','60.163.236.44','1718265807','1.0.5','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('3','1','1','kaihua.baguatan.cn','60.163.236.44','1718266837','1.0.5','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('4','1','5','hslsh.souxue.cc','60.163.236.44','1718269366','1.0.5','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('5','1','5','www.xsframe.com','127.0.0.1','1718347687','1.0.5','7.4.3','0');
INSERT INTO `ims_cloud_frame_log` VALUES('6','1','1','ceshi.xsframe.cn','115.194.190.119','1718597278','1.0.5','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('7','1','5','hslsh.souxue.cc','115.194.190.119','1718675780','1.0.5','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('8','1','1','ceshi.xsframe.cn','58.101.41.60','1718817910','1.0.5','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('9','1','1','demo.xsframe.cn','125.120.228.151','1718848064','1.0.5','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('10','1','1','demo.xsframe.cn','125.120.228.151','1718851049','1.0.6','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('11','1','5','hslsh.souxue.cc','125.120.228.151','1718856064','1.0.6','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('12','1','5','kaihua.baguatan.cn','125.120.228.151','1718874758','1.0.6','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('13','1','5','kaihua.baguatan.cn','60.163.236.29','1719377241','1.0.6','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('14','1','1','ceshi.xsframe.cn','115.194.189.176','1720886640','1.0.7','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('15','1','5','www.xsframe.cn','125.120.224.219','1720939465','1.0.7','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('16','1','1','ceshi.xsframe.cn','125.120.224.219','1720961320','1.0.7','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('17','1','12','xs.wang1278.top','115.194.189.46','1721097263','1.0.7','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('18','1','2','ceshi.xsframe.cn','115.194.189.46','1721299666','1.0.8','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('19','1','5','kaihua.baguatan.cn','115.194.189.46','1721353934','1.0.8','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('20','1','2','ceshi.xsframe.cn','183.156.142.33','1721402419','1.0.8','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('21','1','5','www.xsframe.cn','36.27.86.159','1721569291','1.0.8','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('22','0','5','www.xsframe.cn','125.120.21.146','1721918354','1.0.9','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('23','0','2','ceshi.xsframe.cn','125.120.21.146','1721918380','1.0.9','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('24','0','2','ceshi.xsframe.cn','58.100.218.60','1721921824','1.0.9','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('25','0','2','ceshi.xsframe.cn','125.120.21.146','1722076453','1.0.10','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('26','0','5','hslsh.souxue.cc','36.27.86.183','1722217056','1.0.10','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('27','0','1','www.xsframe.cn','125.119.222.93','1722673983','1.0.10','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('28','0','2','ceshi.xsframe.cn','125.121.4.119','1723112151','1.0.11','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('29','0','2','ceshi.xsframe.cn','58.100.75.78','1723304819','1.0.11','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('30','0','2','ceshi.xsframe.cn','115.198.56.88','1723789737','1.0.12','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('31','0','5','hslsh.souxue.cc','115.198.56.88','1724046248','1.0.12','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('32','0','1','iabc.cicaf.com','115.198.57.186','1724221788','1.0.12','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('33','0','2','ceshi.xsframe.cn','125.120.63.141','1724333148','1.0.12','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('34','0','2','ceshi.xsframe.cn','125.120.63.141','1724333687','1.0.13','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('35','0','2','ceshi.xsframe.cn','58.100.75.78','1724333965','1.0.13','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('36','0','5','hslsh.souxue.cc','115.198.203.142','1724655896','1.0.13','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('37','0','1','www.xsframe.cn','122.233.235.174','1725118011','1.0.13','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('38','0','5','hslsh.souxue.cc','122.233.235.174','1725353884','1.0.14','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('39','0','2','ceshi.xsframe.cn','125.121.6.201','1725371280','1.0.14','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('40','0','2','ceshi.xsframe.cn','122.234.212.109','1725453550','1.0.15','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('41','0','2','ceshi.xsframe.cn','122.234.212.109','1725519040','1.0.16','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('42','0','5','hslsh.souxue.cc','122.234.212.109','1725521673','1.0.16','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('43','0','2','ceshi.xsframe.cn','122.234.212.109','1725611122','1.0.17','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('44','0','1','www.xsframe.com','127.0.0.1','1725613196','1.0.17','7.4.3','0');
INSERT INTO `ims_cloud_frame_log` VALUES('45','0','2','ceshi.xsframe.cn','211.90.237.147','1725613887','1.0.17','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('46','0','5','hslsh.souxue.cc','125.121.4.150','1725854055','1.0.17','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('47','0','1','www.xsframe.com','127.0.0.1','1726026966','1.0.18','7.4.3','0');
INSERT INTO `ims_cloud_frame_log` VALUES('48','0','2','ceshi.xsframe.cn','125.121.4.150','1726027367','1.0.18','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('49','0','5','hslsh.souxue.cc','125.121.4.150','1726044673','1.0.18','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('50','0','1','iabc.cicaf.com','125.121.184.212','1726130102','1.0.18','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('51','0','1','mxp.le-3d.com','125.121.184.212','1726308583','1.0.18','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('52','0','22','121.36.247.182','111.122.195.199','1726910942','1.0.18','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('53','0','2','ceshi.xsframe.cn','125.120.86.242','1727260158','1.0.20','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('54','0','1','www.xsframe.com','127.0.0.1','1727273077','1.0.20','7.4.3','0');
INSERT INTO `ims_cloud_frame_log` VALUES('55','0','5','hslsh.souxue.cc','125.120.86.242','1727314586','1.0.20','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('56','0','21','www.xs.com','127.0.0.1','1727398112','1.0.20','7.4.3','0');
INSERT INTO `ims_cloud_frame_log` VALUES('57','0','1','www.xsframe.cn','125.120.86.242','1727527937','1.0.20','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('58','0','17','www.llbao.top','124.90.106.149','1727530274','1.0.20','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('59','0','5','hslsh.souxue.cc','183.129.110.198','1727598770','1.0.21','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('60','0','17','www.llbao.top','36.27.84.110','1727613831','1.0.21','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('61','0','2','ceshi.xsframe.cn','36.27.84.110','1727614009','1.0.21','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('62','0','2','ceshi.xsframe.cn','124.90.106.149','1727615911','1.0.21','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('63','0','2','ceshi.xsframe.cn','171.223.92.145','1728626065','1.0.21','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('64','0','1','mxp.le-3d.com','60.177.143.79','1728891763','1.0.22','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('65','0','5','hslsh.souxue.cc','60.177.143.79','1728897342','1.0.22','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('66','0','2','ceshi.xsframe.cn','60.177.143.79','1728955003','1.0.22','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('67','0','25','funshop.xsframe.cn','125.121.198.96','1729234504','1.0.22','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('68','0','5','hslsh.souxue.cc','115.198.204.38','1729479768','1.0.23','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('69','0','25','funshop.xsframe.cn','115.198.204.38','1729481903','1.0.23','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('70','0','2','ceshi.xsframe.cn','122.224.203.146','1729603432','1.0.23','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('71','0','1','mxp.le-3d.com','122.231.167.131','1730449490','1.0.23','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('72','0','2','ceshi.xsframe.cn','218.89.245.78','1730468048','1.0.24','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('73','0','1','www.xsframe.com','127.0.0.1','1730477178','1.0.24','7.4.3','0');
INSERT INTO `ims_cloud_frame_log` VALUES('74','0','5','hslsh.souxue.cc','183.128.216.217','1730964123','1.0.24','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('75','0','25','funshop.xsframe.cn','183.128.216.217','1731057260','1.0.25','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('76','0','2','ceshi.xsframe.cn','218.72.51.129','1731162048','1.0.25','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('77','0','5','hslsh.souxue.cc','218.72.51.129','1731292776','1.0.25','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('78','0','1','mxp.le-3d.com','115.192.35.52','1731564223','1.0.25','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('79','0','35','xs.01stack.cn','115.192.32.76','1731595572','1.0.25','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('80','0','35','xs.01stack.cn','115.192.35.52','1731654736','1.0.25','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('81','0','2','ceshi.xsframe.cn','115.204.91.88','1731858361','1.0.25','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('82','0','2','ceshi.xsframe.cn','115.204.91.88','1731859519','1.0.26','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('83','0','5','hslsh.souxue.cc','115.204.91.88','1731908946','1.0.26','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('84','0','25','funshop.xsframe.cn','115.204.91.88','1731919026','1.0.26','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('85','0','1','www.xsframe.cn','115.204.91.88','1731937624','1.0.26','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('86','0','1','mxp.le-3d.com','115.204.92.31','1732183668','1.0.26','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('87','0','1','jiurui.xsframe.cn','115.204.92.31','1732261288','1.0.26','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('88','0','2','ceshi.xsframe.cn','115.204.92.31','1732451488','1.0.27','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('89','0','2','ceshi.xsframe.cn','115.198.201.107','1732549741','1.0.27','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('90','0','5','hslsh.souxue.cc','115.198.201.107','1732550160','1.0.27','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('91','0','25','funshop.xsframe.cn','115.216.55.231','1732679739','1.0.27','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('92','0','2','ceshi.xsframe.cn','125.120.80.161','1733123850','1.0.27','7.4.33','0');
INSERT INTO `ims_cloud_frame_log` VALUES('93','0','25','funshop.xsframe.cn','220.191.51.60','1733707643','1.0.28','7.4.33','0');

