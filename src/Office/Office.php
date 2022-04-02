<?php


namespace YangChengEasyComposer\Office;

use think\Request;

//引入OFFICE类
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use YangChengEasyComposer\Base;

/**
 * Class Office
 * @package YangCheng
 * Office操作
 */
class Office extends Base
{
    /**
     * 使用PHPEXECL导入
     * 特别注意：返回数组下标以1开始
     *
     * @param string $file      文件地址（绝对路径）
     * @param int    $sheet     工作表sheet(传0则获取第一个sheet)
     * @param int    $columnCnt 列数(传0则自动获取最大列)
     * @param array  $options   操作选项
     *                          array mergeCells 合并单元格数组
     *                          array formula    公式数组
     *                          array format     单元格格式数组
     *
     * @return array
     *
     * @author 杨成 2020年3月12日
     */
    public static function importExecl($file = '',$sheet = 0,$columnCnt = 0, &$options = [])
    {
        try {
            /* 转码 */
            $file = iconv("utf-8", "gb2312", $file);

            if (empty($file) OR !file_exists($file)) {
                throw new \Exception('文件不存在!'.$file);
            }

            $objRead = IOFactory::createReader('Xlsx');

            if (!$objRead->canRead($file)) {
                $objRead = IOFactory::createReader('Xls');

                if (!$objRead->canRead($file)) {
                    throw new \Exception('只支持导入Excel文件！');
                }
            }

            /* 如果不需要获取特殊操作，则只读内容，可以大幅度提升读取Excel效率 */
            empty($options) && $objRead->setReadDataOnly(true);
            /* 建立excel对象 */
            $obj = $objRead->load($file);
            /* 获取指定的sheet表 */
            $currSheet = $obj->getSheet($sheet);

            if (isset($options['mergeCells'])) {
                /* 读取合并行列 */
                $options['mergeCells'] = $currSheet->getMergeCells();
            }

            if (0 == $columnCnt) {
                /* 取得最大的列号 */
                $columnH = $currSheet->getHighestColumn();
                /* 兼容原逻辑，循环时使用的是小于等于 */
                $columnCnt = Coordinate::columnIndexFromString($columnH);
            }

            /* 获取总行数 */
            $rowCnt = $currSheet->getHighestRow();
            $data   = [];

            /* 读取内容 */
            for ($_row = 1; $_row <= $rowCnt; $_row++) {
                $isNull = true;

                for ($_column = 1; $_column <= $columnCnt; $_column++) {
                    $cellName = Coordinate::stringFromColumnIndex($_column);
                    $cellId   = $cellName . $_row;
                    $cell     = $currSheet->getCell($cellId);

                    if (isset($options['format'])) {
                        /* 获取格式 */
                        $format = $cell->getStyle()->getNumberFormat()->getFormatCode();
                        /* 记录格式 */
                        $options['format'][$_row][$cellName] = $format;
                    }

                    if (isset($options['formula'])) {
                        /* 获取公式，公式均为=号开头数据 */
                        $formula = $currSheet->getCell($cellId)->getValue();

                        if (0 === strpos($formula, '=')) {
                            $options['formula'][$cellName . $_row] = $formula;
                        }
                    }

                    if (isset($format) && 'm/d/yyyy' == $format) {
                        /* 日期格式翻转处理 */
                        $cell->getStyle()->getNumberFormat()->setFormatCode('yyyy/mm/dd');
                    }

                    $data[$_row][$cellName] = trim($currSheet->getCell($cellId)->getFormattedValue());

                    if (!empty($data[$_row][$cellName])) {
                        $isNull = false;
                    }
                }

                /* 判断是否整行数据为空，是的话删除该行数据 */
                if ($isNull) {
                    unset($data[$_row]);
                }
            }
            if(file_exists($file)){
                unset($file);  //释放文件
            }
            return $data;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * 转关联数组
     * 特别注意：返回数组下标以0开始
     * 特别注意：本方法只适用于importExecl方法处理出来的数据，且要求数据表头必须为横向
     *
     * @param array  $arr      原始数组
     * @param int    $rowTitleCnt     表头行号(传0则获取第一行)
     * @param int    $rowCnt     数据起始行号(传0则获取第二行)
     *
     * @return array 返回表头关联数组
     *
     * @author 杨成 2020年3月12日
     */
    public static function tranRelArr($arr,$rowTitleCnt=0,$rowCnt=0)
    {
        if (empty($arr)) {
            throw new \Exception('原始数组不存在'.$arr);
        }
        if($rowTitleCnt==0){
            $rowTitleCnt=1;
        }
        if($rowCnt==0){
            $rowCnt=2;
        }
        /*创建存储桶*/
        $data = [];
        $kuaiArr = [];
        /*开始循环入栈*/
        for ($a_rowCnt=($rowCnt);$a_rowCnt<=count($arr);$a_rowCnt++){
            /*开始循环键名*/
            foreach ($arr[$rowTitleCnt] as $titleK=>$titleV){
                /*组成新数组*/
                $kuaiArr[$titleV] = $arr[$a_rowCnt][$titleK];
            }
            /*入栈总数据*/
            array_push($data,$kuaiArr);
        }
        return $data;
    }
}