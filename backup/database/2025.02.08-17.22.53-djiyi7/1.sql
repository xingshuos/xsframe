-- -----------------------------
-- Veitool MySQL Data Transfer 
-- 
-- Host     : 127.0.0.1
-- Port     : 3306
-- Database : xsframe
-- 
-- Part : #1
-- Date : 2025-02-08 17:22:53
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

