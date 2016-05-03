-- phpMyAdmin SQL Dump
-- version 3.3.8.1
-- http://www.phpmyadmin.net
--
-- 主机: w.rdc.sae.sina.com.cn:3307
-- 生成日期: 2016 年 03 月 26 日 15:43
-- 服务器版本: 5.6.23
-- PHP 版本: 5.3.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- 数据库: `app_dtmao`
--

-- --------------------------------------------------------

--
-- 表的结构 `dm_courier`
--

CREATE TABLE IF NOT EXISTS `dm_courier` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `username` varchar(32) NOT NULL COMMENT '用户名',
  `mobile` char(15) NOT NULL COMMENT '用户手机',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '用户状态',
  `schoolid` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `mobile` (`mobile`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='配送员表' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `dm_follow`
--

CREATE TABLE IF NOT EXISTS `dm_follow` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `openid` char(28) NOT NULL COMMENT 'OpenId',
  `unionid` varchar(255) NOT NULL COMMENT 'UnionID',
  `nickname` varchar(255) NOT NULL DEFAULT '' COMMENT '昵称',
  `sex` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '性别',
  `city` varchar(100) NOT NULL DEFAULT '' COMMENT '城市',
  `province` varchar(100) NOT NULL DEFAULT '' COMMENT '省份',
  `country` varchar(100) NOT NULL DEFAULT '' COMMENT '国家',
  `headimgurl` varchar(255) NOT NULL DEFAULT '' COMMENT '头像',
  `subscribe_time` int(10) NOT NULL COMMENT '关注时间',
  `mobile` varchar(30) NOT NULL DEFAULT '' COMMENT '手机号',
  `status` tinyint(1) DEFAULT '0' COMMENT '用户状态',
  `mTime` int(10) NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `openid` (`openid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=48 ;

-- --------------------------------------------------------
--
-- 表的结构 `dm_log`
--

CREATE TABLE IF NOT EXISTS `dm_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cTime` int(11) DEFAULT NULL,
  `cTime_format` varchar(30) DEFAULT NULL,
  `data` text,
  `data_post` text,
  `action` varchar(30) DEFAULT NULL COMMENT '动作',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=436 ;

-- --------------------------------------------------------
--
-- 表的结构 `dm_member`
--

CREATE TABLE IF NOT EXISTS `dm_member` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `openid` varchar(28) NOT NULL COMMENT 'OpenId',
  `name` varchar(30) NOT NULL COMMENT '姓名',
  `addr` varchar(100) NOT NULL DEFAULT '' COMMENT '详细地址',
  `schoolid` int(10) NOT NULL COMMENT '学校',
  `phone` char(11) DEFAULT '' COMMENT '手机号码',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- 表的结构 `dm_order`
--

CREATE TABLE IF NOT EXISTS `dm_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `openid` char(28) NOT NULL COMMENT '用户id',
  `cost` decimal(10,0) NOT NULL DEFAULT '0' COMMENT '费用',
  `detail` text NOT NULL COMMENT '具体内容',
  `type` varchar(20) NOT NULL COMMENT '订单类型',
  `mTime` int(11) NOT NULL COMMENT '处理时间',
  `cTime` int(11) NOT NULL COMMENT '订单创建时间',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态',
  `note` text NOT NULL COMMENT '备注',
  `dealwith` text NOT NULL COMMENT '处理',
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `status` (`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------
--
-- 表的结构 `dm_school`
--

CREATE TABLE IF NOT EXISTS `dm_school` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `province` varchar(20) NOT NULL DEFAULT '' COMMENT '省份',
  `city` varchar(20) NOT NULL DEFAULT '' COMMENT '市',
  `school` varchar(50) NOT NULL DEFAULT '' COMMENT '学校',
  `district` varchar(20) NOT NULL DEFAULT '' COMMENT '区',
  `addrinfo` varchar(100) NOT NULL DEFAULT '' COMMENT '详细地址',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13 ;

-- --------------------------------------------------------

--
-- 表的结构 `dm_suggestions`
--

CREATE TABLE IF NOT EXISTS `dm_suggestions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `cTime` int(10) NOT NULL COMMENT '创建时间',
  `content` text NOT NULL COMMENT '内容',
  `openid` char(28) NOT NULL COMMENT 'openid',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `dm_user`
--

CREATE TABLE IF NOT EXISTS `dm_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `username` varchar(32) NOT NULL COMMENT '用户名',
  `realname` varchar(32) NOT NULL DEFAULT '' COMMENT '用户真实姓名',
  `password` char(32) NOT NULL COMMENT '密码',
  `mobile` char(15) NOT NULL DEFAULT '' COMMENT '用户手机',
  `last_login_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后登录时间',
  `last_login_ip` bigint(20) NOT NULL DEFAULT '0' COMMENT '最后登录IP',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '用户状态',
  `openid` varchar(100) NOT NULL DEFAULT '',
  `schoolid` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `mobile` (`mobile`),
  KEY `status` (`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='用户表' AUTO_INCREMENT=4 ;
