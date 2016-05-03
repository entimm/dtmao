<?php
/**
 * @Author: liulu72056
 * @Date:   2015-07-02 12:06:37
 * @Last Modified by:   liulu72056
 * @Last Modified time: 2015-07-12 11:56:49
 */

namespace Home\Controller;
use Think\Controller;

/**
 * 前台公共控制器
 * 为防止多分组Controller名称冲突，公共Controller名称统一使用分组名称
 */
class AuthController extends HomeController {

    //初始化操作
    function _initialize() {
        if (! is_login ()) {
            cookie ( '__forward__', $_SERVER ['REQUEST_URI'] );
            $url = U ( 'User/login' );
            redirect ( $url );
        }
    }

}
