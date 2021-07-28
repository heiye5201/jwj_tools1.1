<?php
namespace jwj_tools\tools\Api;


class UploadFile
{
    protected $upload_host = '';//上传url
    protected $oauth_user_name = '';//网关账号
    protected $oauth_secret = '';//网关secret
    protected $oauth_gateway = '';//网关端口
    public function __construct($oauth = array()){
        $this->upload_host = $oauth['upload_host'];
        $this->oauth_user_name = $oauth['oauth_user_name'];
        $this->oauth_secret = $oauth['oauth_secret'];
        $this->oauth_gateway = isset($oauth['oauth_gateway']) ? $oauth['oauth_gateway'] : '' ;
    }

    /**
     * @param $data
     * @param string access_key 七牛云accessKey
     * @param string secret_key 七牛云secretKey
     * @param string bucket 七牛云bucket
     * @return \think\response\Json
     */
    public function upload_file( $data= []){
        if(!isset($_FILES["file"])){
            return json(array('status'=>102,'msg'=>'error:未接收到文件!','data'=>$_FILES));
        }
        $file = $_FILES['file'];
        $name_arr = explode('.',$file['name']);
        $suffix = end($name_arr);//文件后缀
        $cfile = curl_file_create($file['tmp_name'],$file['type'],'');
        $data['type'] = isset($data['type']) ? $data['type'] : input('type',1);
        $data['file'] = $cfile;
        $data['safe'] = input('safe',1);
        $data['suffix'] = $suffix;
        $re_json = $this->curlPost($data);
        $re = json_decode($re_json,true);
        if($re['status'] == 200){
            $res['data']['input_info'] = $re['data']['file_src'];
            $res['data']['info'] = config('img_domain').$re['data']['file_src'];
            $res['status'] = 200;
            $res['msg'] = 'ok';
            return json_encode($res);
        }
        return json_encode(array('status'=>102,'data'=>$re_json,'msg'=>'上传失败'));
    }


    private function curlPost($params)
    {
        $ch = curl_init();
        $url = $this->upload_host;
        $user_name = $this->oauth_user_name;
        $secret = $this->oauth_secret;
        $date = gmdate('D, d M Y H:i:s \G\M\T');

        $str = "X-date: ".$date . '';
        $signature = base64_encode(hash_hmac("sha1", $str, $secret, true));

        $head = array(
            //"Host: ".parse_url($url)['host'],
            "X-date: ".$date,
            'Authorization: hmac username="'.$user_name.'", algorithm="hmac-sha1", headers="X-date", signature="'.$signature.'"',
        );

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $head);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 3000);
        if($this->oauth_gateway){
            curl_setopt($ch, CURLOPT_PORT, $this->oauth_gateway);
        }

        if (!empty($params)) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }

        $rs = curl_exec($ch);

        curl_close($ch);

        return $rs;
    }

}
