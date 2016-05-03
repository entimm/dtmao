<?php
/**
 * @Author: liulu72056
 * @Date:   2015-07-01 14:49:26
 * @Last Modified by:   liulu72056
 * @Last Modified time: 2015-07-27 12:44:06
 */
namespace Home\Controller;

class OrderController extends AuthController {

    public function index($s = 0, $p = 1) {
        $fieldInfo = array(
            'revname'     => '收件人',
            'destcity'    => '寄送城市',
            'destination' => '寄送地址',
            'weight'      => '重量(斤)',
            'provider'    => '快递公司',
            'type'        => '种类',
            'num'         => '数量'
        );

        $map = array('status' => $s);
        $uid = is_login ();
        $User = D ('User');
        $userinfo = $User->where('id='.$uid)->find();
        if(!$User->isAdmin($userinfo)) $map['schoolid'] = $userinfo['schoolid'];

        $Order  = M ('order');
        $orders = $Order->join('__MEMBER__ ON __ORDER__.openid = __MEMBER__.openid')
                        ->join('__SCHOOL__ ON __MEMBER__.schoolid = __SCHOOL__.id')
                        ->field(array('dm_order.id','dm_order.openid','cost','detail','type','mTime','cTime','status','note','name','addr','school','phone','dealwith'))
                        ->where($map)
                        ->order('id')
                        ->page($p.',25')
                        ->select();
        foreach ($orders as &$value) {
            $num = $value['id'] + 1000000;
            $value['number'] = date('ymd', $value['ctime']) . substr($num, -6);
            $detail = $value['detail'];
            if(!empty($detail)) {
                $detail = unserialize($detail);
                if($detail['destcity']) {
                    $detail['destcity'] = $detail['destcity']['prov'].' '.$detail['destcity']['city'].' '.$detail['destcity']['dist'];
                }
                $value['detail'] = $detail;
            }
            $dealwith = $value['dealwith'];
            if(!empty($dealwith)) {;
                $dealwith = unserialize($dealwith);
                $value['dealwith'] = $dealwith;
            }
        }
        $this->assign('orders', $orders);

        $count = $Order->where($map)->count();
        $Page  = new \Think\Page($count,25);
        $show  = $Page->show();
        $this->assign('page', $show);
        $this->assign('count', $count);

        $this->assign('fieldInfo', $fieldInfo);

        $Courier = M ('Courier');
        $map = array('status'=>1,'schoolid'=>session('user_auth.schoolid'));
        $couriers = $Courier->where($map)->select();
        $this->assign('couriers', $couriers);

        $this->display();
    }

    public function dealWith() {
        $reason   = I ('post.reason');
        $data     = array();
        $dealwith = array();
        if(empty($reason)) {
            $data['status'] = 1;
            $dealwith['svname'] = I ('post.svname');
            $dealwith['svcontact'] = I ('post.svcontact');
        } else {
            $data['status'] = 2;
            $dealwith['reason'] = $reason;
        }
        $data['dealwith'] = serialize($dealwith);
        $data['id'] = I ('post.orderid');
        $data['mTime'] = time();

        $Order = M ( 'order' );
        $res = $Order->save($data);
        if(!empty($res)) {
            $orderInfo = $Order->find($data['id']);
            $num = $orderInfo['id'] + 1000000;
            $orderInfo['number'] = date('ymd', $orderInfo['ctime']) . substr($num, -6);
            $wechatAuth = D('WechatAuth');
            $$template = array();
            if(empty($reason)) {
                $template = array(
                    'touser'      => $orderInfo['openid'],
                    'template_id' => 'S50fABg08HS1nqffadlS3UbH8LlxPirfs8gWKbnRxns',
                    'url'         => 'http://dtmao.sinaapp.com/m/orderinfo/id/'.$orderInfo['id'],
                    'topcolor'    => "#FF0000",
                    'data'        => array(
                        'first'    => array('value'=>'亲，您的'.$orderInfo['type'].'服务订单已经成功受理', 'color'=>'#173177'),
                        'keyword1' => array('value'=>$orderInfo['number'], 'color'=>'#173177'),
                        'keyword2' => array('value'=>'DT猫', 'color'=>'#173177'),
                        'keyword3' => array('value'=>$dealwith['svname'], 'color'=>'#173177'),
                        'keyword4' => array('value'=>$dealwith['svcontact'], 'color'=>'#173177'),
                        'remark'   => array('value'=>'请保持您的电话畅通，以便配送员联系您', 'color'=>'#173177')
                    )
                );
            } else {
                $template = array(
                    'touser'      => $orderInfo['openid'],
                    'template_id' => 'FPkSNU5llzxI1HDY_xiqE79yXXvjuSxjwJ1YMDMK6_U',
                    'url'         => 'http://dtmao.sinaapp.com/m/orderinfo/id/'.$orderInfo['id'],
                    'topcolor'    => "#FF0000",
                    'data'        => array(
                        'first'    => array('value'=>'亲，很抱歉的说我们无法为您提供配送服务', 'color'=>'#173177'),
                        'keyword1' => array('value'=>$orderInfo['number'], 'color'=>'#173177'),
                        'keyword2' => array('value'=>date('y-m-d H:i:s', $orderInfo['ctime']), 'color'=>'#173177'),
                        'keyword3' => array('value'=>date('y-m-d H:i:s', $orderInfo['mtime']), 'color'=>'#173177'),
                        'keyword4' => array('value'=>$reason, 'color'=>'#173177'),
                        'remark'   => array('value'=>'订单处理结果', 'color'=>'#173177')
                    )
                );
            }
            $info = $wechatAuth->sendTemplate($template);
            $returnData['status']  = 1;
            $returnData['info']  = $info;
        }
        else $returnData['status']  = 0;

        $this->ajaxReturn($returnData);
    }

}
