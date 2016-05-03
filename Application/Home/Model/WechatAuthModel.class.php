<?php
/**
 * 微信外部调用模型
 * @Author: liulu72056
 * @Date:   2015-07-04 10:26:54
 * @Last Modified by:   liulu72056
 * @Last Modified time: 2015-07-11 17:59:41
 */
namespace Home\Model;

class WechatAuthModel {

    /* 消息类型常量 */
    const MSG_TYPE_TEXT     = 'text';
    const MSG_TYPE_IMAGE    = 'image';
    const MSG_TYPE_VOICE    = 'voice';
    const MSG_TYPE_VIDEO    = 'video';
    const MSG_TYPE_MUSIC    = 'music';
    const MSG_TYPE_NEWS     = 'news';
    const MSG_TYPE_LOCATION = 'location';
    const MSG_TYPE_LINK     = 'link';
    const MSG_TYPE_EVENT    = 'event';

    /* 二维码类型常量 */
    const QR_SCENE       = 'QR_SCENE';
    const QR_LIMIT_SCENE = 'QR_LIMIT_SCENE';

    /**
     * 微信开发者申请的appID
     * @var string
     */
    private $appId = '';

    /**
     * 微信开发者申请的appSecret
     * @var string
     */
    private $appSecret = '';

    /**
     * 获取到的access_token
     * @var string
     */
    private $accessToken = '';

    /**
     * 微信api根路径
     * @var string
     */
    private $apiURL    = 'https://api.weixin.qq.com';

    /**
     * 微信媒体文件根路径
     * @var string
     */
    private $mediaURL  = 'http://file.api.weixin.qq.com/cgi-bin';

    /**
     * 微信二维码根路径
     * @var string
     */
    private $qrcodeURL = 'https://mp.weixin.qq.com/cgi-bin';

    /**
     * 用户同意授权，获取code
     * @var string
     */
    private $requestCodeURL = 'https://open.weixin.qq.com/connect/oauth2/authorize';

    /**
     * 网页授权获取用户基本信息
     * @var string
     */
    private $oauthApiURL = 'https://api.weixin.qq.com/sns';

    /**
     * 构造方法，调用微信高级接口时实例化SDK
     * @param string $appid  微信appid
     * @param string $secret 微信appsecret
     * @param string $token  获取到的access_token
     */
    public function __construct() {
        $this->appId     = APPID;
        $this->appSecret = APPSECRET;

        $this->accessToken = $this->getAccessToken();
    }

    /**
     * 获取access_token，用于后续接口访问
     * @return array access_token信息，包含 token 和有效期
     */
    public function getAccessToken() {
        $access_token = S ( 'access_token' );
        if ($access_token !== false) return $access_token;

        $param = array(
            'appid'  => $this->appId,
            'secret' => $this->appSecret
        );

        $param['grant_type'] = 'client_credential';
        $url = "{$this->apiURL}/cgi-bin/token";
        $token = self::http($url, $param);
        $res = json_decode($token, true);

        if(is_array($res)) {
            if(isset($res['errcode'])) {
                throw new \Exception($res['errmsg']);
            } else {
                $access_token = $res['access_token'];
                $expires_in = $res['expires_in'];
                S ( 'access_token', $access_token, $expires_in );
                return $access_token;
            }
        } else {
            throw new \Exception('获取微信access_token失败！');
        }
    }

    /**
     * 获取access_token，用于后续接口访问
     * @return array access_token信息，包含 token 和有效期
     */
    public function getAccessToken4Code($code = null) {
        $param = array(
            'appid'  => $this->appId,
            'secret' => $this->appSecret
        );

        $param['code'] = $code;
        $param['grant_type'] = 'authorization_code';
        $url = "{$this->oauthApiURL}/oauth2/access_token";

        $token = self::http($url, $param);
        $res = json_decode($token, true);

        if(is_array($res)) {
            if(isset($res['errcode'])) {
                throw new \Exception($res['errmsg']);
            } else {
                return $res;
            }
        } else {
            throw new \Exception('获取微信access_token失败！');
        }
    }

