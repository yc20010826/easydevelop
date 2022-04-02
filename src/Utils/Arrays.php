<?php
namespace YangChengEasyComposer\Utils;

use YangChengEasyComposer\Base;

/**
 * 数组工具类
 * Class Arrays
 * @package YangChengEasy\Utils
 */
class Arrays extends Base
{
    /**
     * 清空数组两端空格
     * @param $array array 需要处理的数组
     * @return array
     * @author 杨成 2022年3月31日15:07:07
     *
     */
    public static function delArrayNull($array):array{
        if(!is_array($array)){
            return [];
        }
        return array_map([self::class,'delArrayNull'],$array);
    }

}