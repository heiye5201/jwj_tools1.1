<?php

namespace jwj_tools\tools\Practice;


use jwj_tools\tools\Qiniu\QiniuManager;

class PracticeWord
{

    protected $courses_id = 0 ;
    protected $practice_word_id = 0 ;
    protected $practiceType = '' ;
    protected $practice = [] ;
    protected $practiceArr = [] ;


    public  function getWordData($wordUrl,$practiceWordId,$coursesId){
        $word_model = new WordImport() ;
        $html =  $word_model->wordToHtmlGet($wordUrl);
        $data = $word_model->getPractice($html) ;
        $this->dealPractice($data,$practiceWordId,$coursesId);
        return [] ;
    }



    public function dealPractice($data,$practice_word_id =0,$courses_id=0){
        if($data){
            foreach($data as $k=>$v){
                if(!empty($v)){
                    $this->cutIndexOne($v);
                }
            }
        }
    }


    //去除题型
    public function cutIndexOne($onePractice){
        try{
            $practiceType = $this->getType($onePractice['0']);
            if(empty($practiceType)){
                throw new \Exception('题型不存在:'.$onePractice['0'], 103);
            }
        }catch (\Exception $e){
            throw new \Exception(($e->getMessage())." 题型出错:".$onePractice['0'], 103);
        }
        unset($onePractice['0']);
        $this->getPracticeSend($practiceType,$onePractice);
    }


    //获取题型
    protected function getType($firstV){
        $practice = explode('.',$firstV);
        $practiceTypeValue = str_replace(' ', '', $practice['1']);
        $this->practice_type_value = $practiceTypeValue;
        $keys = array_search($practiceTypeValue,\Tools\get_practice_type());
        return $keys;
    }



    //分发处理
    public function getPracticeSend($practiceType,$onePractice){
        if($practiceType && $onePractice){
            $this->dealDetail($onePractice);
            $this->parentDeal();
            $this->practice = [];
        }
    }


    public function dealDetail($onePractice){
        if(isset($onePractice) && !empty($onePractice)){
            foreach($onePractice as $k=>$practice){
                try{
                    $this->forwardDeal($practice);
                }catch (\Exception $e){
                    $p = $onePractice;
                    $title = $p['1'];
                    throw new \Exception(($e->getMessage())." 题目是:$title", 103);
                }

            }
        }
    }




    protected function parentDeal(){
        $onePractice = [];
        $onePractice['courses_id'] = $this->courses_id;
        $onePractice['practice_word_id'] = $this->practice_word_id;
        $onePractice['practice_type'] = $this->practiceType;
        $onePractice['practice_title'] = $this->practice['practice_title'];
        $practiceSecondTitle = isset($this->practice['practice_second_title']) ? $this->practice['practice_second_title'] :[];
        $option = isset($this->practice['option']) ? $this->practice['option'] :[];
        $onePractice['practice_question'] = json_encode(["practice_second_title"=>$practiceSecondTitle,"option"=>$option],JSON_UNESCAPED_UNICODE);
        $onePractice['practice_answer'] = json_encode($this->practice['practice_answer'],JSON_UNESCAPED_UNICODE);
        $onePractice['practice_resolve'] = isset($this->practice['practice_resolve']) ? $this->practice['practice_resolve'] : '';
        $onePractice['practice_explain'] = isset($this->practice['practice_explain']) ? $this->practice['practice_explain'] : '';
        $onePractice['add_time'] = time();
        $this->practiceArr[] = $onePractice;
    }




    //$is_option 1:选项类型,需要处理，0不需要处理
    public function forwardDeal($practice){
        $tag = substr($practice , 0 , 7);
        $tagContent = substr($practice , 7);
        switch($tag){
            case "题目:":
                $this->dealPracticeTitle($tagContent);
                break;
            case "选项:":
                $this->dealOption($tagContent);
                break;
            case "答案:":
                $this->dealAnswer($tagContent);
                break;
            case "解析:":
                $this->dealAnalysis($tagContent);
                break;
            case "次题:":
                $this->dealSecondaryTitle($tagContent);
                break;
            case "说明:":
                $this->dealExplain($tagContent);
                break;
            default:
                throw new \Exception('题型: "'.$this->practice_type_value.'" 格式出错(换行或者未知标签..问题)', 404);

        }
    }



    //处理题目
    public function dealPracticeTitle($tagContent){
        if($this->practice_word_id){
            //处理题目换行
            $tagContent = str_replace('#' , '</br>',$tagContent);
            //处理标题图片
            $tagContent = $this->dealImg($tagContent);
        }
        $this->practice['practice_title'] = $tagContent;
    }



    //处理选项
    public function dealOption($tagContent){
        $optionCon = explode('|',$tagContent);
        $option = [];
        if($optionCon){
            foreach($optionCon as &$v){
                $voption = explode('.',$v);
                $key = $voption[0];
                $val = $voption[1];
                $option[$key] = $val;
            }
        }
        $this->practice['option'][] = $option;
    }


    //处理答案
    public function dealAnswer($tagContent){
        $optionAnswoer = $tagContent;
        if($this->practice_word_id){
            //处理换行
            $tagContent = str_replace('#' , '</br>',$tagContent);
            $tagContent = $this->dealImg($tagContent);
        }
        if(!empty($tagContent)){
            $optionAnswoer = explode('|',$tagContent);
        }
        $this->practice['practice_answer'] = $optionAnswoer;
    }


    //处理解析
    public function dealAnalysis($tagContent){
        if($this->practice_word_id){
            //处理换行
            $tagContent = str_replace('#' , '</br>',$tagContent);
        }
        $this->practice['practice_resolve'] = $tagContent;
    }




    //处理说明
    public function dealExplain($tagContent){
        if($this->practice_word_id){
            //处理换行
            $tagContent = str_replace('#' , '</br>',$tagContent);
        }
        $this->practice['practice_explain'] = $tagContent;
    }



    //处理次级题目
    public function dealSecondaryTitle($tagContent){
        if($this->practice_word_id){
            //处理换行
            $tagContent = str_replace('#' , '</br>',$tagContent);
        }
        $this->practice['practice_second_title'][] = $tagContent;
    }


    public function dealImg($tagContent){
        $imgpreg = "/<img (.*?) src=\"(.+?)\".*?>/";
        preg_match($imgpreg,$tagContent,$imgs);
        if(count($imgs) > 0){
            $qiniu_model = new QiniuManager() ;
            $key = $qiniu_model->uploadPicBase64($imgs['2']);
            $domainUrl = env('qiniu_oss.img_domain_url') ;
            $src = $domainUrl.$key;
            $tagContent = preg_replace($imgpreg,"<img src='".$src."' />",$tagContent);
        }
        return $tagContent;
    }

}

