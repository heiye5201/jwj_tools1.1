<?php

namespace jwj_tools\tools\Qiniu;


use Qiniu\Auth;
use Qiniu\Storage\UploadManager;

class QiniuManager{

    //练习表
    const VIDEO_1 = 1;
    const IMG_2   = 2;
    const DOC_3   = 3;
    const NOTE_4  = 4;

    public static function getTokenAndDomain($file_type = 0){
        $accessKey =  env('qiniu_oss.access_key') ;
        $secretKey = env('qiniu_oss.secret_key') ;
        try{
            $bucketArr = self::getBucket($file_type);
            $auth = new Auth($accessKey, $secretKey);
            $token = $auth->uploadToken($bucketArr['bucket'],null,3600*24);
        }catch (\Exception $e){
            throw new \Exception($e->getMessage(), $e->getCode());
        }
        $data = [
            'domain' => $bucketArr['domain'],
            'uptoken' => $token
        ];
        return $data;
    }

    protected static function getBucket($file_type =0){
        switch($file_type){
            case self::VIDEO_1 :
                return  [
                    'domain'=>env('qiniu_oss.video_domain_url'),
                    'bucket'=>env('qiniu_oss.video_bucket')
                ] ;
                break;
            case self::IMG_2 :
                return  [
                    'domain'=>env('qiniu_oss.img_domain_url'),
                    'bucket'=>env('qiniu_oss.img_bucket')
                ] ;
                break;
            case self::DOC_3 :
                return  [
                    'domain'=>env('qiniu_oss.word_domain_url'),
                    'bucket'=>env('qiniu_oss.word_bucket')
                ] ;
                break;
            case self::NOTE_4 :
                return  [
                    'domain'=>env('qiniu_oss.note_domain_url'),
                    'bucket'=>env('qiniu_oss.note_bucket')
                ] ;
                break;
            default:
                throw new \think\Exception('未知文件类型', 102);
        }

    }

    /**
     * 上传base64位图片到七牛云
     * $image base64位图片流
     */
    public function uploadPicBase64($image)
    {
        // 去除base64,
        $num = strpos($image, ',');
        $image = substr($image, $num + 1);
        $str = isset($image) ? $image : false;
        //生成图片key
        $rand = rand(1111, 9999);
        $now = time();
        $name = $now . $rand;
        $Key = $name;
        $data = $this->getTokenAndDomain(self::IMG_2);
        $upToken = $data['uptoken'];
        if ($str) {
            $qiniu = $this->phpCurlImg(env('qiniu_oss.base64_url') . $Key, $str, $upToken);
            $qiniuArr = json_decode($qiniu, true);
            if (!empty($qiniuArr['key'])) {
                return $qiniuArr['key'];
            } else {
                throw new \Exception($qiniu, 102);
            }
        }
        return false;

    }


    //七牛base64上传方法
    public function phpCurlImg($remote_server,$post_string,$upToken)
    {
        $headers = array();
        $headers[] = 'Content-Type:application/octet-stream';
        $headers[] = 'Authorization:UpToken '.$upToken;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$remote_server);
        curl_setopt($ch, CURLOPT_HTTPHEADER ,$headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }


    public function uploads_pptx($tmp_name,$name,$type = 'ppt',$path = '',$origin = 0,$chaper_id){
        //跨域不需要
        $img_name_arr = explode('.',$name);
        $img_suffix = $img_name_arr[count($img_name_arr)-1];//图片后缀名
        $newName = $chaper_id.'_'.time();
        if($img_suffix != 'pptx'){
            return array('status'=>102,'msg'=>'格式有误!','data'=>$name);
        }
        //新文件名
        $imageName = $type."_".date("Ymd_His",time())."_".rand(1111,9999).'.'.$img_suffix;
        if(!$path){
            $path = "uploads/ppt";//图片路径
        }
        if (!is_dir($path)){ //判断目录是否存在 不存在就创建
            mkdir($path,0777,true);
        }
        $imageSrc= './'.$path.'/'.$newName.'.pptx';
        $re = move_uploaded_file($tmp_name,$imageSrc);
        if(!$re){
            return array('status'=>102,'msg'=>'文件上传失败','data'=>array());
        }else{
            return array('status'=> 200,'data'=>array('img_src'=>$path.'/'.$newName),'msg'=>'OK');
        }

    }



    public static function upload_hand_out_file_path($filePath){
        $accessKey = env('qiniu_oss.access_key') ;
        $secretKey = env('qiniu_oss.secret_key') ;
        $bucket = env('qiniu_oss.img_bucket') ;
        // 构建鉴权对象
        $auth = new Auth($accessKey, $secretKey);
        // 生成上传 Token
        $token = $auth->uploadToken($bucket);
        // 要上传文件的本地路径
        $uploadMgr = new UploadManager();
        $returnData = [];
        $mid_key = explode('/',$filePath); ;
        $key = $mid_key[count($mid_key) - 1];
        list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
        $returnData[] = $ret['key'];
        return $returnData;
    }




