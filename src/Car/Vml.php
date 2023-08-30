<?php

namespace YangChengEasyComposer\Car;

use YangChengEasyComposer\Base;
use YangChengEasyComposer\Utils\Request;

/**
 * 车辆品牌及型号库
 * 完全文档：https://www.kancloud.cn/xiaoggvip/carapi/1671204
 * 本库采用第三方接口，如失效，请自行更换接口
 */

class Vml extends Base
{

    /**
     * API接口地址
     * @var string API地址
     */
    private static $apiBaseUrl = "https://tool.bitefu.net/car/";

    /**
     * 检索车辆品牌
     * @param array $from 数据源：默认0 可选(0:汽车之家,1:易车网,2:瓜子二手车,3:58二手车,4:淘车网,5:第一车网)
     * @param string $keyword 搜索关键字
     * @param string $id 品牌ID
     * @param int $pagesize 数据量（300为最大）
     * @return array
     */
    public static function getBrand(array $from=[0],string $keyword='',string $id='',int $pagesize=300){
        $param['type'] = "brand";
        // 格式化数据源
        if($from == []){
            $from = [0];
        }
        $from = implode('|',$from);
        $from = "[{$from}]";
        $param['from'] = $from;
        // 品牌关键字
        if(!empty($keyword)){
            $param['keyword'] = $keyword;
        }
        // 品牌ID
        if(!empty($id)){
            $param['id'] = $id;
        }
        // 调取数据量
        if(!empty($pagesize)){
            $param['pagesize'] = $pagesize;
        }
        $result = Request::send_request(self::$apiBaseUrl, $param, 'GET');
        return $result['info'];
    }

    /**
     * 检索车辆车系
     * @param array $from 数据源：默认0 可选(0:汽车之家,1:易车网,2:瓜子二手车,3:58二手车,4:淘车网,5:第一车网)
     * @param string $keyword 搜索关键字
     * @param int $id 品牌ID
     * @param int $brand_id 车系ID
     * @param int $pagesize 数据量，默认100，自由调整
     * @return array
     * @author YangCheng 2023/8/30 14:48
     */
    public static function getSeries(array $from=[0],string $keyword='',int $id=null,int $brand_id=null,int $pagesize=100){
        $param['type'] = "series";
        if($from == []){
            $from = [0];
        }
        // 格式化数据源
        $from = implode('|',$from);
        $from = "[{$from}]";
        $param['from'] = $from;
        // 品牌关键字
        if(!empty($keyword)){
            $param['keyword'] = $keyword;
        }
        // 品牌ID
        if(!empty($id)){
            $param['id'] = $id;
        }
        // 车系ID
        if(!empty($brand_id)){
            $param['brand_id'] = $brand_id;
        }
        // 调取数据量
        if(!empty($pagesize)){
            $param['pagesize'] = $pagesize;
        }
        $result = Request::send_request(self::$apiBaseUrl, $param, 'GET');
        return $result['info'];
    }

    /**
     * 检索车辆厂家（条件筛选不生效）
     * @param array $from 数据源：默认0 可选(0:汽车之家,1:易车网,2:瓜子二手车,3:58二手车,4:淘车网,5:第一车网)
     * @param string $keyword 搜索关键字
     * @param int|null $id 厂家ID
     * @param int|null $brand_id 品牌ID
     * @param int|null $series_id 车系ID
     * @param int $pagesize 数据量，默认100，自由调整
     * @return array
     * @author YangCheng 2023/8/30 14:59
     */
    public static function getSeriesGroup(array $from=[0],string $keyword='',int $id=null,int $brand_id=null,int $series_id=null,int $pagesize=100){
        $param['type'] = "series_group";
        if($from == []){
            $from = [0];
        }
        // 格式化数据源
        $from = implode('|',$from);
        $from = "[{$from}]";
        $param['from'] = $from;
        // 品牌关键字
        if(!empty($keyword)){
            $param['keyword'] = $keyword;
        }
        // 厂家ID
        if(!empty($id)){
            $param['id'] = $id;
        }
        // 品牌ID
        if(!empty($brand_id)){
            $param['brand_id'] = $brand_id;
        }
        // 车系ID
        if(!empty($series_id)){
            $param['series_id'] = $series_id;
        }
        // 调取数据量
        if(!empty($pagesize)){
            $param['pagesize'] = $pagesize;
        }
        $result = Request::send_request(self::$apiBaseUrl, $param, 'GET');
        return $result['info'];
    }

    /**
     * 检索车辆车型
     * @param array $from 数据源：默认0 可选(0:汽车之家,1:易车网,2:瓜子二手车,3:58二手车,4:淘车网,5:第一车网)
     * @param string $keyword 搜索关键字
     * @param int|null $id 车型ID
     * @param int|null $brand_id 品牌ID
     * @param int|null $series_id 车系ID
     * @param int|null $group_id 厂家ID
     * @param string|null $year 年份，例如2010
     * @param int $pagesize 数据量，默认50，自由调整
     * @return array
     * @author YangCheng 2023/8/30 15:14
     */
    public static function getInfo(array $from=[0],string $keyword='',int $id=null,int $brand_id=null,int $series_id=null,int $group_id=null,string $year='',int $pagesize=50)
    {
        $param['type'] = "info";
        if($from == []){
            $from = [0];
        }
        // 格式化数据源
        $from = implode('|',$from);
        $from = "[{$from}]";
        $param['from'] = $from;
        // 品牌关键字
        if(!empty($keyword)){
            $param['keyword'] = $keyword;
        }
        // 车型ID
        if(!empty($id)){
            $param['id'] = $id;
        }
        // 品牌ID
        if(!empty($brand_id)){
            $param['brand_id'] = $brand_id;
        }
        // 车系ID
        if(!empty($series_id)){
            $param['series_id'] = $series_id;
        }
        // 厂家ID
        if(!empty($group_id)){
            $param['group_id'] = $group_id;
        }
        // 调取数据量
        if(!empty($pagesize)){
            $param['pagesize'] = $pagesize;
        }
        $result = Request::send_request(self::$apiBaseUrl, $param, 'GET');
        return $result['info'];
    }

    /**
     * 检索车辆车型详情(仅支持汽车之家,易车网,第一车网源)
     * @param array $from 数据源：默认0 可选(0:汽车之家,1:易车网,5:第一车网)
     * @param string $keyword 搜索关键字
     * @param int|null $id 车型ID
     * @return array
     * @author YangCheng 2023/8/30 15:14
     */
    public static function getInfoDetail(array $from=[0],int $id=null)
    {
        $param['type'] = "detail";
        if($from == []){
            $from = [0];
        }
        // 格式化数据源
        $from = implode('|',$from);
        $from = "[{$from}]";
        $param['from'] = $from;
        // 品牌关键字
        if(!empty($keyword)){
            $param['keyword'] = $keyword;
        }
        // 车型ID
        if(!empty($id)){
            $param['id'] = $id;
        }
        $result = Request::send_request(self::$apiBaseUrl, $param, 'GET');
        return $result['info'];
    }


}