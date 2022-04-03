<?php


namespace YangChengEasyComposer\Wechat;

use think\Request;
use think\facade\Cache;
use think\facade\Log;
use YangChengEasyComposer\Base;

/**
 * 微信公众号类库
 * Class WechatOfficial
 * @package YangChengEasy\Wechat
 */
class WechatOfficial extends Base
{
    public $apiBaseUrl = 'https://api.weixin.qq.com';   //  微信公众平台接口域名，含有http
    private $appId = '';        //  微信公众号APPPID
    private $appSecret = '';    //  微信公众号appSecret
    private $token = '';        //  微信公众号token
    private $aesKey = '';       //  微信公众号消息加解密密钥
    private $lodId = '';        //  微信公众号原始ID
    public $access_token = ''; //  微信公众号assessToken，两小时更新一次

    protected $errNumber = 3; // 错误重试次数

    /**
     * 公众号类实例化
     * WechatOfficial constructor.
     * @param array $config 公众号配置
     * @param Request|null $request
     */
    public function __construct($config=[], Request $request = null)
    {
        parent::__construct($request);
        if(count($config)<2){
            throw new \think\Exception('请先进行配置实例化');
        }
        if(empty($config['appid'])){
            throw new \think\Exception('公众号appid不能为空');
        }
        if(empty($config['secret'])){
            throw new \think\Exception('公众号secret不能为空');
        }
        $this->appId = $config['appid'];
        $this->appSecret = $config['secret'];
        $this->token = empty($config['token'])?'':$config['token'];
        $this->lodId = empty($config['lodId'])?'':$config['lodId'];
        $this->aesKey = empty($config['aesKey'])?'':$config['aesKey'];
        $this->getAccessToken();
    }

    /**
     * 获取公众号token
     * @return mixed|null
     * @throws \think\Exception
     * @author YangCheng 2022年3月31日14:59:19
     */
    public function getAccessToken()
    {
        if(Cache::get('wechatOfficialAccessToken')){
            $this->access_token = Cache::get('wechatOfficialAccessToken');
            return Cache::get('wechatOfficialAccessToken');
        }
        $url = '/cgi-bin/token';
        // 参数
        $pram = [
            'grant_type' => 'client_credential',
            'appid' => $this->appId,
            'secret' => $this->appSecret,
        ];
        // 发起请求
        try{
            $result = $this->http($this->apiBaseUrl.$url, $pram);
            $result = json_decode($result,true);
            if (isset($result['errcode']) && $result['errcode'] != 0 || !$result){
                Log::error('yangchengEasy中WechatOfficial类getAccessToken方法出现错误：'.$result['errmsg']);
                throw new \think\Exception($result['errmsg']);
            }
            $access_token = $result['access_token'];
            // 载入配置到信息
            $this->access_token = $access_token;
            Cache::set('wechatOfficialAccessToken',$access_token,$result['expires_in']);
            return $access_token;
        }catch (\Exception $e){
            Log::error('yangchengEasy中WechatOfficial类getAccessToken方法出现错误：'.$e);
            throw new \think\Exception($e->getMessage());
        }
    }

    /**
     * 获取js调用凭证
     * @return |null
     */
    public function get_jsapi_ticket(){
        if(Cache::get('wx_jsapi_ticket')){
            return Cache::get('wx_jsapi_ticket');
        }
        $url = '/cgi-bin/ticket/getticket';
        // 参数
        $pram = [
            'type' => 'jsapi',
            'access_token' => $this->access_token,
        ];
        // 发起请求
        try{
            $result = $this->http($this->apiBaseUrl.$url, $pram);
            $result = json_decode($result,true);
            if (isset($result['errcode']) && $result['errcode'] != 0 || !$result){
                Log::error('yangchengEasy中get_jsapi_ticket方法出现错误：'.$result['errmsg']);
                return null;
            }
            $ticket = $result['ticket'];
            // 数据入缓存
            Cache::set('wx_jsapi_ticket',$ticket,7200);
            return $ticket;
        }catch (\Exception $e){
            Log::error('yangchengEasy中get_jsapi_ticket方法出现错误：'.$e);
            return null;
        }
    }

    /**
     * 获取微信JS调用加密字符串
     * @param $noncestr
     * @param $timestamp
     * @param $url
     * @return string
     */
    public function get_js_signature($noncestr, $timestamp, $url):string{
        $jsapi_ticket =  $this->get_jsapi_ticket();
        $str = 'jsapi_ticket='.$jsapi_ticket.'&noncestr='.$noncestr.'&timestamp='.$timestamp.'&url='.$url;
        $sha1 = sha1($str);
        return $sha1;
    }

