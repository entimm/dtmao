<?php
/**
 * @Author: liulu72056
 * @Date:   2015-07-26 14:36:32
 * @Last Modified by:   liulu72056
 * @Last Modified time: 2015-07-26 17:41:51
 */

namespace Home\Controller;

class ToolsController extends AuthController {
    public function addschool() {
        if(IS_POST) {
            $data = I ('post.data');
            $Tools = D ('Tools');
            $result = $Tools->getAddrInfo($data);
            if($result) {
                $addrs = array();
                foreach ($result[1] as $key => $value) {
                    $url = 'http://apis.map.qq.com/ws/geocoder/v1/?key='.QQMAPKEY.'&address='.urlencode($result[2][$key]);
                    $res = wp_file_get_contents ( $url );
                    $res = json_decode ( $res, true );
                    $adinfo = $res['result']['address_components'];
                    $addrs[] = array(
                                      'school'   => $value,
                                      'province'     => $adinfo['province'],
                                      'city'     => $adinfo['city'],
                                      'district' => $adinfo['district'],
                                      'addrinfo' => $result[2][$key]
                                );
                }
                $School = M ('School');
                foreach ($addrs as $value) {
                    $School->add($value);
                }
            }
            $this->redirect('Tools/listschool');
        } else {
            $this->display();
        }
    }

    public function delschool($id = 0) {
        //$map = array('id'=>$id);
        //M ('School')->where($map)->delete();
        $this->redirect('Tools/listschool');
    }

    public function listschool() {
        $schools = M ('School')->select();
        $this->assign('schools', $schools);
        $this->display();
    }
}