    public function getRequestCodeURL($redirect_uri, $state = null, $scope = 'snsapi_base') {

        $query = array(
            'appid'         => $this->appId,
            'redirect_uri'  => $redirect_uri,
            'response_type' => 'code',
            'scope'         => $scope,
        );

        if(!is_null($state) && preg_match('/[a-zA-Z0-9]+/', $state)) {
            $query['state'] = $state;
        }

        $query = http_build_query($query);
        return "{$this->requestCodeURL}?{$query}#wechat_redirect";
    }

    /**
     * 获取授权用户信息
     * @param  string $token acess_token
     * @param  string $lang  指定的语言
     * @return array         用户信息数据，具体参见微信文档
     */
    public function getUserInfo($token, $lang = 'zh_CN') {
        $query = array(
            'access_token' => $token['access_token'],
            'openid'       => $token['openid'],
            'lang'         => $lang,
        );

        $info = self::http("{$this->oauthApiURL}/userinfo", $query);
        return json_decode($info, true);
    }

    /**
     * 上传媒体资源
     * @param  string $filename 媒体资源本地路径
     * @param  string $type     媒体资源类型，具体请参考微信开发手册
     */
    public function mediaUpload($filename, $type) {
        $param = array(
            'access_token' => $this->accessToken,
            'type'         => $type
        );

        $filename = realpath($filename);
        if(!$filename) throw new \Exception('资源路径错误！');

        $file = array('media' => "@{$filename}");
        $url  = "{$this->mediaURL}/media/upload";
        $data = self::http($url, $param, $file, 'POST');

        return json_decode($data, true);
    }

    /**
     * 获取媒体资源下载地址
     * 注意：视频资源不允许下载
     * @param  string $media_id 媒体资源id
     * @return string           媒体资源下载地址
     */
    public function mediaGet($media_id) {
        $param = array(
            'access_token' => $this->accessToken,
            'media_id'     => $media_id
        );

        $url = "{$this->mediaURL}/media/get?";
        return $url . http_build_query($param);
    }

    /**
     * 给指定用户推送信息
     * 注意：微信规则只允许给在48小时内给公众平台发送过消息的用户推送信息
     * @param  string $openid  用户的openid
     * @param  array  $content 发送的数据，不同类型的数据结构可能不同
     * @param  string $type    推送消息类型
     */
    public function messageCustomSend($openid, $content, $type = self::MSG_TYPE_TEXT) {

        //基础数据
        $data = array(
            'touser'=>$openid,
            'msgtype'=>$type,
        );

        //根据类型附加额外数据
        $data[$type] = call_user_func(array(self, $type), $content);

        return $this->api('cgi-bin/message/custom/send', $data);
    }

    /**
     * 发送文本消息
     * @param  string $openid 用户的openid
     * @param  string $text   发送的文字
     */
    public function sendText($openid, $text) {
        return $this->messageCustomSend($openid, $text, self::MSG_TYPE_TEXT);
    }

    /**
     * 发送图片消息
     * @param  string $openid 用户的openid
     * @param  string $media  图片ID
     */
    public function sendImage($openid, $media) {
        return $this->messageCustomSend($openid, $media, self::MSG_TYPE_IMAGE);
    }

    /**
     * 发送语音消息
     * @param  string $openid 用户的openid
     * @param  string $media  音频ID
     */
    public function sendVoice($openid, $media) {
        return $this->messageCustomSend($openid, $media, self::MSG_TYPE_VOICE);
    }

    /**
     * 发送视频消息
     * @param  string $openid      用户的openid
     * @param  string $media_id    视频ID
     * @param  string $title       视频标题
     * @param  string $discription 视频描述
     */
    public function sendVideo() {
        $video  = func_get_args();
        $openid = array_shift($video);
        return $this->messageCustomSend($openid, $video, self::MSG_TYPE_VIDEO);
    }

    /**
     * 发送音乐消息
     * @param  string $openid         用户的openid
     * @param  string $title          音乐标题
     * @param  string $discription    音乐描述
     * @param  string $musicurl       音乐链接
     * @param  string $hqmusicurl     高品质音乐链接
     * @param  string $thumb_media_id 缩略图ID
     */
    public function sendMusic() {
        $music  = func_get_args();
        $openid = array_shift($music);
        return $this->messageCustomSend($openid, $music, self::MSG_TYPE_MUSIC);
    }

