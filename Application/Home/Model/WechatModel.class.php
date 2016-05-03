<?php
/**
 * 微信基础模型
 * @Author: liulu72056
 * @Date:   2015-07-04 10:26:54
 * @Last Modified by:   liulu72056
 * @Last Modified time: 2015-07-11 17:59:54
 */
namespace Home\Model;

class WechatModel {
	var $data = array ();
	var $wxcpt, $sReqTimeStamp, $sReqNonce, $sEncryptMsg;

	public function __construct() {
		if ($_REQUEST ['doNotInit'])
			return true;
		self::auth(TOKEN) || exit;
		if(IS_GET){
		    exit($_GET['echostr']);
		}

		$content = wp_file_get_contents ( 'php://input' );
		! empty ( $content ) || die ( 'nothing' );

		if ($_GET ['encrypt_type'] == 'aes') {
			vendor ( 'WXBiz.wxBizMsgCrypt' );

			$this->sReqTimeStamp = I ( 'get.timestamp' );
			$this->sReqNonce = I ( 'get.nonce' );
			$this->sEncryptMsg = I ( 'get.msg_signature' );

			$this->wxcpt = new \WXBizMsgCrypt ( TOKEN, ENCODING_AES_KEY, APPID );

			$sMsg = ""; // 解析之后的明文
			$errCode = $this->wxcpt->DecryptMsg ( $this->sEncryptMsg, $this->sReqTimeStamp, $this->sReqNonce, $content, $sMsg );
			if ($errCode != 0) {
				addWeixinLog ( $_GET, "DecryptMsg Error: " . $errCode );
				exit ();
			} else {
				// 解密成功，sMsg即为xml格式的明文
				$content = $sMsg;
			}
		}
        $data = (array) simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOCDATA);

        //将数组键名转换为小写
        $this->data = array_change_key_case($data, CASE_LOWER);
	}
	/* 获取微信平台请求的信息 */
	public function getData() {
		return $this->data;
	}

	/* 回复文本消息 */
	public function replyText($content) {
		$msg ['Content'] = $content;
		$this->_replyData ( $msg, 'text' );
	}
	/* 回复图片消息 */
	public function replyImage($media_id) {
		$msg ['MediaId'] = $media_id;
		$this->_replyData ( $msg, 'image' );
	}
	/* 回复语音消息 */
	public function replyVoice($media_id) {
		$msg ['MediaId'] = $media_id;
		$this->_replyData ( $msg, 'voice' );
	}
	/* 回复视频消息 */
	public function replyVideo($media_id, $title = '', $description = '') {
		$msg ['MediaId'] = $media_id;
		$msg ['Title'] = $title;
		$msg ['Description'] = $description;
		$this->_replyData ( $msg, 'video' );
	}
	/* 回复音乐消息 */
	public function replyMusic($media_id, $title = '', $description = '', $music_url, $HQ_music_url) {
		$msg ['ThumbMediaId'] = $media_id;
		$msg ['Title'] = $title;
		$msg ['Description'] = $description;
		$msg ['MusicURL'] = $music_url;
		$msg ['HQMusicUrl'] = $HQ_music_url;
		$this->_replyData ( $msg, 'music' );
	}
	/*
	 * 回复图文消息 articles array 格式如下： array( array('Title'=>'','Description'=>'','PicUrl'=>'','Url'=>''), array('Title'=>'','Description'=>'','PicUrl'=>'','Url'=>'') );
	 */
	public function replyNews($articles) {
		$msg ['ArticleCount'] = count ( $articles );
		$msg ['Articles'] = $articles;

		$this->_replyData ( $msg, 'news' );
	}
	/* 发送回复消息到微信平台 */
	private function _replyData($msg, $msgType) {
		$msg ['ToUserName'] = $this->data ['fromusername'];
		$msg ['FromUserName'] = $this->data ['tousername'];
		$msg ['CreateTime'] = NOW_TIME;
		$msg ['MsgType'] = $msgType;

		if($_REQUEST ['doNotInit']){
			dump($msg);
			exit;
		}

		addWeixinLog ( $msg, '_replyData', $msgType );

        $xml = new \SimpleXMLElement ( '<xml></xml>' );
        $this->_data2xml ( $xml, $msg );
        $str = $xml->asXML ();

        // 记录日志
        if ($_GET ['encrypt_type'] == 'aes') {
            $sEncryptMsg = ""; // xml格式的密文
            $errCode = $this->wxcpt->EncryptMsg ( $str, $this->sReqTimeStamp, $this->sReqNonce, $sEncryptMsg );
            if ($errCode == 0) {
                $str = $sEncryptMsg;
            } else {
                addWeixinLog ( $str, "EncryptMsg Error: " . $errCode, $msgType );
            }
        }

		echo ($str);
	}

    /**
     * 数据XML编码
     * @param  object $xml  XML对象
     * @param  mixed  $data 数据
     * @param  string $item 数字索引时的节点名称
     * @return string
     */
    protected static function _data2xml($xml, $data, $item = 'item') {
        foreach ($data as $key => $value) {
            /* 指定默认的数字key */
            is_numeric($key) && $key = $item;

            /* 添加子元素 */
            if(is_array($value) || is_object($value)){
                $child = $xml->addChild($key);
                self::_data2xml($child, $value, $item);
            } else {
                if(is_numeric($value)){
                    $child = $xml->addChild($key, $value);
                } else {
                    $child = $xml->addChild($key);
                    $node  = dom_import_simplexml($child);
                    $cdata = $node->ownerDocument->createCDATASection($value);
                    $node->appendChild($cdata);
                }
            }
        }
    }

	/**
	 * 对数据进行签名认证，确保是微信发送的数据
	 * @param  string $token 微信开放平台设置的TOKEN
	 * @return boolean       true-签名正确，false-签名错误
	 */
	protected static function auth($token){
	    /* 获取数据 */
	    $data = array(I ( 'get.timestamp' ), I ( 'get.nonce' ), $token);
	    $sign = I ( 'get.signature' );

	    /* 对数据进行字典排序 */
	    sort($data, SORT_STRING);

	    /* 生成签名 */
	    $signature = sha1(implode($data));

	    return $signature === $sign;
	}

    /* 回复多客服消息 */
    public function reply4Customer($kfAccount) {
        if($kfAccount) {
            $msg ['TransInfo'] = array('KfAccount' => $kfAccount);
        }
        $this->_replyData ( $msg, 'transfer_customer_service' );
    }

    /**
     * 发送文本消息
     *
     * 客服接口-发消息
     */
    public function sendTextByCustomer($content) {
        $wechatAuth = D('WechatAuth');
        $data['touser'] = $this->data ['fromusername'];
        $data['msgtype'] = 'text';
        $data['text'] = array('content' => $content);
        $wechatAuth->sendByCustomer($data);
    }

    /**
     * 发送图片消息
     *
     * 客服接口-发消息
     */
    public function sendImageByCustomer($media_id) {
        $wechatAuth = D('WechatAuth');
        $data['touser'] = $this->data ['fromusername'];
        $data['msgtype'] = 'image';
        $data['image'] = array('media_id' => $media_id);
        $wechatAuth->sendByCustomer($data);
    }

    /**
     * 发送语音消息
     *
     * 客服接口-发消息
     */
    public function sendVoiceByCustomer($media_id) {
        $wechatAuth = D('WechatAuth');
        $data['touser'] = $this->data ['fromusername'];
        $data['msgtype'] = 'voice';
        $data['voice'] = array('media_id' => $media_id);
        $wechatAuth->sendByCustomer($data);
    }

    // todo : 发送视频消息、发送音乐消息、发送图文消息、发送卡券
}