    /**
     * 获取微信用户信息
     * @param $openid
     * @return array
     * @author YangCheng 2022年3月31日17:00:09
     */
    public function getUserInfo($openid):array{
        $url = '/cgi-bin/user/info';
        // 参数
        $pram = [
            "access_token" => $this->access_token,
            "openid" => trim($openid),
        ];

        // 发起请求
        try{
            $result = $this->http($this->apiBaseUrl.$url, $pram);
            $result = json_decode($result,true);
            if (isset($result['errcode']) && $result['errcode'] != 0 || !$result){
                if ($result['errcode'] == '40001'){
                    if ($this->errNumber > 0){
                        $this->getAccessToken();
                        Log::info('yangchengEasy中WechatOfficial类getUserInfo方法出现错误：原因可能是access_token失效，系统已尝试重新获取了一次access_token，并重新执行本方法，'.$result['errmsg']);
                        $this->errNumber --;
                        return $this->getUserInfo($openid);
                    }else{
                        Log::error('yangchengEasy中WechatOfficial类getUserInfo方法出现错误：原因可能是access_token失效，系统已尝试重新获取，但已超过重试次数，故停止重试！'.$result['errmsg']);
                        $this->errNumber = 3;
                        return [];
                    }
                }
                Log::error('yangchengEasy中WechatOfficial类getUserInfo方法出现错误：'.'错误码：'.$result['errcode'].$result['errmsg'].'，openid：'.$openid);
                return [];
            }
            $this->errNumber = 3;
            return $result;

        }catch (\Exception $e){
            Log::error('yangchengEasy中WechatOfficial类getUserInfo方法出现错误：'.$e);
            return [];
        }
    }

    /**
     * 用户关注操作
     * @param $object
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author YangCheng 2022年3月31日17:20:09
     */
    public function subscribe($object){
        $openid = $object->FromUserName;
        // 获取用户信息
        $wxUserInfo = $this->getUserInfo($openid);
        switch ($wxUserInfo['subscribe_scene']){
            case 'ADD_SCENE_SEARCH':
                $wx_scene = '公众号搜索';
                break;
            case 'ADD_SCENE_ACCOUNT_MIGRATION':
                $wx_scene = '公众号迁移';
                break;
            case 'ADD_SCENE_PROFILE_CARD':
                $wx_scene = '名片分享';
                break;
            case 'ADD_SCENE_QR_CODE':
                $wx_scene = '扫描二维码';
                break;
            case 'ADD_SCENE_PROFILE_LINK':
                $wx_scene = '图文页内名称点击';
                break;
            case 'ADD_SCENE_PROFILE_ITEM':
                $wx_scene = '图文页右上角菜单';
                break;
            case 'ADD_SCENE_PAID':
                $wx_scene = '支付后关注';
                break;
            case 'ADD_SCENE_WECHAT_ADVERTISEMENT':
                $wx_scene = '微信广告';
                break;
            case 'ADD_SCENE_OTHERS':
                $wx_scene = '其他来源';
                break;

        }

        return $this->receiveText($object,'发送给用户的文本');
    }

    /**
     * 用户取消关注
     * @param $object
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function unsubscribe($object){
        $openid = $object->FromUserName;
        // todo 这里写具体逻辑
        return $this->receiveText($object,'这里是发送给用户的消息，但是这里是取消关注，貌似没用');
    }

    /**
     * 使用code换取网页授权信息，包含openid
     * @param $code
     * @return array|bool|mixed|string
     */
    public function codeToAccessToken($code){
        $url = '/sns/oauth2/access_token';

        $pram = [
            'appid'=>$this->appId,
            'secret'=>$this->appSecret,
            'code'=>$code,
            'grant_type'=>'authorization_code',
        ];

        // 发起请求
        try{
            $result = $this->http($this->apiBaseUrl.$url, $pram);
            $result = json_decode($result,true);
            if (isset($result['errcode']) && $result['errcode'] != 0 || !$result){
                if ($result['errcode'] == '40001'){
                    if ($this->errNumber > 0){
                        $this->getAccessToken();
                        Log::info('yangchengEasy中codeToAccessToken方法出现错误：原因可能是access_token失效，系统已尝试重新获取了一次access_token，并重新执行本方法，'.$result['errmsg']);
                        $this->errNumber --;
                        return $this->codeToAccessToken($code);
                    }else{
                        Log::error('yangchengEasy中codeToAccessToken方法出现错误：原因可能是access_token失效，系统已尝试重新获取，但已超过重试次数，故停止重试！'.$result['errmsg']);
                        $this->errNumber = 3;
                        return [];
                    }
                }
                Log::error('yangchengEasy中codeToAccessToken方法出现错误：'.'错误码：'.$result['errcode'].$result['errmsg']);
                return [];
            }
            $this->errNumber = 3;
            return $result;

        }catch (\Exception $e){
            Log::error('yangchengEasy中codeToAccessToken方法出现错误：'.$e);
            return [];
        }

    }

