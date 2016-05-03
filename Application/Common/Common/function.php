<?php
/**
 * @Author: liulu72056
 * @Date:   2015-06-30 23:31:00
 * @Last Modified by:   liulu72056
 * @Last Modified time: 2015-07-11 21:47:26
 */

// 防超时的file_get_contents改造函数
function wp_file_get_contents($url) {
    $context = stream_context_create ( array (
            'http' => array (
                    'timeout' => 30
            )
    ) ) // 超时时间，单位为秒

    ;

    return file_get_contents ( $url, 0, $context );
}

// 写日志
function addWeixinLog($data, $data_post = '', $action = '') {
    $log ['cTime'] = time ();
    $log ['cTime_format'] = date ( 'Y-m-d H:i:s', $log ['cTime'] );
    $log ['data'] = is_array ( $data ) ? json_encode ( $data ) : $data;
    $log ['data_post'] = is_array ( $data_post ) ? json_encode ( $data_post ) : $data_post;
    $log ['action'] = $action;
    M ( 'log' )->add ( $log );
}

/**
 * 系统非常规MD5加密方法
 * @param  string $str 要加密的字符串
 * @return string
 */
function think_md5($str, $key = 'Think'){
    return '' === $str ? '' : md5(sha1($str) . $key);
}

/**
 * 系统加密方法
 * @param string $data 要加密的字符串
 * @param string $key  加密密钥
 * @param int $expire  过期时间 (单位:秒)
 * @return string
 */
function think_encrypt($data, $key, $expire = 0) {
    $key  = md5($key);
    $data = base64_encode($data);
    $x    = 0;
    $len  = strlen($data);
    $l    = strlen($key);
    $char =  '';
    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) $x=0;
        $char  .= substr($key, $x, 1);
        $x++;
    }
    $str = sprintf('%010d', $expire ? $expire + time() : 0);
    for ($i = 0; $i < $len; $i++) {
        $str .= chr(ord(substr($data,$i,1)) + (ord(substr($char,$i,1)))%256);
    }
    return str_replace('=', '', base64_encode($str));
}

/**
 * 系统解密方法
 * @param string $data 要解密的字符串 （必须是think_encrypt方法加密的字符串）
 * @param string $key  加密密钥
 * @return string
 */
function think_decrypt($data, $key){
    $key    = md5($key);
    $x      = 0;
    $data   = base64_decode($data);
    $expire = substr($data, 0, 10);
    $data   = substr($data, 10);
    if($expire > 0 && $expire < time()) {
        return '';
    }
    $len  = strlen($data);
    $l    = strlen($key);
    $char = $str = '';
    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) $x = 0;
        $char  .= substr($key, $x, 1);
        $x++;
    }
    for ($i = 0; $i < $len; $i++) {
        if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
            $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
        }else{
            $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
        }
    }
    return base64_decode($str);
}

/**
 * 检测用户是否登录
 *
 * @return integer 0-未登录，大于0-当前登录用户ID
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function is_login() {
    $user = session ( 'user_auth' );
    if (empty ( $user )) {
        return 0;
    } else {
        return session ( 'user_auth_sign' ) == data_auth_sign ( $user ) ? $user ['uid'] : 0;
    }
}

/**
 * 数据签名认证
 *
 * @param array $data
 *          被认证的数据
 * @return string 签名
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function data_auth_sign($data) {
    // 数据类型检测
    if (! is_array ( $data )) {
        $data = ( array ) $data;
    }
    ksort ( $data ); // 排序
    $code = http_build_query ( $data ); // url编码并生成query字符串
    $sign = sha1 ( $code ); // 生成签名
    return $sign;
}
