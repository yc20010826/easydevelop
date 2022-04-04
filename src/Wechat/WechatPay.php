<?php

namespace YangChengEasyComposer\Wechat;

use think\facade\Response;
use think\Request;
use think\response\Json;
use YangChengEasyComposer\Base;
use Yansongda\Pay\Pay;

/**
 * 微信支付工具类
 * @author 杨成 1991361342@qq.com
 * 基于yansongdaPay进行改造而来，更贴切新手上手使用
 *
 * 温馨提示：在初始化本类时，需要必传的参数使用todo注释，请注意观察
 *
 * 关于订单参数：
 *          所有订单配置参数和官方无任何差别，兼容所有功能，所有订单配置中，客观参数均不用配置，扩展包已经为大家自动处理了，
 *          比如，trade_type，appid，sign, spbill_create_ip 等参数，大家只需传入订单类主观参数即可。
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

    protected $wechatPayClass = null; // 实例化类存储

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
        $this->wechatPayClass = Pay::wechat($this->config);
    }

    /**
     * 示例异步回调时候
     * @return \Symfony\Component\HttpFoundation\Response|Response 返回告知微信SUCCESS
     */
    public function notify_url(){
        $result = $this->wechatPayClass->verify(); // 返回 Collection 类型，可以通过 $result->xxx 得到服务器返回的数据。
        $out_trade_no = $result->out_trade_no; // 商户订单号
        /**
         * 这里写你的业务逻辑
         */
        // 如果成功则返回给微信官方
        return $this->wechatPayClass->success()->send();
    }

    /**
     * 微信公众号支付
     * @param array $order 订单参数
     * @return \Yansongda\Supports\Collection
     */
    public function pay_mp(array $order=[]){
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
        $result = $this->wechatPayClass->miniapp($order);

        // 返回 Collection 实例。包含了调用 JSAPI 的所有参数，如appId，timeStamp，nonceStr，package，signType，paySign 等；
        // 可直接通过 $result->appId, $result->timeStamp 获取相关值。
        // 后续调用不在本文档讨论范围内，请自行参考官方文档。
        return $result;
    }

    /**
     * 微信小程序支付
     * @param array $order 订单参数
     * @return \Yansongda\Supports\Collection
     */
    public function pay_miniapp(array $order=[]){
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
        $result = $this->wechatPayClass->mp($order);

        // 返回 Collection 实例。包含了调用 JSAPI 的所有参数，如appId，timeStamp，nonceStr，package，signType，paySign 等；
        // 可直接通过 $result->appId, $result->timeStamp 获取相关值。
        // 后续调用不在本文档讨论范围内，请自行参考官方文档。
        return $result;
    }

    /**
     * 微信H5支付
     * @param array $order 订单参数
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response 直接跳转支付
     */
    public function pay_h5(array $order=[]){
        if(empty($order)){
            $order = [
                'out_trade_no' => time(), // todo：支付订单号（由你自己生成）
                'body' => 'subject-测试', // todo：支付内容
                'total_fee' => '1', // todo：支付金额（单位分）
                'notify_url' => '',// todo：本次支付成功后微信通知的地址
            ];
            // todo 其余参数可参照官方V2文档进行入参
        }
        return $this->wechatPayClass->wap($order)->send();
    }

    /**
     * 微信APP支付
     * @param array $order 订单参数
     * @return \Symfony\Component\HttpFoundation\Response 返回数据与官方一致
     */
    public function pay_app(array $order=[]){
        if(empty($order)){
            $order = [
                'out_trade_no' => time(), // todo：支付订单号（由你自己生成）
                'body' => 'subject-测试', // todo：支付内容
                'total_fee' => '1', // todo：支付金额（单位分）
                'notify_url' => '',// todo：本次支付成功后微信通知的地址
            ];
            // todo 其余参数可参照官方V2文档进行入参
        }
        return $this->wechatPayClass->app($order);
    }

    /**
     * 被扫二维码支付
     * @param array $order 订单参数
     * @return string 支付二维码链接
     */
    public function pay_pc(array $order=[]) :string{
        if(empty($order)){
            $order = [
                'out_trade_no' => time(), // todo：支付订单号（由你自己生成）
                'body' => 'subject-测试', // todo：支付内容
                'total_fee' => '1', // todo：支付金额（单位分）
                'notify_url' => '',// todo：本次支付成功后微信通知的地址
            ];
            // todo 其余参数可参照官方V2文档进行入参
        }
        $result = $this->wechatPayClass->scan($order);
        $qr_url = $result->code_url;
        return $qr_url;
    }

    /**
     * 主扫二维码支付
     * @param array $order 订单参数
     * @return string 支付二维码链接
     */
    public function pay_pos(array $order=[]){
        if(empty($order)){
            $order = [
                'out_trade_no' => time(), // todo：支付订单号（由你自己生成）
                'body' => 'subject-测试', // todo：支付内容
                'total_fee' => '1', // todo：支付金额（单位分）
                'auth_code' => '1354804793001231564897', // todo：客户付款码内容（需要您自行在前端使用二维码扫描客户的付款码获得）
                'notify_url' => '',// todo：本次支付成功后微信通知的地址
            ];
            // todo 其余参数可参照官方V2文档进行入参
        }
        $result = $this->wechatPayClass->pos($order);

        // 返回数据与官方及时返回数据一致，使用$result->xxx 获取相关值
        return $result;
    }

    /**
     * 账户转账(企业付款)
     * @param array $order 订单参数
     * @param string $type 可选参数：app='app应用'；miniapp='小程序应用'
     * @return string 支付二维码链接
     */
    public function pay_transfer(array $order=[], string $type=''){
        if(empty($order)){
            // todo 如果您在队列中使用，订单参数内必须存在 spbill_create_ip 字段，也就是说spbill_create_ip的值我们不会自动携带，需要您自行传入
            $order = [
                'partner_trade_no' => '',              //商户订单号
                'openid' => '',                        //收款人的openid
                'check_name' => 'NO_CHECK',            //NO_CHECK：不校验真实姓名\FORCE_CHECK：强校验真实姓名
                // 're_user_name'=>'张三',              //check_name为 FORCE_CHECK 校验实名的时候必须提交
                'amount' => '1',                       //企业付款金额，单位为分
                'desc' => '帐户提现',                  //付款说明
            ];
            // todo 其余参数可参照官方V2文档进行入参
        }
        if(!empty($type)){
            if(!in_array($type,['app','miniapp'])){
                throw new \think\Exception('type参数仅支持app/miniapp');
            }
            $order['type'] = $type;
        }
        $result = $this->wechatPayClass->transfer($order);
        // 返回数据与官方及时返回数据一致，使用$result->xxx 获取相关值
        return $result;
    }

    /**
     * 取消|关闭订单
     * @param string $out_trade_no 商户订单号
     * @param string $type 可选参数：app='app订单'；miniapp='小程序订单'
     * @return bool 关闭成功返回true，否则返回false
     * @throws \Yansongda\Pay\Exceptions\GatewayException
     * @throws \Yansongda\Pay\Exceptions\InvalidArgumentException
     * @throws \Yansongda\Pay\Exceptions\InvalidSignException
     * @throws \think\Exception
     */
    public function cancel(string $out_trade_no='', string $type=''){
        if(empty($out_trade_no)){
            throw new \think\Exception('请传入商户订单号');
        }
        $order = [
            'out_trade_no' => $out_trade_no,
        ];
        if(!empty($type)){
            if(!in_array($type,['app','miniapp'])){
                throw new \think\Exception('type参数仅支持app/miniapp');
            }
            $order['type'] = $type;
        }
        $result = $this->wechatPayClass->close($order);

        // 以下代码为直接返回是否关闭订单成功，不需要则删除后返回直接 return $result;
        if($result->result_code != 'SUCCESS'){
            return false;
        }
        return true;
    }

    /**
     * 订单退款
     * @param array $order 订单参数
     * @param string $type 可选参数：app='app订单'；miniapp='小程序订单'
     * @return string 支付二维码链接
     */
    public function refund(array $order=[], string $type=''){
        if(empty($order)){
            $order = [
                'out_trade_no' => '', // todo：支付订单号（由你自己生成）,
                'out_refund_no' => time(), // todo：退款单号（由你自己生成）
                'total_fee' => '1', // todo：订单总金额
                'refund_fee' => '1', // todo：本次退款金额
                'refund_desc' => '测试退款haha', // todo：退款订单描述
                'notify_url' => '',// todo：本次成功后微信通知的地址
            ];
            // todo 其余参数可参照官方V2文档进行入参
        }
        if(!empty($type)){
            if(!in_array($type,['app','miniapp'])){
                throw new \think\Exception('type参数仅支持app/miniapp');
            }
            $order['type'] = $type;
        }
        $result = $this->wechatPayClass->refund($order);
        // 返回数据与官方及时返回数据一致，使用$result->xxx 获取相关值
        return $result;
    }

    /**
     * 查询普通订单（查询退款订单）
     * @param string $out_trade_no 商户订单号
     * @param string $type 可选参数：app='app订单'；miniapp='小程序订单'
     * @param false $is_refund 是否查询退款订单（传入true则视为本次需要查询的是退款订单）
     * @return \Yansongda\Supports\Collection
     * @throws \Yansongda\Pay\Exceptions\GatewayException
     * @throws \Yansongda\Pay\Exceptions\InvalidArgumentException
     * @throws \Yansongda\Pay\Exceptions\InvalidSignException
     */
    public function query_order(string $out_trade_no='', string $type='',$is_refund=false){
        $order = [
            'out_trade_no' => $out_trade_no,
        ];
        if(!empty($type)){
            if(!in_array($type,['app','miniapp'])){
                throw new \think\Exception('type参数仅支持app/miniapp');
            }
            $order['type'] = $type;
        }
        if($is_refund){
            $result = $this->wechatPayClass->find($order, 'refund');
        }else{
            $result = $this->wechatPayClass->find($order);
        }
        // 返回数据与官方及时返回数据一致，使用$result->xxx 获取相关值
        return $result;
    }

    /**
     * 查询转账（企业付款）订单
     * @param string $partner_trade_no 商户订单号
     * @param string $type 可选参数：app='app订单'；miniapp='小程序订单'
     * @return \Yansongda\Supports\Collection
     * @throws \Yansongda\Pay\Exceptions\GatewayException
     * @throws \Yansongda\Pay\Exceptions\InvalidArgumentException
     * @throws \Yansongda\Pay\Exceptions\InvalidSignException
     */
    public function query_transfer_order(string $partner_trade_no='', string $type=''){
        $order = [
            'partner_trade_no' => $partner_trade_no,
        ];
        if(!empty($type)){
            if(!in_array($type,['app','miniapp'])){
                throw new \think\Exception('type参数仅支持app/miniapp');
            }
            $order['type'] = $type;
        }
        $result = $this->wechatPayClass->find($order, 'transfer');
        // 返回数据与官方及时返回数据一致，使用$result->xxx 获取相关值
        return $result;
    }



}