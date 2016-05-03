<?php
/**
 * 微信交互控制器
 * 主要获取和反馈微信平台的数据
 * @Author: liulu72056
 * @Date:   2015-07-02 12:06:37
 * @Last Modified by:   liulu72056
 * @Last Modified time: 2015-07-13 12:33:08
 */
namespace Home\Controller;

class WechatController extends HomeController {
    private $wechat;
    private $data = array ();

    public function index() {
        $this->wechat = D ( 'Wechat' );
        // 获取数据
        $data = $this->wechat->getData ();
        $this->data = $data;

        $this->token = $data ['ToUserName'];

        $this->follow($data);

        // 回复数据
        $this->reply ( $data );

        // 结束程序。防止thinkphp框架的调试信息输出
        exit ();
    }

    private function reply($data) {
        $msgtype = $data['msgtype'];
        if($msgtype != 'event') {
            addWeixinLog ( $data, $GLOBALS ['HTTP_RAW_POST_DATA'], $msgtype );
        }
        switch ($msgtype) {
            //事件
            case 'event':
                $event = strtolower($data['event']);
                addWeixinLog ( $data, $GLOBALS ['HTTP_RAW_POST_DATA'], $event . 'Event' );
                // 记录日志
                switch ($event) {
                    //关注
                    case 'subscribe':
                        //二维码关注
                        if(isset($data['eventkey']) && isset($data['ticket'])){
                            self::eventQrsceneSubscribe($data);
                        //普通关注
                        }else{
                            self::eventSubscribe($data);
                        }
                        break;
                    //扫描二维码
                    case 'scan':
                        self::eventScan($data);
                        break;
                    //地理位置
                    case 'location':
                        self::eventLocation($data);
                        break;
                    //自定义菜单 - 点击菜单拉取消息时的事件推送
                    case 'click':
                        self::eventClick($data);
                        break;
                    //自定义菜单 - 点击菜单跳转链接时的事件推送
                    case 'view':
                        self::eventView($data);
                        break;
                    //自定义菜单 - 扫码推事件的事件推送
                    case 'scancode_push':
                        self::eventScancodePush($data);
                        break;
                    //自定义菜单 - 扫码推事件且弹出“消息接收中”提示框的事件推送
                    case 'scancode_waitmsg':
                        self::eventScancodeWaitMsg($data);
                        break;
                    //自定义菜单 - 弹出系统拍照发图的事件推送
                    case 'pic_sysphoto':
                        self::eventPicSysPhoto($data);
                        break;
                    //自定义菜单 - 弹出拍照或者相册发图的事件推送
                    case 'pic_photo_or_album':
                        self::eventPicPhotoOrAlbum($data);
                        break;
                    //自定义菜单 - 弹出微信相册发图器的事件推送
                    case 'pic_weixin':
                        self::eventPicWeixin($data);
                        break;
                    //自定义菜单 - 弹出地理位置选择器的事件推送
                    case 'location_select':
                        self::eventLocationSelect($data);
                        break;
                    //取消关注
                    case 'unsubscribe':
                        self::eventUnsubscribe($data);
                        break;
                    //群发接口完成后推送的结果
                    case 'masssendjobfinish':
                        self::eventMassSendJobFinish($data);
                        break;
                    //模板消息完成后推送的结果
                    case 'templatesendjobfinish':
                        self::eventTemplateSendJobFinish($data);
                        break;
                    //会话状态通知事件 - 接入会话
                    case 'kf_create_session':
                        self::eventKFCreateSession($data);
                        break;
                    //会话状态通知事件 - 关闭会话
                    case 'kf_close_session':
                        self::eventKFCloseSession($data);
                        break;
                    //会话状态通知事件 - 转接会话
                    case 'kf_switch_session':
                        self::eventKFSwitchSession($data);
                        break;
                    default:
                        $this->wechat->replyText('收到了未知类型的消息');
                        return;
                }
                break;
            //文本
            case 'text':
                self::text($data);
                break;
            //图像
            case 'image':
                self::image($data);
                break;
            //语音
            case 'voice':
                self::voice($data);
                break;
            //视频
            case 'video':
                self::video($data);
                break;
            //小视频
            case 'shortvideo':
                self::shortvideo($data);
                break;
            //位置
            case 'location':
                self::location($data);
                break;
            //链接
            case 'link':
                self::link($data);
                break;
            default:
                $this->wechat->replyText('收到未知的消息，我不知道怎么处理');
        }
    }

