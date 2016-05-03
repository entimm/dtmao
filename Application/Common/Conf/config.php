<?php
return array(
    /* 数据库配置 */
    'DB_TYPE'   => 'mysqli', // 数据库类型
    'DB_HOST'   => 'SAE_MYSQL_HOST_M', // 服务器地址
    'DB_NAME'   => 'SAE_MYSQL_DB', // 数据库名
    'DB_USER'   => 'SAE_MYSQL_USER', // 用户名
    'DB_PWD'    => 'SAE_MYSQL_PASS',  // 密码
    'DB_PORT'   => 'SAE_MYSQL_PORT', // 端口
    'DB_PREFIX' => 'dm_', // 数据库表前缀

    /* 调试配置 */
    'SHOW_PAGE_TRACE' => false,

    /* URL配置 */
    'URL_CASE_INSENSITIVE' => true, //默认false 表示URL区分大小写 true则表示不区分大小写
    'URL_MODEL'            => 2, //URL模式
    'VAR_URL_PARAMS'       => '', // PATHINFO URL参数变量
    'URL_PATHINFO_DEPR'    => '/', //PATHINFO URL分割符
);
