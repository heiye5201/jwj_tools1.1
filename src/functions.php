<?php

namespace Tools;


/**
 * 栏目
 * @param $items
 * @param string $id
 * @param string $pid
 * @param string $son
 * @return array
 */
function get_menu($items, $id='id', $pid='pid', $son = 'children')
{
    $tree = [] ;
    $tmpMap = [] ;
    foreach ($items as $item) {
        $tmpMap[$item[$id]] = $item;
    }
    foreach ($items as $item) {
        if (isset($tmpMap[$item[$pid]])) {
            $tmpMap[$item[$pid]][$son][] = &$tmpMap[$item[$id]];
        } else {
            $tree[] = &$tmpMap[$item[$id]];
        }
    }
    unset($tmpMap) ;
    return $tree;
}



/**
 * tree封装
 * @param $tree
 * @param int $rootId
 * @return array
 */
function arr2tree($tree, $rootId = 0) {
    $return = array();
    foreach ($tree as &$item){
        $item['pid'] = $item['parent_id'] ;
    }
    foreach($tree as $leaf) {
        if($leaf['parent_id'] == $rootId) {
            foreach($tree as $subleaf) {
                if($subleaf['parent_id'] == $leaf['id']) {
                    $leaf['children'] = arr2tree($tree, $leaf['id']);
                    break;
                }
            }
            $return[] = $leaf;
        }
    }
    return $return;
}



/**
 * 数字转换为中文
 * @param  integer  $num  目标数字
 */

function number2chinese($num)
{
    if (is_int($num) && $num < 100) {
        $char = array('零', '一', '二', '三', '四', '五', '六', '七', '八', '九');
        $unit = ['', '十', '百', '千', '万'];
        $return = '';
        if ($num < 10) {
            $return = $char[$num];
        } elseif ($num%10 == 0) {
            $firstNum = substr($num, 0, 1);
            if ($num != 10) $return .= $char[$firstNum];
            $return .= $unit[strlen($num) - 1];
        } elseif ($num < 20) {
            $return = $unit[substr($num, 0, -1)]. $char[substr($num, -1)];
        } else {
            $numData = str_split($num);
            $numLength = count($numData) - 1;
            foreach ($numData as $k => $v) {
                if ($k == $numLength) continue;
                $return .= $char[$v];
                if ($v != 0) $return .= $unit[$numLength - $k];
            }
            $return .= $char[substr($num, -1)];
        }
        return $return;
    }
    return '' ;
}


function sec2time($sec){
    $sec = round($sec/60);
    if ($sec >= 60){
        $hour = floor($sec/60);
        $min = $sec%60;
        $res = $hour.'h';
        $min != 0&&$res .= $min.'m';
    }else{
        $res = $sec.'m';
    }
    return $res;
}






function getChar(){
    return [
        "A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","X","Y","Z"
    ] ;
}



function getDirFileName($dir) {
    $array = array();
    //1、先打开要操作的目录，并用一个变量指向它
    //打开当前目录下的目录pic下的子目录common。
    $handler = opendir($dir);
    //2、循环的读取目录下的所有文件
    /* 其中$filename = readdir($handler)是每次循环的时候将读取的文件名赋值给$filename，为了不陷于死循环，所以还要让$filename !== false。一定要用!==，因为如果某个文件名如果叫’0′，或者某些被系统认为是代表false，用!=就会停止循环 */
    while (($filename = readdir($handler)) !== false) {
        // 3、目录下都会有两个文件，名字为’.'和‘..’，不要对他们进行操作
        if ($filename != '.' && $filename != '..') {
            // 4、进行处理
            array_push($array, $filename);
        }
    }
    //5、关闭目录
    closedir($handler);
    return $array;
}




 function get_practice_type(){
    return  [
        5=>"单项选择题",
        10=>"多项选择题",
        15=>"判断题",
        20=>"填空题",
        25=>"名词解释",
        30=>"简答题",
        35=>"论述题",
        40=>"案例分析题",
        45=>"活动设计题",
        50=>"完形填空",
        55=>"单词拼写",
        60=>"阅读理解",
        65=>"词形填空",
        70=>"翻译题",
        75=>"判断说明题",
        80=>"词语解释题",
        85=>"材料分析题",
        90=>"综合分析题",
        95=>"计算分析题",
        100=>"核算题",
        105=>"计算题",
        110=>"应用题",
        115=>"综合应用题",
        120=>"作文",
        135=>'图片选择填空题',
    ];
 }




function http_post_json($url, $jsonStr){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($jsonStr)
        )
    );
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}