    /**
     * 发送图文消息
     * @param  string $openid 用户的openid
     * @param  array  $news   图文内容 [标题，描述，URL，缩略图]
     * @param  array  $news1  图文内容 [标题，描述，URL，缩略图]
     * @param  array  $news2  图文内容 [标题，描述，URL，缩略图]
     *                ...     ...
     * @param  array  $news9  图文内容 [标题，描述，URL，缩略图]
     */
    public function sendNews() {
        $news   = func_get_args();
        $openid = array_shift($news);
        return $this->messageCustomSend($openid, $news, self::MSG_TYPE_NEWS);
    }

    /**
     * 发送一条图文消息
     * @param  string $openid      用户的openid
     * @param  string $title       文章标题
     * @param  string $discription 文章简介
     * @param  string $url         文章连接
     * @param  string $picurl      文章缩略图
     */
    public function sendNewsOnce() {
        $news   = func_get_args();
        $openid = array_shift($news);
        $news   = array($news);
        return $this->messageCustomSend($openid, $news, self::MSG_TYPE_NEWS);
    }

    /**
     * 创建用户组
     * @param  string $name 组名称
     */
    public function groupsCreate($name) {
        $data = array('group' => array('name' => $name));
        return $this->api('cgi-bin/groups/create', $data);
    }

    /**
     * 查询所有分组
     * @return array 分组列表
     */
    public function groupsGet() {
        return $this->api('cgi-bin/groups/get', '', 'GET');
    }

    /**
     * 查询用户所在的分组
     * @param  string $openid 用户的OpenID
     * @return number         分组ID
     */
    public function groupsGetid($openid) {
        $data = array('openid' => $openid);
        return $this->api('cgi-bin/groups/getid', $data);
    }

    /**
     * 修改分组
     * @param  number $id   分组ID
     * @param  string $name 分组名称
     * @return array        修改成功或失败信息
     */
    public function groupsUpdate($id, $name) {
        $data = array('id' => $id, 'name' => $name);
        return $this->api('cgi-bin/groups/update', $data);
    }

    /**
     * 移动用户分组
     * @param  string $openid     用户的OpenID
     * @param  number $to_groupid 要移动到的分组ID
     * @return array              移动成功或失败信息
     */
    public function groupsMemberUpdate($openid, $to_groupid) {
        $data = array('openid' => $openid, 'to_groupid' => $to_groupid);
        return $this->api('cgi-bin/groups/member/update', $data);
    }

    /**
     * 用户设备注名
     * @param  string $openid 用户的OpenID
     * @param  string $remark 设备注名
     * @return array          执行成功失败信息
     */
    public function userInfoUpdateremark($openid, $remark) {
        $data = array('openid' => $openid, 'remark' => $remark);
        return $this->api('cgi-bin/user/info/updateremark', $data);
    }

    /**
     * 获取指定用户的详细信息
     * @param  string $openid 用户的openid
     * @param  string $lang   需要获取数据的语言
     */
    public function userInfo($openid, $lang = 'zh_CN') {
        $param = array('openid' => $openid, 'lang' => $lang);
        return $this->api('cgi-bin/user/info', '', 'GET', $param);
    }

    /**
     * 获取关注者列表
     * @param  string $next_openid 下一个openid，在用户数大于10000时有效
     * @return array               用户列表
     */
    public function userGet($next_openid = '') {
        $param = array('next_openid' => $next_openid);
        return $this->api('cgi-bin/user/get', '', 'GET', $param);
    }

    /**
     * 创建自定义菜单
     * @param  array $button 符合规则的菜单数组，规则参见微信手册
     */
    public function menuCreate($data) {
        return $this->api('cgi-bin/menu/create', $data);
    }

    /**
     * 获取所有的自定义菜单
     * @return array  自定义菜单数组
     */
    public function menuGet() {
        return $this->api('cgi-bin/menu/get', '', 'GET');
    }

    /**
     * 删除自定义菜单
     */
    public function menuDelete() {
        return $this->api('cgi-bin/menu/delete', '', 'GET');
    }

