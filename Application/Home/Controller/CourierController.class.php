<?php
/**
 * @Author: liulu72056
 * @Date:   2015-07-01 14:49:26
 * @Last Modified by:   liulu72056
 * @Last Modified time: 2015-07-18 20:52:13
 */
namespace Home\Controller;

class CourierController extends AuthController {
    public function index() {
        $Courier = M ('Courier');
        $map = array('status'=>1,'schoolid'=>session('user_auth.schoolid'));
        $couriers = $Courier->where($map)->select();
        $this->assign('couriers', $couriers);
        $this->display();
    }

    public function add() {
        $Courier = M ('Courier');
        $Courier->create();
        $Courier->schoolid = session('user_auth.schoolid');
        $Courier->update_time = NOW_TIME;
        $result = $Courier->add();
        if($result) {
            $this->success('添加成功', 'Courier/index');
        } else {
            $this->error('添加失败');
        }
    }

    public function del($id = 0) {
        $map = array('id'=>$id,'schoolid'=>session('user_auth.schoolid'));
        M ('Courier')->where($map)->delete();
        $this->redirect('Courier/index');
    }
}
