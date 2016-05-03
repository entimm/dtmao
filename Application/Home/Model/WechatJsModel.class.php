<?php
/**
 * @Author: liulu72056
 * @Date:   2015-07-04 10:26:54
 * @Last Modified by:   liulu72056
 * @Last Modified time: 2015-07-11 17:59:48
 */
namespace Home\Model;

class WechatJsModel {

    private function getJsApiTicket() {
      $jsapi_ticket = S ('jsapi_ticket');
      if ($jsapi_ticket !== false) return $jsapi_ticket;

      $wechatAuth = D('WechatAuth');
        $accessToken = $wechatAuth->getAccessToken();
        // 如果是企业号用以下 URL 获取 ticket
        // $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$accessToken";
        $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
        $res = json_decode($wechatAuth->http($url), true);

        if ($res['errcode'] == 0) {
            $jsapi_ticket = $res['ticket'];
            $expires_in = $res['expires_in'];
            S ('jsapi_ticket', $jsapi_ticket, $expires_in);
            return $jsapi_ticket;
        }
    }

    private function createNonceStr($length = 16) {
      $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
      $str = "";
      for ($i = 0; $i < $length; $i++) {
        $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
      }
      return $str;
    }

    public function getSignPackage() {
      $jsapiTicket = $this->getJsApiTicket();

      // 注意 URL 一定要动态获取，不能 hardcode.
      $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
      $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

      $timestamp = time();
      $nonceStr = $this->createNonceStr();

      // 这里参数的顺序要按照 key 值 ASCII 码升序排序
      $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

      $signature = sha1($string);

      $signPackage = array(
        "appId"     => APPID,
        "nonceStr"  => $nonceStr,
        "timestamp" => $timestamp,
        "url"       => $url,
        "signature" => $signature,
        "rawString" => $string
      );
      return $signPackage;
    }

}