    public static function upload_hand_out_file($data1){
        // 需要填写你的 Access Key 和 Secret Key
        $accessKey = env('qiniu_oss.access_key') ;
        $secretKey = env('qiniu_oss.secret_key') ;
        $bucket = env('qiniu_oss.img_bucket') ;
        // 构建鉴权对象
        $auth = new Auth($accessKey, $secretKey);
        // 生成上传 Token
        $token = $auth->uploadToken($bucket);
        // 要上传文件的本地路径
        $uploadMgr = new UploadManager();
        $returnData = [];
        foreach ($data1 as $key => $value){
            $filePath = $value;
            $mid_key = explode('/',$value); ;
            $key = $mid_key[count($mid_key) - 1];
            list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
            $returnData[] = $ret['key'];
        }
        return $returnData;
    }



    public static function getToken($file_type = 0){
        $accessKey =  env('QINIU_OSS.ACCESS_KEY') ;
        $secretKey = env('QINIU_OSS.SECRET_KEY') ;
        try{
            $bucketArr = self::getBucket($file_type);
            $auth = new Auth($accessKey, $secretKey);
            $domain = $bucketArr['domain'] ;
            $returnBody = '{"key":"$(key)","hash":"$(etag)","filename":"$(fname)","fsize":"$(fsize)","code":"0","msg":"success","data":{"src": "'.$domain.'/$(key)","title": "$(x:name)"}}';//此处为设置json返回格式
            $policy = array(
                'returnBody' => $returnBody,
            );
            // 简单上传凭证
            $expires = 3600*24;
            $token = $auth->uploadToken($bucketArr['bucket'], null, $expires, $policy, true);
        }catch (\Exception $e){
            throw new \Exception($e->getMessage(), $e->getCode());
        }
        $data = [
            'domain' => $bucketArr['domain'],
            'uptoken' => $token
        ];
        return $data;
    }




    public static function upload_file($filePath,$file_type=1){
        // 需要填写你的 Access Key 和 Secret Key
        $accessKey = env('qiniu_oss.access_key') ;
        $secretKey = env('qiniu_oss.secret_key') ;
        $bucketArr = self::getBucket($file_type);
        $domain = $bucketArr['domain'] ;
        $bucket = $bucketArr['bucket'] ;
        // 构建鉴权对象
        $auth = new Auth($accessKey, $secretKey);
        // 生成上传 Token
        $expires = 3600*24;
        $ext = $filePath->getOriginalExtension() ;
        $name = $filePath->getOriginalName() ;
        $returnBody = '{"key":"$(key)","hash":"$(etag)","filename":"$(fname)","ext":"$(ext)","fsize":"$(fsize)","code":"0","msg":"success","data":{"src": "/$(key)","title": "$(x:name)"}}';//此处为设置json返回格式
        $policy = array(
            'returnBody' => $returnBody,
        );
        $token = $auth->uploadToken($bucket,null,$expires,$policy);
        // 要上传文件的本地路径
        $key = time().'.'.$ext ;
        $uploadMgr = new UploadManager();
        $params = [
            'key'=>time().'.docx',
        ] ;
        list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath,$params);
        return $domain.$ret['data']['src'];
    }


    public static function uploadLocationFile($filePath,$import_name,$file_type=1){
        $accessKey = env('qiniu_oss.access_key') ;
        $secretKey = env('qiniu_oss.secret_key') ;
        $bucketArr = self::getBucket($file_type);
        $domain = $bucketArr['domain'] ;
        $bucket = $bucketArr['bucket'] ;
        $file_array = explode('.',$filePath)  ;
        $ext = isset($file_array[1])?$file_array[1]:"";

        // 构建鉴权对象
        $auth = new Auth($accessKey, $secretKey);
        // 生成上传 Token
        $expires = 3600*24;
        $returnBody = '{"key":"$(key)","hash":"$(etag)","filename":"$(fname)","ext":"$(ext)","fsize":"$(fsize)","code":"0","msg":"success","data":{"src": "/$(key)","title": "$(x:name)"}}';//此处为设置json返回格式
        $policy = array(
            'returnBody' => $returnBody,
        );
        $token = $auth->uploadToken($bucket,null,$expires,$policy);
        // 要上传文件的本地路径
        $key = $import_name.'_'.time().'.'.$ext ;
        $uploadMgr = new UploadManager();
        $params = [
            'key'=>$import_name.'_'.time().'.'.$ext,
        ] ;
        list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath,$params);
        return $domain.$ret['data']['src'];
    }







}