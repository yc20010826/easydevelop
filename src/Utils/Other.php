<?php


namespace YangChengEasyComposer\Utils;

use YangChengEasyComposer\Base;

/**
 * 其他工具类
 * Class Other
 * @package YangChengEasy\Utils
 */
class Other extends Base
{
    /**
     * XML转数组
     * @param string $xml 需要转换的XML数据
     * @return array 数组抛出
     * @author 杨成 2022年3月31日15:07:07
     **/
    public static function xmlToArr($xml=''):array{
        //转化XML为数组
        if(is_file($xml)){
            $xml_array=simplexml_load_file($xml);
        }else{
            $xml_array=simplexml_load_string($xml);
        }
        $y_data = json_encode($xml_array);
        $data = json_decode($y_data,true);
        return $data;
    }

    /**
     * Unicode转中文UTF-8
     * @param $str String 需要转换的字符
     * @return string|string[]
     * @author 杨成 2022年3月31日15:09:23
     *
     */
    public static function de_Unicode($str){
        return preg_replace_callback('/\\\\u([0-9a-f]{4})/i', create_function('$matches', 'return iconv("UCS-2BE","UTF-8",pack("H*", $matches[1]));'), $str);
    }

    /**
     * 判断是否SSL协议
     * @return boolean
     * @author 杨成 2022年3月31日15:09:23
     */
    public static function is_ssl() {
        return $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
    }

    /**
     * 判断数据是否为合法的XML格式
     * @param string $str XML数据
     * @return false|mixed
     */
    public static function is_xml($str): bool
    {
        $xml_parser = xml_parser_create();
        if(!xml_parse($xml_parser,$str,true)){
            xml_parser_free($xml_parser);
            return false;
        }else {
            return (json_decode(json_encode(simplexml_load_string($str)),true));
        }
    }
}