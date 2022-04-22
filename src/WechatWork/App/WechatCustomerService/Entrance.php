<?php
namespace YangChengEasyComposer\WechatWork\App\WechatCustomerService;

use think\Log;
use think\Request;
use YangChengEasyComposer\WechatWork\CreateConnection;
use YangChengEasyComposer\WechatWork\library\WXBizMsgCrypt;
use YangChengEasyComposer\Utils\Request as YangRequest;

/**
 * 微信客服应用
 * Class Entrance
 * @package YangChengEasyComposer\WechatWork\App\WechatCustomerService
 */
class Entrance extends \YangChengEasyComposer\Base
{
    protected $apiUrl = 'https://qyapi.weixin.qq.com'; // 接口域名
    protected $token = ''; // 开发者设置的token
    protected $encodingAesKey = ''; // 开发者设置的EncodingAESKey
    protected $sReceiveId = ''; // 企业ID
    protected $corpSecret = ''; // 企业Secret
    protected $accessToken = ''; // 凭证

    /**
     * 初始化应用
     * Entrance constructor.
     * @param array $config
     *      token:开发者设置的应用token
     *      encodingAesKey:开发者设置的应用EncodingAESKey
     *      corpid:企业ID
     *      corpSecret:应用Secret
     * @param Request|null $request
     */
    public function __construct($config, Request $request = null)
    {
        $this->token = $config['token'];
        $this->encodingAesKey = $config['encodingAesKey'];
        $this->sReceiveId = $config['corpid'];
        $this->corpSecret = empty($config['corpSecret'])?null:$config['corpSecret'];

        $conn = new CreateConnection();
        $this->accessToken = $conn->getAccessToken($this->sReceiveId, $this->corpSecret);
        parent::__construct($request);
    }

    /**
     * 回调地址验证
     * @param $msg_signature
     * @param $timestamp
     * @param $nonce
     * @param $echostr
     * @return string
     * @throws \think\Exception
     */
    public function decryptUrl($msg_signature, $timestamp, $nonce, $echostr){
        $wxcpt = new WXBizMsgCrypt($this->token, $this->encodingAesKey, $this->sReceiveId);
        $returnData = '';
        $error = $wxcpt->VerifyURL($msg_signature, $timestamp, $nonce, $echostr,$returnData);
        if($error != 0){
            throw new \think\Exception('消息解密故障:'.$error);
        }
        return $returnData;
    }

    /**
     * 获取客服列表
     * @return mixed
     * @throws \think\Exception
     */
    public function list_contact_way(){
        $url = '/cgi-bin/kf/account/list';
        // 参数
        $pram = [
            'access_token' => $this->accessToken
        ];
        if(!empty($scene)){
            $pram['scene'] = $scene;
        }
        // 发起请求
        try{
            $result = YangRequest::send_request($this->apiUrl.$url, $pram,'GET');
            if (isset($result['errcode']) && $result['errcode'] != 0 || !$result){
                Log::error('yangchengEasy方法出现错误：'.$result['errmsg']);
                throw new \think\Exception($result['errmsg']);
            }
            return $result['account_list'];
        }catch (\Exception $e){
            Log::error('yangchengEasy方法出现错误：'.$e);
            throw new \think\Exception($e->getMessage());
        }
    }

    /**
     * 获取客服链接
     * @param string $open_kfid 客服ID
     * @param string $scene 场景值
     * @return mixed
     * @throws \think\Exception
     */
    public function add_contact_way($open_kfid, $scene=''){
        $url = '/cgi-bin/kf/add_contact_way?access_token='.$this->accessToken;
        // 参数
        $pram = [
            'access_token' => $this->accessToken,
            'open_kfid' => $open_kfid
        ];
        if(!empty($scene)){
            $pram['scene'] = $scene;
        }
        // 发起请求
        try{
            $result = YangRequest::send_request($this->apiUrl.$url, json_encode($pram),'POST');
            if (isset($result['errcode']) && $result['errcode'] != 0 || !$result){
                Log::error('yangchengEasy方法出现错误：'.$result['errmsg']);
                throw new \think\Exception($result['errmsg']);
            }
            return $result['url'];
        }catch (\Exception $e){
            Log::error('yangchengEasy方法出现错误：'.$e);
            throw new \think\Exception($e->getMessage());
        }
    }

}