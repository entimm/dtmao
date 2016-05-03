<?php
/**
 * @Author: liulu72056
 * @Date:   2015-07-02 12:06:37
 * @Last Modified by:   liulu72056
 * @Last Modified time: 2015-07-27 12:36:27
 */
namespace Home\Controller;

/**
 * 微信交互控制器
 * 主要获取和反馈微信平台的数据
 */
class MController extends HomeController {

    public function suggest() {
        if(IS_POST) {
            $data['cTime'] = time ();
            $data['content'] = I('post.content');
            $data['openid'] = session('?openid') ? session('openid') : '';
            M ( 'suggestions' )->add ( $data );

            $this->assign('useWXJSK', true);
            $wechatJs = D('WechatJs');
            $this->assign('signPackage', $wechatJs->getSignPackage());
            $this->assign('jsApiList', '"closeWindow"');

            $this->assign('title', '提点建议吧');

            $msg['title'] = '消息';
            $msg['content'] = '您的建议提交成功，非常感谢';
            $this->assign('msg', $msg);
            $this->display('result');
        } else {
            $state = I('get.state');
            if($state == CODE_STATE) {
                $code = I('get.code');
                if($code) {
                    $res = D('WechatAuth')->getAccessToken4Code($code);
                    $openid = $res['openid'];
                    session('openid', $openid);
                }
                $this->assign('title', '提点建议吧');
                $this->display();
            } else {
                $requestcodeurl = D('WechatAuth')->getRequestCodeURL('http://dtmao.sinaapp.com/m/suggest', CODE_STATE, 'snsapi_base');
                redirect($requestcodeurl);
            }
        }
    }

    public function recruit() {
        $this->assign('title', '招贤纳士');
        $this->display();
    }

    public function contact() {
        $this->assign('title', '联系方式');
        $this->display();
    }

    public function myinfo() {
        if(IS_POST) {
            $this->assign('title', '我的信息');
            $schoolid = I('post.schoolid');
            $schools = session('schools');
            if(empty($schools[$schoolid])) {
                $msg['title'] = '出错了';
                $msg['content'] = '您所填写的学校有误';
                $this->assign('msg', $msg);
                $this->display('result');

                $this->assign('useWXJSK', true);
                $wechatJs = D('WechatJs');
                $this->assign('signPackage', $wechatJs->getSignPackage());
                $this->assign('jsApiList', '"closeWindow"');

                return;
            }

            $data['schoolid'] = I('post.schoolid');
            $data['time'] = time ();
            $data['id'] = I('post.id');
            $data['name'] = I('post.name');
            $data['phone'] = I('post.phone');
            $data['addr'] = I('post.addr');
            $data['openid'] = session('?openid') ? session('openid') : '';
            if($data['id']) M ( 'member' )->save ( $data );
            else $data['id'] = M ( 'member' )->add ( $data );

            $this->assign('useWXJSK', true);
            $wechatJs = D('WechatJs');
            $this->assign('signPackage', $wechatJs->getSignPackage());
            $this->assign('jsApiList', '"closeWindow"');

            $msg['title'] = '消息';
            $msg['content'] = '您的信息已经提交成功！';
            $this->assign('msg', $msg);
            $this->display('result');

        } else {
            $state = I('get.state');
            if($state == CODE_STATE) {
                $this->assign('title', '我的信息');
                $code = I('get.code');
                if($code) {
                    if(!session('?openid')) {
                        $res = D('WechatAuth')->getAccessToken4Code($code);
                        $openid = $res['openid'];
                        if($openid) session('openid', $openid);
                        else exit;
                    }
                    $map['openid'] = session('openid');
                    $info = M ( 'member' )->where($map)->find();
                    $this->assign('info', $info);

                    $useraddr = S ('useraddr_'.session('openid'));
                    if(!empty($useraddr)) {
                        $map = array('_complex' => $useraddr);
                        $map['_logic'] = 'or';
                    }
                    $map['id'] = $info['schoolid'];
                    $schools = M ('school')->where($map)->getField('id,school');

                    if(empty($schools)) {
                        $this->assign('useWXJSK', true);
                        $wechatJs = D('WechatJs');
                        $this->assign('signPackage', $wechatJs->getSignPackage());
                        $this->assign('jsApiList', '"closeWindow,getLocation"');

                        $msg['title'] = '消息';
                        if(empty($useraddr)) {
                            $msg['content'] = '正在获取您的位置';
                            $this->assign('msg', $msg);
                            $this->display('reqlocation');
                        } else {
                            $msg['content'] = '您所在的区域目前还没有我们的服务点';
                            $this->assign('msg', $msg);
                            $this->display('result');
                        }
                        return;
                    }

                    session('schools', $schools);
                    $this->assign('schools', $schools);
                }
                $this->assign('title', '我的信息');
                $this->display();
            } else {
                $requestcodeurl = D('WechatAuth')->getRequestCodeURL('http://dtmao.sinaapp.com/m/myinfo', CODE_STATE, 'snsapi_base');
                redirect($requestcodeurl);
            }
        }
    }