    /**
     * @descrpition 文本
     * @param $data
     */
    private function text($data){
        $content = $data['content'];
        $arr = array('我今天累了，明天再陪你聊天吧'
                    ,'哈哈~~'
                    ,'你话好多啊，不跟你聊了'
                    ,'亲，有快件要取吗，我可以帮你哦'
                    ,'亲，有快件要寄吗，我可以帮你哦'
                    ,'又和我聊天了呀');
        $k = array_rand($arr);
        // $content = '收到文本消息,内容是 '.$content;
        $this->wechat->replyText($arr[$k]);
    }

    /**
     * @descrpition 图像
     * @param $data
     */
    private function image($data){
        $content = '收到图片';
        $this->wechat->replyText($content);
    }

    /**
     * @descrpition 语音
     * @param $data
     */
    private function voice($data){
        if(!isset($data['recognition'])){
            $content = '收到语音';
            $this->wechat->replyText($content);
        }else{
            $content = '收到语音识别消息，语音识别结果为：'.$data['recognition'];
            $this->wechat->replyText($content);
        }
    }

    /**
     * @descrpition 视频
     * @param $data
     */
    private function video($data){
        $content = '收到视频';
        $this->wechat->replyText($content);
    }

    /**
     * @descrpition 视频
     * @param $data
     */
    private function shortvideo($data){
        $content = '收到小视频';
        $this->wechat->replyText($content);
    }

    /**
     * @descrpition 地理
     * @param $data
     */
    private function location($data){
        $content = '收到上报的地理位置';
        if($data['label']) $content .= "\n地址：".$data['label'];
        $this->wechat->replyText($content);
    }

    /**
     * @descrpition 链接
     * @param $data
     */
    private function link($data){
        $content = '收到连接';
        $this->wechat->replyText($content);
    }

    /**
     * @descrpition 关注
     * @param $data
     */
    private function eventSubscribe($data){
        $Follow = M('follow');
        $Follow->where(array('openid'=>$data['fromusername']))->find();
        $Follow->status = 1;
        $Follow->save();

        $content = '欢迎您关注我们的微信，将为您竭诚服务';
        $this->wechat->replyText($content);
    }

    /**
     * @descrpition 取消关注
     * @param $data
     */
    private function eventUnsubscribe($data){
        $Follow = M('follow');
        $Follow->where(array('openid'=>$data['fromusername']))->find();
        $Follow->status = 2;
        $Follow->save();

        $content = '为什么不理我了？';
        $this->wechat->replyText($content);
    }

    /**
     * @descrpition 扫描二维码关注（未关注时）
     * @param $data
     */
    private function eventQrsceneSubscribe($data){
        $Follow = M('follow');
        $Follow->where(array('openid'=>$data['fromusername']))->find();
        $Follow->status = 1;
        $Follow->save();

        $content = '欢迎您关注我们的微信，将为您竭诚服务';
        $this->wechat->replyText($content);
    }

    /**
     * @descrpition 扫描二维码（已关注时）
     * @param $data
     */
    private function eventScan($data){
        $content = '您已经关注了哦～';
        $this->wechat->replyText($content);
    }

    /**
     * @descrpition 上报地理位置
     * @param $data
     */
    private function eventLocation($data){
        $fromusername = $data['fromusername'];
        if(! S ('useraddr_'.$fromusername)) {
            $latitude = $data ['latitude'];
            $longitude = $data ['longitude'];

            $res = wp_file_get_contents ( 'http://apis.map.qq.com/ws/geocoder/v1/?key='.QQMAPKEY.'&location='.$latitude.','.$longitude );
            $res = json_decode ( $res, true );
            $adinfo = $res['result']['ad_info'];
            $info['city'] = $adinfo['city'];
            if(!empty($adinfo['district'])) {
                $info['district'] = $adinfo['district'];
            }
            S ('useraddr_'.$fromusername, $info, 3600);
        }
        $adinfo = S ('useraddr_'.$fromusername);
        // $this->wechat->replyText('收到上报的地理位置 ' . $adinfo['city'] . $adinfo['district']);
    }

