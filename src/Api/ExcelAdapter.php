<?php
/**
 * excel操作类
 */

namespace jwj_tools\tools\Api;



use \Exception;

class ExcelAdapter
{

    public function write($fileName,$expCellName,$expTableData){
        //此处是设置缓存方式
        $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
        $cacheSettings = array();
        \PHPExcel_Settings::setCacheStorageMethod($cacheMethod,$cacheSettings);
        $cellNum = count($expCellName);
        $dataNum = count($expTableData);
        $PHPExcel =  new \PHPExcel();
        $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');

        for($i=0;$i<$cellNum;$i++){
            $PHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'1', $expCellName[$i][1]);
        }
        for($i=0;$i<$dataNum;$i++){
            for($j=0;$j<$cellNum;$j++){
                $PHPExcel->getActiveSheet()->setCellValueExplicit($cellName[$j].($i+2), $expTableData[$i][$expCellName[$j][0]]);
            }
        }
        ob_end_clean();
        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$fileName.'.xls"');
        header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
        $objWriter = \PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }


    /**
     * excel导入
     * $xlsCell：必须导入的表字段
     */
    public function read($xlsCell,$file,$moreSheet=false){
        if(!file_exists($file)){
            return false;
        }
        $inputFileType = \PHPExcel_IOFactory::identify($file);
        $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
        try{
            $PHPReader = $objReader->load($file);
        }catch(Exception $e){}
        if(!isset($PHPReader)) return false;
        /* @var $objWorksheet PHPExcel_Worksheet */
        if($moreSheet){
            $arr = array();
            //获取工作表的数目
            $sheetCount = $PHPReader->getSheetCount();
            for($sheetNum = 0; $sheetNum < $sheetCount; $sheetNum++ ) {
                $_currentSheet = $PHPReader->getSheet($sheetNum);
                $allRow = $_currentSheet->getHighestRow();//how many rows
                $highestColumn = $_currentSheet->getHighestColumn();//how many columns
                $allColumn = \PHPExcel_Cell::columnIndexFromString($highestColumn);
                $fieldNum = count($xlsCell);
                $fieldNameList = array();
                $checkFieldNameList = array();//用于判断必填的字段在用户导入的excel的字段是否都包含
                for($i = 0;$i<$fieldNum;$i++){
                    $xlsCellName = trim($xlsCell[$i][1]);
                    $fieldNameList[$xlsCellName] = $xlsCell[$i][0];
                    $checkFieldNameList[] = $xlsCellName;
                }
                //获取用户导入的excel的列的字段
                for($currentRow = 1 ;$currentRow<=1;$currentRow++){
                    $fieldName = array();
                    for($currentColumn=0;$currentColumn<$allColumn;$currentColumn++){;
                        $col = \PHPExcel_Cell::stringFromColumnIndex($currentColumn);
                        $address = $col.$currentRow;
                        $cell = $_currentSheet->getCell($address);
                        $value = $cell->getFormattedValue();
                        $fieldName[$currentColumn+1] = $value;
                    }
                }
                //判断用户导入的表字段是否在自定义的字段里
                //数据从第二行开始
                for($currentRow = 2 ;$currentRow<=$allRow;$currentRow++){
                    $row = array();
                    for($currentColumn=0;$currentColumn<$allColumn;$currentColumn++){;
                        $col = \PHPExcel_Cell::stringFromColumnIndex($currentColumn);
                        $address = $col.$currentRow;
                        $cell = $_currentSheet->getCell($address);
                        $value = $cell->getFormattedValue();
                        $fieldNameKey = $fieldName[$currentColumn+1];
                        if(isset($fieldNameList[$fieldNameKey])){
                            $row[$fieldNameList[$fieldNameKey]] = trim($value);
                        }
                    }
                    $arr[] = $row;
                }
            }
            return $arr;
        }
        $objWorksheet = $PHPReader->getActiveSheet();
        $allRow = $objWorksheet->getHighestRow();//how many rows
        $highestColumn = $objWorksheet->getHighestColumn();//how many columns
        $allColumn = \PHPExcel_Cell::columnIndexFromString($highestColumn);
        $fieldNum = count($xlsCell);
        $fieldNameList = array();
        $checkFieldNameList = array();//用于判断必填的字段在用户导入的excel的字段是否都包含
        for($i = 0;$i<$fieldNum;$i++){
            $xlsCellName = trim($xlsCell[$i][1]);
            $fieldNameList[$xlsCellName] = $xlsCell[$i][0];
            $checkFieldNameList[] = $xlsCellName;
        }
        //获取用户导入的excel的列的字段
        for($currentRow = 1 ;$currentRow<=1;$currentRow++){
            $fieldName = array();
            for($currentColumn=0;$currentColumn<$allColumn;$currentColumn++){;
                $col = \PHPExcel_Cell::stringFromColumnIndex($currentColumn);
                $address = $col.$currentRow;
                $cell = $objWorksheet->getCell($address);
                $value = $cell->getFormattedValue();
                $fieldName[$currentColumn+1] = $value;
            }
        }
        //判断用户导入的表字段是否在自定义的字段里
        $arr = array();
        //数据从第二行开始
        for($currentRow = 2 ;$currentRow<=$allRow;$currentRow++){
            $row = array();
            for($currentColumn=0;$currentColumn<$allColumn;$currentColumn++){;
                $col = \PHPExcel_Cell::stringFromColumnIndex($currentColumn);
                $address = $col.$currentRow;
                $cell = $objWorksheet->getCell($address);
                $value = $cell->getFormattedValue();
                $fieldNameKey = $fieldName[$currentColumn+1];
                if(isset($fieldNameList[$fieldNameKey])){
                    $row[$fieldNameList[$fieldNameKey]] = trim($value);
                }
            }
            $arr[] = $row;
        }
        return $arr;
    }



}