    public function _empty($name) {
        $services = array(
            'pickup'     => array('type' =>'取件', 'title' => '取个件'),
            'sentout'    => array('type' =>'寄件', 'title' => '寄个件'),
            'takeout'    => array('type' =>'外卖', 'title' => '外卖'),
            'snacks'     => array('type' =>'零食', 'title' => '零食'),
            'fruit'      => array('type' =>'水果生鲜', 'title' => '水果生鲜'),
            'flowercake' => array('type' =>'鲜花蛋糕', 'title' => '鲜花蛋糕')
        );
        if(!in_array($name, array_keys($services))) {
            $this->assign('useWXJSK', true);
            $wechatJs = D('WechatJs');
            $this->assign('signPackage', $wechatJs->getSignPackage());
            $this->assign('jsApiList', '"closeWindow"');
            $this->display('error');
        }
        if(IS_POST) {
            $data['cTime']  = time ();
            $data['cost']   = 2;
            $data['type']   = $services[$name]['type'];
            $data['note']   = I ('post.note');
            $data['openid'] = session('?openid') ? session('openid') : '';
            $data['detail'] = serialize(I ('post.detail'));
            M ( 'order' )->add ( $data );

            $this->assign('useWXJSK', true);
            $wechatJs = D('WechatJs');
            $this->assign('signPackage', $wechatJs->getSignPackage());
            $this->assign('jsApiList', '"closeWindow"');

            $this->assign('title', $services[$name]['title']);

            $msg['title'] = '消息';
            $msg['content'] = '您的订单已经生成，我们会很快进行处理，谢谢';
            $this->assign('msg', $msg);
            $this->display('result');
        } else {
            $state = I('get.state');
            if($state == CODE_STATE) {
                $code = I('get.code');
                if($code) {
                    $res = D('WechatAuth')->getAccessToken4Code($code);
                    $openid = $res['openid'];
                    $res = M ( 'member' )->where(array('openid'=>$openid))->find();
                    if(empty($res)) {
                        $msg = array('title'=>'消息', 'content'=>'亲，为了更好更快的给您提供服务，请您先<a href="http://dtmao.sinaapp.com/m/myinfo">完善您的个人信息</a>，谢谢您的配合');
                        $this->assign('msg', $msg);
                        $this->display('result');
                        return;
                    }
                    session('openid', $openid);
                }
                $this->assign('title', $services[$name]['title']);
                $this->display();
            } else {
                $requestcodeurl = D('WechatAuth')->getRequestCodeURL('http://dtmao.sinaapp.com/m/' . $name, CODE_STATE, 'snsapi_base');
                redirect($requestcodeurl);
            }
        }
    }

    public function test() {
        $this->assign('useWXJSK', true);
        $wechatJs = D('WechatJs');
        $this->assign('signPackage', $wechatJs->getSignPackage());
        $this->assign('jsApiList', '"getLocation"');
        $this->display('reqlocation');
    }

    public function assist($s = 1) {
        if($s == 1) {
            var_dump(S ('useraddr_o4xlUsxqQp22n-sv32wHO7m9ncs0'));
            var_dump(S ('useraddr_o4xlUs35wfL-qIs_7desNRFksHuI'));
        } else {
            S ('useraddr_o4xlUsxqQp22n-sv32wHO7m9ncs0', null);
            S ('useraddr_o4xlUs35wfL-qIs_7desNRFksHuI', null);
        }
    }

    public function setlocation() {
        if(IS_POST) {
            $latitude  = I ('post.latitude');
            $longitude = I ('post.longitude');
            $openid    = session('openid');

            $res = wp_file_get_contents ( 'http://apis.map.qq.com/ws/geocoder/v1/?key='.QQMAPKEY.'&location='.$latitude.','.$longitude );
            $res = json_decode ( $res, true );
            $adinfo = $res['result']['ad_info'];
            $info['city'] = $adinfo['city'];
            if(!empty($adinfo['district'])) {
                $info['district'] = $adinfo['district'];
            }
            S ('useraddr_'.$openid, $info, 3600);

            $data = array('status' => 1, 'info'=>$info['city'].' '.$info['district']);
            // $data = array('status' => 1, 'info'=>json_encode($adinfo));
            $this->ajaxReturn($data);
        }
    }

    public function orderinfo($id) {
        $state = I('get.state');
        if($state == CODE_STATE) {
            $code = I('get.code');
            if($code) {
                $res = D('WechatAuth')->getAccessToken4Code($code);
                $openid = $res['openid'];
                $map = array('dm_order.id'=>$id,'dm_order.openid'=>$openid);
                $order = M ('order')->join('__MEMBER__ ON __ORDER__.openid = __MEMBER__.openid')
                                ->join('__SCHOOL__ ON __MEMBER__.schoolid = __SCHOOL__.id')
                                ->field(array('dm_order.id','dm_order.openid','cost','detail','type','mTime','cTime','status','note','name','addr','school','phone','dealwith'))
                                ->where($map)
                                ->find();
                if($order) {
                    $num = $id + 1000000;
                    $order['number'] = date('ymd', $order['ctime']) . substr($num, -6);
                    $detail = $order['detail'];
                    if(!empty($detail)) {
                        $detail = unserialize($detail);
                        $order['detail'] = $detail;
                    }
                    $dealwith = $order['dealwith'];
                    if(!empty($dealwith)) {
                        $dealwith = unserialize($dealwith);
                        $order['dealwith'] = $dealwith;
                    }
                }
                $this->assign('order', $order);
            }
            $this->assign('title', '订单详情');
            $this->display();
        } else {
            $requestcodeurl = D('WechatAuth')->getRequestCodeURL('http://dtmao.sinaapp.com/m/orderinfo/id/' . $id, CODE_STATE, 'snsapi_base');
            redirect($requestcodeurl);
        }
    }

}