    /**
     * @descrpition 自定义菜单 - 点击菜单拉取消息时的事件推送
     * @param $data
     */
    private function eventClick($data){
        //获取该分类的信息
        $eventKey = $data['eventkey'];
        $openid = $data['fromusername'];
        $map = array('openid' => $openid);
        $info = M ( 'member' )->where($map)->find();
        if(empty($info)) {
            $content = '亲，为了更好更快的给您提供服务，请您先<a href="http://dtmao.sinaapp.com/m/myinfo">完善您的个人信息</a>，谢谢您的配合';
            $this->wechat->replyText($content);
        } else {

        }
    }

    /**
     * @descrpition 自定义菜单 - 点击菜单跳转链接时的事件推送
     * @param $data
     */
    private function eventView($data){

    }

    /**
     * @descrpition 自定义菜单 - 扫码推事件的事件推送
     * @param $data
     */
    private function eventScancodePush($data){
        //获取该分类的信息
        $eventKey = $data['eventkey'];
        $content = '收到扫码推事件的事件，您设置的key是' . $eventKey;
        $content .= '。扫描信息：'.$data['scancodeinfo'];
        $content .= '。扫描类型(一般是qrcode)：'.$data['scantype'];
        $content .= '。扫描结果(二维码对应的字符串信息)：'.$data['scanresult'];
        $this->wechat->replyText($content);
    }

    /**
     * @descrpition 自定义菜单 - 扫码推事件且弹出“消息接收中”提示框的事件推送
     * @param $data
     */
    private function eventScancodeWaitMsg($data){
        //获取该分类的信息
        $eventKey = $data['eventkey'];
        $content = '收到扫码推事件且弹出“消息接收中”提示框的事件，您设置的key是' . $eventKey;
        $content .= '。扫描信息：'.$data['scancodeinfo'];
        $content .= '。扫描类型(一般是qrcode)：'.$data['scantype'];
        $content .= '。扫描结果(二维码对应的字符串信息)：'.$data['scanresult'];
        $this->wechat->replyText($content);
    }

    /**
     * @descrpition 自定义菜单 - 弹出系统拍照发图的事件推送
     * @param $data
     */
    private function eventPicSysPhoto($data){
        //获取该分类的信息
        $eventKey = $data['eventkey'];
        $content = '收到弹出系统拍照发图的事件，您设置的key是' . $eventKey;
        $content .= '。发送的图片信息：'.$data['sendpicsinfo'];
        $content .= '。发送的图片数量：'.$data['count'];
        $content .= '。图片列表：'.$data['piclist'];
        $content .= '。图片的MD5值，开发者若需要，可用于验证接收到图片：'.$data['picmd5sum'];
        $this->wechat->replyText($content);
    }

    /**
     * @descrpition 自定义菜单 - 弹出拍照或者相册发图的事件推送
     * @param $data
     */
    private function eventPicPhotoOrAlbum($data){
        //获取该分类的信息
        $eventKey = $data['eventkey'];
        $content = '收到弹出拍照或者相册发图的事件，您设置的key是' . $eventKey;
        $content .= '。发送的图片信息：'.$data['sendpicsinfo'];
        $content .= '。发送的图片数量：'.$data['count'];
        $content .= '。图片列表：'.$data['piclist'];
        $content .= '。图片的MD5值，开发者若需要，可用于验证接收到图片：'.$data['picmd5sum'];
        $this->wechat->replyText($content);
    }

    /**
     * @descrpition 自定义菜单 - 弹出微信相册发图器的事件推送
     * @param $data
     */
    private function eventPicWeixin($data){
        //获取该分类的信息
        $eventKey = $data['eventkey'];
        $content = '收到弹出微信相册发图器的事件，您设置的key是' . $eventKey;
        $content .= '。发送的图片信息：'.$data['sendpicsinfo'];
        $content .= '。发送的图片数量：'.$data['count'];
        $content .= '。图片列表：'.$data['piclist'];
        $content .= '。图片的MD5值，开发者若需要，可用于验证接收到图片：'.$data['picmd5sum'];
        $this->wechat->replyText($content);
    }

