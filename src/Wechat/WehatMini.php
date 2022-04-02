<?php

namespace YangChengEasyComposer\Wechat;

use think\Cache;
use think\Config;
use think\Log;
use think\Request;
use YangChengEasyComposer\Base;
use YangChengEasyComposer\Wechat\library\WXBizDataCrypt;

/**
 * 小程序封装类
 * Class WehatMini
 * @package YangChengEasy\Wechat
 */
class WehatMini extends Base
{
    protected $apiBaseUrl = 'https://api.weixin.qq.com';
    protected $appid = null; // APPID
    protected $secret = null; // APPSecret
    public $access_token = ''; //  assessToken，两小时更新一次

    /**
     * WehatMini constructor.
     * @param Request|null $request
     * @param array $config
     * @throws \think\Exception
     */
    public function __construct($config=[], Request $request = null)
    {
        parent::__construct($request);
        if(empty($config)){
            throw new \think\Exception('请先进行配置实例化');
        }
        if(empty($config['appid'])){
            throw new \think\Exception('小程序APPID不能为空');
        }
        if(empty($config['secret'])){
            throw new \think\Exception('小程序APPsecret不能为空');
        }
        $this->appid = $config['appid'];
        $this->secret = $config['secret'];
        $this->getAccessToken();
    }

    /**
     * 获取token
     * @return mixed|null
     * @throws \think\Exception
     * @author YangCheng 2022年3月31日14:59:19
     */
    public function getAccessToken()
    {
        if(Cache::get('wechatMiniAccessToken')){
            $this->access_token = Cache::get('wechatMiniAccessToken');
            return Cache::get('wechatMiniAccessToken');
        }
        $url = '/cgi-bin/token';
        // 参数
        $pram = [
            'grant_type' => 'client_credential',
            'appid' => $this->appid,
            'secret' => $this->secret,
        ];
        // 发起请求
        try{
            $result = $this->http($this->apiBaseUrl.$url, $pram);
            $result = json_decode($result,true);
            if (isset($result['errcode']) && $result['errcode'] != 0 || !$result){
                Log::error('yangchengEasy中WechatMini类getAccessToken方法出现错误：'.$result['errmsg']);
                throw new \think\Exception($result['errmsg']);
            }
            $access_token = $result['access_token'];
            // 载入配置到信息
            $this->access_token = $access_token;
            Cache::set('wechatMiniAccessToken',$access_token,$result['expires_in']);
            return $access_token;
        }catch (\Exception $e){
            Log::error('yangchengEasy中WechatMini类getAccessToken方法出现错误：'.$e);
            throw new \think\Exception($e->getMessage());
        }
    }

    /**
     * code换取用户信息（openid|session_key|unionid）
     * @param string $code 用户前端获得的CODE
     * @return array
     * @throws \think\Exception
     * @author YangCheng 2022年3月31日14:59:19
     */
    public function getUserOpenId($code) :array{
        $url = '/sns/jscode2session';
        // 参数
        $pram = [
            'appid' => $this->appid,
            'secret' => $this->secret,
            'js_code' => $code,
            'grant_type' => 'authorization_code',
        ];
        // 发起请求
        try{
            $result = $this->http($this->apiBaseUrl.$url, $pram,'GET');
            $result = json_decode($result,true);
            if (isset($result['errcode']) && $result['errcode'] != 0 || !$result){
                Log::error('yangchengEasy中WechatMini类getUserOpenId方法出现错误：'.$result['errmsg']);
                throw new \think\Exception($result['errmsg']);
            }
            return $result;
        }catch (\Exception $e){
            Log::error('yangchengEasy中WechatMini类getUserOpenId方法出现错误：'.$e);
            throw new \think\Exception($e->getMessage());
        }
    }

    /**
     * 对微信加密数据进行解密
     * @param $sessionKey
     * @param $encryptedData
     * @param $iv
     * @return array
     * @throws \think\Exception
     * @author YangCheng 2022年3月31日14:59:19
     */
    public function dataDecryption($sessionKey,$encryptedData,$iv):array{
        $result = null; // 结果存储桶
        $WXBizDataCryptClass = new WXBizDataCrypt($this->appid, $sessionKey);
        $errCode = $WXBizDataCryptClass->decryptData($encryptedData, $iv, $result);
        if($errCode != 0){
            throw new \think\Exception('微信加解密失败：'.$errCode,$errCode);
        }
        $result = json_decode($result,true);
        return $result;
    }

}