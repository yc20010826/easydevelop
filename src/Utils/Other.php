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

    /**
     * 将时间戳转换为日期时间
     * @param int    $time   时间戳
     * @param string $format 日期时间格式
     * @return string
     */
    public static function datetime($time, $format = 'Y-m-d H:i:s'): string
    {
        $time = is_numeric($time) ? $time : strtotime($time);
        return date($format, $time);
    }

    /**
     * 将字节转换为可读文本
     * @param int    $size      大小
     * @param string $delimiter 分隔符
     * @param int    $precision 小数位数
     * @return string
     */
    public static function format_bytes($size, $delimiter = '', $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
        for ($i = 0; $size >= 1024 && $i < 6; $i++) {
            $size /= 1024;
        }
        return round($size, $precision) . $delimiter . $units[$i];
    }

    /**
     * 首字母头像
     * @param string $text 文本
     * @return string
     */
    public static function letter_avatar($text)
    {
        $total = unpack('L', hash('adler32', $text, true))[1];
        $hue = $total % 360;
        list($r, $g, $b) = (new self())->hsv2rgb($hue / 360, 0.3, 0.9);

        $bg = "rgb({$r},{$g},{$b})";
        $color = "#ffffff";
        $first = mb_strtoupper(mb_substr($text, 0, 1));
        $src = base64_encode('<svg xmlns="http://www.w3.org/2000/svg" version="1.1" height="100" width="100"><rect fill="' . $bg . '" x="0" y="0" width="100" height="100"></rect><text x="50" y="50" font-size="50" text-copy="fast" fill="' . $color . '" text-anchor="middle" text-rights="admin" dominant-baseline="central">' . $first . '</text></svg>');
        $value = 'data:image/svg+xml;base64,' . $src;
        return $value;
    }
}