    /**
     * @descrpition 自定义菜单 - 弹出地理位置选择器的事件推送
     * @param $data
     */
    private function eventLocationSelect($data){
        //获取该分类的信息
        $eventKey = $data['eventkey'];
        $content = '收到点击跳转事件，您设置的key是' . $eventKey;
        $content .= '。发送的位置信息：'.$data['sendlocationinfo'];
        $content .= '。X坐标信息：'.$data['location_x'];
        $content .= '。Y坐标信息：'.$data['location_y'];
        $content .= '。精度(可理解为精度或者比例尺、越精细的话 scale越高)：'.$data['scale'];
        $content .= '。地理位置的字符串信息：'.$data['label'];
        $content .= '。朋友圈POI的名字，可能为空：'.$data['poiname'];
        $this->wechat->replyText($content);
    }

    /**
     * 群发接口完成后推送的结果
     *
     * 本消息有公众号群发助手的微信号“mphelper”推送的消息
     * @param $data
     */
    private function eventMassSendJobFinish($data){
        //发送状态，为“send success”或“send fail”或“err(num)”。但send success时，也有可能因用户拒收公众号的消息、系统错误等原因造成少量用户接收失败。err(num)是审核失败的具体原因，可能的情况如下：err(10001), //涉嫌广告 err(20001), //涉嫌政治 err(20004), //涉嫌社会 err(20002), //涉嫌色情 err(20006), //涉嫌违法犯罪 err(20008), //涉嫌欺诈 err(20013), //涉嫌版权 err(22000), //涉嫌互推(互相宣传) err(21000), //涉嫌其他
        $status = $data['status'];
        //计划发送的总粉丝数。group_id下粉丝数；或者openid_list中的粉丝数
        $totalCount = $data['totalcount'];
        //过滤（过滤是指特定地区、性别的过滤、用户设置拒收的过滤，用户接收已超4条的过滤）后，准备发送的粉丝数，原则上，FilterCount = SentCount + ErrorCount
        $filterCount = $data['filtercount'];
        //发送成功的粉丝数
        $sentCount = $data['sentcount'];
        //发送失败的粉丝数
        $errorCount = $data['errorcount'];
        $content = '发送完成，状态是'.$status.'。计划发送总粉丝数为'.$totalCount.'。发送成功'.$sentCount.'人，发送失败'.$errorCount.'人。';
        $this->wechat->replyText($content);
    }

    /**
     * 模板消息完成后推送的结果
     *
     * @param $data
     */
    private function eventTemplateSendJobFinish($data){
        //发送状态，成功success，用户拒收failed:user block，其他原因发送失败failed: system failed
        $status = $data['status'];
        if($status == 'success'){
            //发送成功
        }else if($status == 'failed:user block'){
            //因为用户拒收而发送失败
        }else if($status == 'failed: system failed'){
            //其他原因发送失败
        }
    }

    /**
     * 会话状态通知事件 - 接入会话
     *
     * @param $data
     */
    private function eventKFCreateSession($data) {
        $this->wechat->sendTextByCustomer('会话已接入');
    }

    /**
     * 会话状态通知事件 - 关闭会话
     *
     * @param $data
     */
    private function eventKFCloseSession($data) {
        $this->wechat->sendTextByCustomer('会话已关闭');
    }

    /**
     * 会话状态通知事件 - 转接会话
     *
     * @param $data
     */
    private function eventKFSwitchSession($data) {
        $this->wechat->sendTextByCustomer('会话已转接');
    }


    // 保存关键词的请求数
    private function request_count($keywordArr) {
        $map ['id'] = $keywordArr ['id'];
        M ( 'keyword' )->where ( $map )->setInc ( 'request_count' );
    }

    private function follow($data) {
        $openid = $data['fromusername'];
        $Follow = M('follow');
        $map = array('openid'=>$openid);
        $userinfo = $Follow->where($map)->find();
        if(empty($userinfo)) {
            $wechatAuth = D('WechatAuth');
            $userinfo = $wechatAuth->userInfo($openid);
            $userinfo = json_decode($userinfo, true);
            $userinfo['mTime'] = NOW_TIME;
            $userinfo['openid'] = $openid;
            $Follow->add($userinfo);
        } else {
            $info = $Follow->where($map)->find();
            if(NOW_TIME - $info['mtime'] > 432000) {
                $wechatAuth = D('WechatAuth');
                $userinfo = $wechatAuth->userInfo($openid);
                $userinfo = json_decode($userinfo, true);
                $userinfo['mTime'] = NOW_TIME;
                $userinfo['id'] = $info['id'];
                $Follow->save($userinfo);
            }
        }
    }
}
?>
