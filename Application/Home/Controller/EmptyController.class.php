<?php
/**
 * @Author: liulu72056
 * @Date:   2015-07-02 14:24:43
 * @Last Modified by:   liulu72056
 * @Last Modified time: 2015-07-02 14:30:52
 */
namespace Home\Controller;
use Think\Controller;

/**
 * 空控制器的概念是指当系统找不到请求的控制器名称的时候，系统会尝试定位空控制器(EmptyController)，利用这个机制我们可以用来定制错误页面和进行URL的优化。
 */
class EmptyController extends Controller {

    public function index(){
        $this->redirect('Index/index');
    }

    public function _empty($name){
        $this->redirect('Index/index');
    }
}
