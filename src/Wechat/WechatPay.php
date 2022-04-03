<?php

namespace YangChengEasyComposer\Wechat;

use think\Request;
use YangChengEasyComposer\Base;
use Yansongda\Pay\Pay;

/**
 * 微信支付工具类
 * @author 杨成 1991361342@qq.com
 * 基于yansongdaPay进行改造而来，更贴切新手上手使用
 *
 * 温馨提示：在初始化本类时，需要必传的参数使用todo注释，请注意观察
 */
class WechatPay extends Base
{
    /**
     * 微信支付类配置
     * @var array
     */
    protected $config = [
        'appid' => null, // todo：APP APPID（APP支付时必传）
        'app_id' => null, // todo：公众号 APPID（公众号支付时必传）
        'miniapp_id' => null, // todo：小程序 APPID （小程序支付时必传）
        'mch_id' => null, // todo：微信支付商户号
        'key' => null, // todo：微信支付商户密钥V2
        'notify_url' => 'http://yanda.net.cn/notify.php', // todo：异步回调地址，当支付成功后微信官方向这个地址发起请求通知（不设置将视为您在每个订单参数里面设置了）
        'cert_client' => null, // apiclient_cert.pem 证书退款等情况时用到（可选参数）！！！需要转入绝对路径
        'cert_key' => null,// apiclient_key.pem 证书退款等情况时用到（可选参数）！！！需要转入绝对路径
    ];

    /**
     * 类初始化
     * @param array $config 类配置数组，格式需要与上方$config内一致
     * @param Request|null $request
     * @throws \think\Exception
     */
    public function __construct($config, Request $request = null)
    {
        parent::__construct($request);

        if(empty($config)){
            throw new \think\Exception('请先进行配置实例化');
        }
        $this->config = $config;
    }

    /**
     * 微信公众号支付
     * @param array $order 订单参数
     * @return \Yansongda\Supports\Collection
     */
    public function pay_mp($order=[]){
        if(empty($order)){
            $order = [
                'out_trade_no' => time(), // todo：支付订单号（由你自己生成）
                'body' => 'subject-测试', // todo：支付内容
                'total_fee' => '1', // todo：支付金额（单位分）
                'openid' => 'onkVf1FjWS5SBxxxxxxxx',// todo：支付人openid
                'notify_url' => '',// todo：本次支付成功后微信通知的地址
            ];
            // todo 其余参数可参照官方V2文档进行入参
        }
        $result = Pay::wechat($this->config)->mp($order);

        // 返回 Collection 实例。包含了调用 JSAPI 的所有参数，如appId，timeStamp，nonceStr，package，signType，paySign 等；
        // 可直接通过 $result->appId, $result->timeStamp 获取相关值。
        // 后续调用不在本文档讨论范围内，请自行参考官方文档。
        return $result;
    }

}