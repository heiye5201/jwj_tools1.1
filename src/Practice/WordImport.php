<?php

namespace jwj_tools\tools\Practice;
use PhpOffice\PhpWord\IOFactory;

class WordImport
{

    /**
     * 获得word的内容数据
     * @param $wordUrl
     * @return array
     */
    public function getCoreWordData($wordUrl){
        $html = $this->wordToHtmlGet($wordUrl) ;
        $data = $this->getPractice($html) ;
        return $data ;
    }


    /**
     * 核心考点数据解析
     * @param $practices
     * @return array
     */
    public  function formatWordArrayNew($practices){
        $contents = [] ; $titles = [] ; $data = [] ; $labels = [] ;
        foreach ($practices as $key=>$value){
            if(count($value)>0){
                $have = strstr($value[1], '答:');
                $zh_have = strstr($value[1], '答：');
                if($have||$zh_have){
                    $content = str_replace("#","&nbsp",$value[1]);
                    $contents[] = $content ;
                }
                $b= mb_strpos($value[0],"【");
                $c=  mb_strpos($value[0],"】");
                $labels[] = mb_substr($value[0],$b+1,$c-1,'utf-8');
                $titles[] = $value[0] ;
            }
        }
        foreach ($titles as $key=>$value){
            $data[] = [
                'title' => $value ,
                'content'=>isset($contents[$key])?$contents[$key]:"",
                'label'=>isset($labels[$key])?$labels[$key]:"",
            ] ;
        }
        unset($contents) ;unset($titles) ;unset($labels) ;unset($practices) ;
        return $data ;
    }



    /**
     * 解析word 生成html格式的文件
     * @param $wordUrl
     * @return false|string
     */
    public function wordToHtmlGet($wordUrl){
        $wordUrl = preg_replace("/ /", "%20", $wordUrl);

        $file = file_get_contents($wordUrl);
        $docx = './tmp/'.time().'.docx';
        file_put_contents($docx,$file) ;

        $tmp = self::getWordToHtml($docx) ;
        $html = file_get_contents($tmp) ;
        unlink($tmp) ;
        return $html ;
    }


    /**
     * word转换成html
     * @param $form
     * @return string
     * @throws \PhpOffice\PhpWord\Exception\Exception
     */
    public function getWordToHtml($form)
    {
        $php_word = IOFactory::load($form) ;
        $xml_writer = IOFactory::createWriter($php_word,'HTML') ;
        $fileName = './tmp/word'.time().'.html' ;
        $xml_writer->save($fileName) ;
        unlink($form) ;
        return $fileName;
    }




    /**
     * html 数据格式出来
     * @param $html
     * @return array
     */
    public function getPractice($html){
        $html = self::FromData($html);
        if($html){
            $quesionArr = explode("<p>&nbsp;</p><p>&nbsp;</p>",$html);
            $practice = [];
            foreach($quesionArr as $k=>$v){
                preg_match_all('/<p >(.*?)<\/p>/',$v,$s);
                $onePractice = $s[1];
                if(is_array($onePractice)){
                    foreach($onePractice as $key=>&$val){
                        $val = str_replace(array("<span >", "</span>", "<span>"), "", $val);
                    }
                }
                $practice[] = $onePractice;
            }
            return $practice ;
        }
        return [];
    }


    //组织html数据
    protected function FromData($html){
        $html = self::cutStyle($html);
        $html = self::cutBodyContent($html);
        return self::cutRn($html);
    }


    //去掉样式
    protected function cutStyle($html){
        return preg_replace('/style=".*?"/i', '', $html);
    }

    //截取body内容
    protected function cutBodyContent($html,$begin='<body>',$end='</body>'){
        $b = mb_strpos($html,$begin) + mb_strlen($begin);
        $e = mb_strpos($html,$end) - $b;
        return mb_substr($html,$b,$e);
    }

    //去掉\r\n
    protected function cutRn($html){
        return str_replace(array("\r\n", "\r", "\n"), "", $html);
    }



}