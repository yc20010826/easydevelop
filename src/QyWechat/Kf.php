<?php
namespace YangChengEasyComposer\QyWechat;

use think\Cache;
use think\Log;
use think\Request;
use YangChengEasyComposer\Base;

//require_once (dirname(__FILE__) . '/library/Department.php');
require_once (dirname(__FILE__) . '/library/CreateConnection.php');
//require_once (dirname(__FILE__) . '/library/User.php');
//require_once (dirname(__FILE__) . '/library/Tag.php');
//require_once (dirname(__FILE__) . '/library/Material.php');
//require_once (dirname(__FILE__) . '/library/SendMessage.php');
//require_once (dirname(__FILE__) . '/library/Agent.php');
//require_once (dirname(__FILE__) . '/library/Menu.php');
//require_once (dirname(__FILE__) . '/library/Chat.php');


class Kf extends Base
{
    protected $apiBaseUrl = 'https://qyapi.weixin.qq.com';
    protected $corpid = ''; // 企业ID
    protected $corpsecret = ''; //企业微信应用的凭证密钥

    /**
     * Kf constructor.
     * @param Request|null $request
     */
    public function __construct($config, Request $request = null)
    {
        parent::__construct($request);

        $this->corpid = $config['corpid'];
        $this->corpsecret = $config['corpsecret'];
    }

    /**
     * 获取企业微信客服链接
     * @param string $open_kfid 企业微信客服ID
     * @param string $scene 场景值
     * @return mixed
     * @throws \think\Exception
     */
    public function add_contact_way($open_kfid,$scene=''){
        $createConnection = new CreateConnection();
        $access_token = $createConnection -> getAccessToken($corpid, $corpsecret);
        $url = '/cgi-bin/kf/add_contact_way?access_token='.$access_token;
        // 参数
        $pram = [
            'open_kfid' => $open_kfid,
            'scene' => $scene,
        ];
        // 发起请求
        try{
            $result = $this->http($this->apiBaseUrl.$url, json_encode($pram));
            $result = json_decode($result,true);
            if (isset($result['errcode']) && $result['errcode'] != 0 || !$result){
                Log::error('yangchengEasy中方法出现错误：'.$result['errmsg']);
                throw new \think\Exception($result['errmsg']);
            }
            return $result['url'];
        }catch (\Exception $e){
            Log::error('yangchengEasy中方法出现错误：'.$e);
            throw new \think\Exception($e->getMessage());
        }
    }
}