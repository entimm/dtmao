<?php
/**
 * @Author: liulu72056
 * @Date:   2015-07-01 14:49:26
 * @Last Modified by:   liulu72056
 * @Last Modified time: 2015-07-26 19:21:33
 */
namespace Home\Controller;

class WechatAuthController extends AuthController {

    private $wechatAuth;

    public function _initialize() {
        $this->wechatAuth = D('WechatAuth');
    }

    public function index() {
        $accesstoken = $this->wechatAuth->getAccessToken();
        $this->assign('accesstoken', $accesstoken);
        $this->display();
    }

    public function createCustomMenu() {
        $data = '{
  "button": [{
    "name": "快递服务",
    "sub_button": [{
      "type": "view",
      "name": "取个件",
      "url": "http://dtmao.sinaapp.com/m/pickup"
    }, {
      "type": "view",
      "name": "寄个件",
      "url": "http://dtmao.sinaapp.com/m/sentout"
    }]
  }, {
    "name": "生活服务",
    "sub_button":[{
      "type": "view",
      "name": "外卖",
      "url": "http://dtmao.sinaapp.com/m/takeout"
    }, {
      "type": "view",
      "name": "零食",
      "url": "http://dtmao.sinaapp.com/m/snacks"
    }, {
      "type": "view",
      "name": "水果生鲜",
      "url": "http://dtmao.sinaapp.com/m/fruit"
    }, {
      "type": "view",
      "name": "鲜花蛋糕",
      "url": "http://dtmao.sinaapp.com/m/flowercake"
    }]
  }, {
    "name": "DT猫",
    "sub_button": [{
    "type": "view",
    "name": "我的信息",
    "url":  "http://dtmao.sinaapp.com/M/myinfo"
    }, {
      "type": "view",
      "name": "招贤纳士",
      "url": "http://dtmao.sinaapp.com/M/recruit"
    }, {
      "type": "view",
      "name": "联系方式",
      "url": "http://dtmao.sinaapp.com/M/contact"
    }, {
      "type": "view",
      "name": "建议意见",
      "url": "http://dtmao.sinaapp.com/M/suggest"
    }]
    }]
  }]
}';
        $res = $this->wechatAuth->menuCreate($data);
        echo $res;
    }

    public function getRequestCodeURL() {
        echo $this->wechatAuth->getRequestCodeURL('http://dtmao.sinaapp.com/m/suggest', 'auth', 'snsapi_base');
    }

    public function addDKF() {
        $res = $this->wechatAuth->addAccount('enjoy@DT_mao', 'enjoy', md5('720569318'));
        var_dump($res);
    }

    public function sendByCustomer() {
        $data['touser'] = 'o4xlUs35wfL-qIs_7desNRFksHuI';
        $data['msgtype'] = 'text';
        $content = '内容';
        $data['text'] = array('content' => $content);
        $this->wechatAuth->sendByCustomer($data);
    }

    public function userInfo() {
        // echo $this->wechatAuth->userInfo('o4xlUs35wfL-qIs_7desNRFksHuI');
    }
}
