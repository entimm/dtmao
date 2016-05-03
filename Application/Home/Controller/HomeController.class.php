<?php
/**
 * @Author: liulu72056
 * @Date:   2015-07-02 12:06:37
 * @Last Modified by:   liulu72056
 * @Last Modified time: 2015-07-12 11:56:54
 */

namespace Home\Controller;
use Think\Controller;

/**
 * 前台公共控制器
 * 为防止多分组Controller名称冲突，公共Controller名称统一使用分组名称
 */
class HomeController extends Controller {

	/* 空操作，用于输出404页面 */
	public function _empty(){
		$this->redirect('Index/index');
	}

}
