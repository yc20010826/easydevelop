<?php


namespace YangChengEasyComposer\Utils;


use YangChengEasyComposer\Base;

/**
 * 请求封装类
 * Class Request
 * @package YangChengEasy\Utils
 */
class Request extends Base
{
    /**
     * 发起一个http请求（数组返回）
     * @param string $url 请求地址
     * @param array $data 请求参数
     * @param string $method 请求方法：GET | POST | PUT | DEL (需要大写)
     * @param array $header 请求头部，注意是下标数组
     * @return array 返回数据格式必定为数组，如不需要数组返回请使用send_request_all方法
     */
    public static function send_request($url, $data=null, $method='GET', $header=array()) :array{
        $multi = false; // 是否保留原格式请求发出
        // 如果是POST请求，需要判断请求参数是否为数组，如果不是数组则不需要转格式，这种情况存在与post请求，请求内容是json字符串的时候
        if(!in_array($method,['GET']) && !is_array($data)){
            $multi = true;
        }
        $result = (new self())->http($url,$data,$method,$header,$multi);
        if(Other::is_xml($result)){
            $result = Other::xmlToArr($result);
        }else{
            $result = json_decode($result,true);
        }

        return $result;
    }

    /**
     * 发起一个http请求（原样返回）
     * @param string $url 请求地址
     * @param array $data 请求参数
     * @param string $method 请求方法：GET | POST | PUT | DEL (需要大写)
     * @param array $header 请求头部，注意是下标数组
     * @return array|bool|string
     */
    public static function send_request_all($url, $data=null, $method='GET', $header=array()){
        $multi = false; // 是否保留原格式请求发出
        // 如果是POST请求，需要判断请求参数是否为数组，如果不是数组则不需要转格式，这种情况存在与post请求，请求内容是json字符串的时候
        if(!in_array($method,['GET']) && !is_array($data)){
            $multi = true;
        }
        $result = (new self())->http($url,$data,$method,$header,$multi);
        return $result;
    }
}