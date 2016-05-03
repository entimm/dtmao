<?php
/**
 * @Author: liulu72056
 * @Date:   2015-07-02 12:06:37
 * @Last Modified by:   liulu72056
 * @Last Modified time: 2015-07-27 14:42:39
 */

// 应用入口文件

// 检测PHP环境
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');

error_reporting(E_ALL ^ E_NOTICE);
date_default_timezone_set ( 'PRC' );

//微信配置
define('TOKEN', 'dtmao');
define("APPID", '');
define("APPSECRET", '');
define('ENCODING_AES_KEY', "");

//腾讯地图key
define('QQMAPKEY', '');

//用户验证
define('AUTH_KEY', '');

//CODE_STATE
define('CODE_STATE', '');

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG', false);

// 绑定访问模块
define('BIND_MODULE','Home');

// 定义应用目录
define('APP_PATH', './Application/');

// 引入ThinkPHP入口文件
require './ThinkPHP/ThinkPHP.php';
