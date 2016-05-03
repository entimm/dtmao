<?php
/**
 * @Author: liulu72056
 * @Date:   2015-07-11 11:12:39
 * @Last Modified by:   liulu72056
 * @Last Modified time: 2015-07-18 19:05:52
 */
namespace Home\Controller;

/**
 * 用户控制器
 * 包括用户中心，用户登录及注册
 */
class UserController extends HomeController {
    /* 登录页面 */
    public function login($username = '', $password = '') {
        if ( is_login () ) {
            $this->redirect ( 'Order/index' );
        }
        if (IS_POST) {
            $User = D ( 'User' );
            $uid = $User->login ( $username, $password );
            if (0 < $uid) {
                $url = Cookie ( '__forward__' );
                if ($url) {
                    Cookie ( '__forward__', null );
                } else {
                    $url = U ( 'Order/index' );
                }
                $this->success ( '登录成功！', $url );
            } else { // 登录失败
                switch ($uid) {
                    case - 1 :
                        $error = '用户不存在或被禁用！';
                        break; // 系统级别禁用
                    case - 2 :
                        $error = '密码错误！';
                        break;
                    default :
                        $error = '未知错误！';
                        break; // 0-接口参数错误（调试阶段使用）
                }
                $this->error ( $error );
            }
        } else {
            $this->display ( 'login' );
        }
    }

    /* 退出登录 */
    public function logout() {
        if (is_login ()) {
            D ( 'User' )->logout ();
        }
        $this->redirect ( 'User/login' );
    }

    /**
     * 修改密码提交
     *
     * @author huajie <banhuajie@163.com>
     */
    public function profile() {
        if (! is_login ()) {
            $this->error ( '您还没有登录', U ( 'User/login' ) );
        }
        if (IS_POST) {
            // 获取参数
            $uid = is_login ();
            $password = I ( 'post.oldpwd' );
            $repassword = I ( 'post.renewpwd' );
            $data ['password'] = I ( 'post.newpwd' );
            empty ( $password ) && $this->error ( '请输入原密码' );
            empty ( $data ['password'] ) && $this->error ( '请输入新密码' );
            empty ( $repassword ) && $this->error ( '请输入确认密码' );

            if ($data ['password'] !== $repassword) {
                $this->error ( '您输入的新密码与确认密码不一致' );
            }

            $User = D ( 'User' );
            $res = $User->updateInfo ( $uid, $password, $data );
            if ($res ['status']) {
                D ( 'User' )->logout ();
                $this->success ( '修改密码成功！' , U ( 'Order/index' ) );
            } else {
                $this->error ( $res ['info'] );
            }
        } else {
            $this->display ();
        }
    }

    /* 注册页面 */
    public function register($username = '', $password = '', $mobile = '') {
        $User = D ( 'User' );
        $uid = $User->register ( $username, $password, $mobile );
        if (0 < $uid) {
            $this->success ( '注册成功，请登录', U ( 'login' ) );
        } else {
            $this->error ( $this->showRegError ( $uid ) );
        }
    }

    public function follows($p = 1) {
        if (! is_login ()) {
            cookie ( '__forward__', $_SERVER ['REQUEST_URI'] );
            $url = U ( 'User/login' );
            redirect ( $url );
        }

        $Follow = M ( 'follow' );
        $follows = $Follow->field('nickname, sex, headimgurl, subscribe_time, city, province')
                          ->where('status=1')
                          ->page($p.',50')
                          ->order('id desc')
                          ->select();

        $count = $Follow->where('status=1')->count();
        $Page  = new \Think\Page($count,50);
        $show  = $Page->show();
        $this->assign('page', $show);
        $this->assign('count', $count);

        $this->assign('follows', $follows);
        $this->display();
    }

    /**
     * 获取用户注册错误信息
     *
     * @param integer $code
     *          错误编码
     * @return string 错误信息
     */
    private function showRegError($code = 0) {
        switch ($code) {
            case - 1 :
                $error = '用户名长度必须在16个字符以内！';
                break;
            case - 2 :
                $error = '用户名被禁止注册！';
                break;
            case - 3 :
                $error = '用户名被占用！';
                break;
            case - 4 :
                $error = '密码长度必须在6-30个字符之间！';
                break;
            case - 5 :
                $error = '邮箱格式不正确！';
                break;
            case - 6 :
                $error = '邮箱长度必须在1-32个字符之间！';
                break;
            case - 7 :
                $error = '邮箱被禁止注册！';
                break;
            case - 8 :
                $error = '邮箱被占用！';
                break;
            case - 9 :
                $error = '手机号不能为空！';
                break;
            case - 10 :
                $error = '手机被禁止注册！';
                break;
            case - 11 :
                $error = '手机号被占用！';
                break;
            default :
                $error = '未知错误';
        }
        return $error;
    }
}