    /**
     * 生成二维码
     * @param string $scene_id 场景值（整数）
     * @param string $action_name 二维码类型，QR_SCENE为临时的整型参数值，QR_STR_SCENE为临时的字符串参数值，QR_LIMIT_SCENE为永久的整型参数值，QR_LIMIT_STR_SCENE为永久的字符串参数值
     * @param string $expire_seconds 该二维码有效时间，以秒为单位。 最大不超过2592000（即30天），此字段如果不填，则默认有效期为120秒。
     * @return array|bool|mixed|string
     */
    public function create_qrCode($scene_id='', $action_name='QR_SCENE', $expire_seconds='120'){
        $url = '/cgi-bin/qrcode/create?access_token='.$this->access_token;

        $pram = [
            'expire_seconds'=>$expire_seconds,
            'action_name'=>$action_name,
            'action_info'=>[
                'scene'=>[
                    "scene_id"=>$scene_id
                ]
            ],
        ];

        $pram = json_encode($pram);

        // 发起请求
        try{
            $result = $this->http($this->apiBaseUrl.$url, $pram, 'POST',  array('Content-Type: application/json','Content-Length: ' . strlen($pram)), true);
            $result = json_decode($result,true);
            if (isset($result['errcode']) && $result['errcode'] != 0 || !$result){
                if ($result['errcode'] == '40001'){
                    $this->getAccessToken();
                    Log::error('yangchengEasy中create_qrCode方法出现错误：原因可能是access_token失效，系统已尝试重新获取了一次access_token，'.$result['errmsg']);
                    return null;
                }
                Log::error('yangchengEasy中create_qrCode方法出现错误：'.'错误码：'.$result['errcode'].$result['errmsg']);
                return [];
            }
            return $result;

        }catch (\Exception $e){
            Log::error('yangchengEasy中create_qrCode方法出现错误：'.$e);
            return [];
        }
    }

    /**
     * 通过ticket获取二维码图片地址
     * @param array $result
     * @return string
     */
    public function exchange_qrCode($result = []){
        if (empty($result) || !isset($result['ticket'])){
            return '';
        }

        $ticket = urlencode($result['ticket']);

        return "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=".$ticket;
    }

    /**
     * 发送文本消息
     * @param $object
     * @return string
     */
    public function receiveText($object,$content){
        $result = $this->transmitText($object, $content);
        return $result;
    }

    /**
     * 接收图片消息
     * @param $object
     * @return string
     */
    public function receiveImage($object){
        $content = "你发送的是图片，地址为：".$object->PicUrl;
        $result = $this->transmitText($object, $content);
        return $result;
    }

    /**
     * 接收语音消息
     * @param $object
     * @return string
     */
    public function receiveVoice($object){
        $result = $this->transmitText($object, '暂不支持语音信息哟');
        return $result;
    }

    /**
     * 接收视频消息
     * @param $object
     * @return string
     */
    public function receiveVideo($object){
        $content = "你发送的是视频，媒体ID为：".$object->MediaId;
        $result = $this->transmitText($object, $content);
        return $result;
    }

    /**
     * 接收位置消息
     * @param $object
     * @return string
     */
    public function receiveLocation($object){
        $content = "你发送的是位置，纬度为：".$object->Location_X."；经度为：".$object->Location_Y."；缩放级别为：".$object->Scale."；位置为：".$object->Label;
        $result = $this->transmitText($object, $content);
        return $result;
    }

    /**
     * 接收链接消息
     * @param $object
     * @return string
     */
    public function receiveLink($object){
        $content = "你发送的是链接，标题为：".$object->Title."；内容为：".$object->Description."；链接地址为：".$object->Url;
        $result = $this->transmitText($object, $content);
        return $result;
    }

    /**
     * 文本消息内容
     * @param $object
     * @param $content
     * @return string
     */
    public function transmitText($object, $content){
        $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                    </xml>";

        if (!isset($object->FromUserName) || empty($object->FromUserName)){
            $object->FromUserName = $this->lodId;
        }

        $result = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content);

        return $result;
    }


}