    /**
     * 创建二维码，可创建指定有效期的二维码和永久二维码
     * @param  integer $scene_id       二维码参数
     * @param  integer $expire_seconds 二维码有效期，0-永久有效
     */
    public function qrcodeCreate($scene_id, $expire_seconds = 0) {
        $data = array();

        if(is_numeric($expire_seconds) && $expire_seconds > 0) {
            $data['expire_seconds'] = $expire_seconds;
            $data['action_name']    = self::QR_SCENE;
        } else {
            $data['action_name']    = self::QR_LIMIT_SCENE;
        }

        $data['action_info']['scene']['scene_id'] = $scene_id;
        return $this->api('cgi-bin/qrcode/create', $data);
    }

    /**
     * 根据ticket获取二维码URL
     * @param  string $ticket 通过 qrcodeCreate接口获取到的ticket
     * @return string         二维码URL
     */
    public function showqrcode($ticket) {
        return "{$this->qrcodeURL}/showqrcode?ticket={$ticket}";
    }

    /**
     * 长链接转短链接
     * @param  string $long_url 长链接
     * @return string           短链接
     */
    public function shorturl($long_url) {
        $data = array(
            'action'   => 'long2short',
            'long_url' => $long_url
        );

        return $this->api('cgi-bin/shorturl', $data);
    }

    /**
     * 获取微信服务器IP列表
     */
    public function IPList($ticket) {
        return $this->api('cgi-bin/getcallbackip', '', 'GET');
    }

    /**
     * 获取自动回复规则
     */
    public function autoreplyInfo($ticket) {
        return $this->api('cgi-bin/get_current_autoreply_info', '', 'GET');
    }

    /**
     * 调用微信api获取响应数据
     * @param  string $name   API名称
     * @param  string $data   POST请求数据
     * @param  string $method 请求方式
     * @param  string $param  GET请求参数
     * @return array          api返回结果
     */
    protected function api($name, $data = '', $method = 'POST', $param = '') {
        $params = array('access_token' => $this->accessToken);

        if(!empty($param) && is_array($param)) {
            $params = array_merge($params, $param);
        }

        $url  = "{$this->apiURL}/{$name}";
        if(!empty($data) && is_array($data)) {
            //保护中文，微信api不支持中文转义的json结构
            array_walk_recursive($data, function(&$value) {
                $value = urlencode($value);
            });
            $data = urldecode(json_encode($data));
        }

        $data = self::http($url, $params, $data, $method);

        return $data;
    }

    /**
     * 发送HTTP请求方法，目前只支持CURL发送请求
     * @param  string $url    请求URL
     * @param  array  $param  GET参数数组
     * @param  array  $data   POST的数据，GET请求时该参数无效
     * @param  string $method 请求方法GET/POST
     * @return string          响应数据
     */
    public static function http($url, $param = array(), $data = '', $method = 'GET') {
        $opts = array(
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        );

        if (stripos ( $url, "https://" ) !== FALSE) {
            $opts[CURLOPT_SSL_VERIFYPEER] = false;
            $opts[CURLOPT_SSL_VERIFYHOST] = false;
        }

        if($param) {
            if (stripos ( $url, "?" )) $url .= '&' . http_build_query($param);
            else $url .= '?' . http_build_query($param);
        }

        /* 根据请求类型设置特定参数 */
        $opts[CURLOPT_URL] = $url;

        if(strtoupper($method) == 'POST') {
            $opts[CURLOPT_POST] = 1;
            $opts[CURLOPT_POSTFIELDS] = $data;

            if(is_string($data)) { //发送JSON数据
                $opts[CURLOPT_HTTPHEADER] = array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length: ' . strlen($data),
                );
            }
        }

        /* 初始化并执行curl请求 */
        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $content  = curl_exec($ch);
        $status = curl_getinfo ( $ch );
        curl_close($ch);

        if (intval ( $status ["http_code"] ) == 200) {
            return $content;
        } else {
            return false;
        }
    }

// ------------------------------------------------------------------

    /**
     * 构造文本信息
     * @param  string $content 要回复的文本
     */
    private static function text($content) {
        $data['content'] = $content;
        return $data;
    }

    /**
     * 构造图片信息
     * @param  integer $media 图片ID
     */
    private static function image($media) {
        $data['media_id'] = $media;
        return $data;
    }

    /**
     * 构造音频信息
     * @param  integer $media 语音ID
     */
    private static function voice($media) {
        $data['media_id'] = $media;
        return $data;
    }

    /**
     * 构造视频信息
     * @param  array $video 要回复的视频 [视频ID，标题，说明]
     */
    private static function video($video) {
        $data = array();
        list(
            $data['media_id'],
            $data['title'],
            $data['description'],
        ) = $video;

        return $data;
    }

    /**
     * 构造音乐信息
     * @param  array $music 要回复的音乐[标题，说明，链接，高品质链接，缩略图ID]
     */
    private static function music($music) {
        $data = array();
        list(
            $data['title'],
            $data['description'],
            $data['musicurl'],
            $data['hqmusicurl'],
            $data['thumb_media_id'],
        ) = $music;

        return $data;
    }

    /**
     * 构造图文信息
     * @param  array $news 要回复的图文内容
     * [
     *      0 => 第一条图文信息[标题，说明，图片链接，全文连接]，
     *      1 => 第二条图文信息[标题，说明，图片链接，全文连接]，
     *      2 => 第三条图文信息[标题，说明，图片链接，全文连接]，
     * ]
     */
    private static function news($news) {
        $articles = array();
        foreach ($news as $key => $value) {
            list(
                $articles[$key]['title'],
                $articles[$key]['description'],
                $articles[$key]['url'],
                $articles[$key]['picurl']
            ) = $value;

            if($key >= 9) break; //最多只允许10条图文信息
        }

        $data['articles']     = $articles;
        return $data;
    }

    // ------------------------------------------------------------------

    /**
     * 客服帐号管理 - 添加客服帐号
     * 请注意，必须先在公众平台官网为公众号设置微信号后才能使用该能力。
     * 开发者可以通过本接口为公众号添加客服账号，每个公众号最多添加10个客服账号。
     *
     * @param $kfAccount String 完整客服账号，格式为：账号前缀@公众号微信号
     * @param $nickname String 昵称
     * @param $password String 密码
     *
     */
    public function addAccount($kfAccount, $nickname, $password) {
        $data = array();
        $data['kf_account'] = $kfAccount;
        $data['nickname'] = $nickname;
        $data['password'] = $password;
        $data = json_encode($data);
        return $this->api('customservice/kfaccount/add', $data, 'POST');
    }

    /**
     * 客服帐号管理 - 修改客服帐号
     * 请注意，必须先在公众平台官网为公众号设置微信号后才能使用该能力。
     *
     * @param $kfAccount String 完整客服账号，格式为：账号前缀@公众号微信号
     * @param $nickname String 昵称
     * @param $password String 密码
     *
     */
    public function editAccount($kfAccount, $nickname, $password) {
        $data = array();
        $data['kf_account'] = $kfAccount;
        $data['nickname'] = $nickname;
        $data['password'] = $password;
        $data = json_encode($data);
        return $this->api('customservice/kfaccount/update', $data, 'POST');
    }

    /**
     * 客服帐号管理 - 删除客服帐号
     * 请注意，必须先在公众平台官网为公众号设置微信号后才能使用该能力。
     *
     * @param $kfAccount String 完整客服账号，格式为：账号前缀@公众号微信号
     * @param $nickname String 昵称
     * @param $password String 密码
     *
     */
    public function delAccount($kfAccount) {
        $data = array();
        $data['kf_account'] = $kfAccount;
        return $this->api('customservice/kfaccount/del', null, 'GET', $data);
    }

    /**
     * 客服帐号管理 - 获取所有客服账号
     * 请注意，必须先在公众平台官网为公众号设置微信号后才能使用该能力。
     */
    public function getAccountList() {
        $json = $this->api('cgi-bin/customservice/getkflist', null, 'GET');
        $kflist =array();
        //补充昵称
        foreach ($json->kf_list as $value ) {
            $kflist[] = $value->kf_account;
        }

        return $kflist;
    }

    /**
     * 客服帐号管理 - 获取在线客服接待信息
     * 请注意，必须先在公众平台官网为公众号设置微信号后才能使用该能力。
     */
    public function getOnlineKFList() {
        $json = $this->api('cgi-bin/customservice/getonlinekflist', null, 'GET');
        $kf_onlinelists = $json->kf_online_list;
        $kflist =array();
        //补充昵称
        foreach ($kf_onlinelists as $value ) {
            $kflist[] = $value->kf_account;
        }

        return $kflist;
    }

    /**
     * 客服帐号管理 - 设置客服帐号的头像
     * 请注意，必须先在公众平台官网为公众号设置微信号后才能使用该能力。
     *
     * 可调用本接口来上传图片作为客服人员的头像，头像图片文件必须是jpg格式，推荐使用640*640大小的图片以达到最佳效果
     *
     * @param $kfAccount String 完整客服账号，格式为：账号前缀@公众号微信号
     * @param $imagePath String 待上传的头像文件路径
     *
     */
    public function setAccountImage($kfAccount, $imagePath) {
        if(!file_exists($imagePath)) {
            return false;
        }
        $data = array();
        $data['media'] = '@'.$imagePath;
        $param['kf_account'] = $kfAccount;
        return $this->api('customservice/kfaccount/uploadheadimg', $data, 'POST', $param);
    }

    /**
     * 获取客服聊天记录接口，有分页，一次获取一页，一页最多1000条
     * 在需要时，开发者可以通过获取客服聊天记录接口，获取多客服的会话记录，包括客服和用户会话的所有消息记录和会话的创建、关闭等操作记录。利用此接口可以开发如“消息记录”、“工作监控”、“客服绩效考核”等功能
     *
     * @param $startTime 查询开始时间，UNIX时间戳
     * @param $endTime 查询结束时间，UNIX时间戳，每次查询不能跨日查询
     * @param $pageIndex 查询第几页，从1开始
     * @param $pageSize 每页大小，每页最多拉取1000条
     *
     */
    public function getRecord($startTime, $endTime, $pageIndex=1, $pageSize=1000) {
        $data = array();
        $data['starttime'] = $startTime;
        $data['endtime'] = $endTime;
        $data['openid'] = $openId;
        $data['pagesize'] = $pageSize;
        $data['pageindex'] = $pageIndex;
        $data = json_encode($data);
        return $this->api('customservice/getrecord', $data, 'POST');
    }

    /**
     * 创建会话
     *
     * 开发者可以使用本接口，为多客服的客服工号创建会话，将某个客户直接指定给客服工号接待，需要注意此接口不会受客服自动接入数以及自动接入开关限制。只能为在线的客服（PC客户端在线，或者已绑定多客服助手）创建会话。
     */
    public function kfSessionCreate($kf_account, $openid, $text) {
        $data['kf_account'] = $kf_account;
        $data['openid'] = $openid;
        $data['text'] = $text;
        $res = $this->api('customservice/kfsession/create', $data, 'POST');
        $res = json_decode($res, true);
        if($res['errcode'] == 0) return true;
        else return false;
    }

    /**
     * 关闭会话
     *
     * 开发者可以使用本接口，关闭一个会话。
     */
    public function kfSessionClose($kf_account, $openid, $text) {
        $data['kf_account'] = $kf_account;
        $data['openid'] = $openid;
        $data['text'] = $text;
        $res = $this->api('customservice/kfsession/close', $data, 'POST');
        $res = json_decode($res, true);
        if($res['errcode'] == 0) return true;
        else return false;
    }

    /**
     * 获取客户的会话状态
     *
     * 开发者可以通过本接口获取客户当前的会话状态。
     */
    public function kfSessionGet($openid) {
        $param['openid'] = $openid;
        $res = $this->api('customservice/kfsession/getsession', null, 'GET', $param);
        $res = json_decode($res, true);
        if($res['errcode'] == 0) return true;
        else return false;
    }

    /**
     * 获取客服的会话列表
     *
     * 开发者可以通过本接口获取某个客服正在接待的会话列表。
     */
    public function kfSessionList($kf_account) {
        $param['kf_account'] = $kf_account;
        return $this->api('customservice/kfsession/getsessionlist', null, 'GET', $param);
    }

    /**
     * 获取未接入会话列表
     *
     * 开发者可以通过本接口获取当前正在等待队列中的会话列表，此接口最多返回最早进入队列的100个未接入会话。
     */
    public function kfSessionGetwaitcase() {
        return $this->api('customservice/kfsession/getwaitcase', null, 'GET');
    }

    /**
     * 客服接口-发消息
     */
    public function sendByCustomer($data) {
        $res = $this->api('cgi-bin/message/custom/send', $data, 'POST');
    }

    // ------------------------------------------------------------------

    public function sendTemplate($data) {
        return $res = $this->api('cgi-bin/message/template/send', $data, 'POST');
    }
}
