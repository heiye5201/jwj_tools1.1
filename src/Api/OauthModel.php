<?php
/**
 * Created by PhpStorm.
 * User: hyh
 * Date: 2020/3/10
 * Time: 14:12
 */

namespace jwj_tools\tools\Api;


class OauthModel
{


    /**
     * 获取网关账号密码
     */
    public function get_oauth_info($appid){
        $url = 'http://127.0.0.1:8001/hmac-auths?consumer_id='.$appid;

        $oauth_info = $this->getCurl(array(),$url);
        $oauth_info = json_decode($oauth_info,true);
        $res = $oauth_info && isset($oauth_info['data'][0]) ? $oauth_info['data'][0] : false;
        return $res;
    }



    /**
     * 添加网关账号
     */
    public function add_oauth_info($username){
        $url = 'http://127.0.0.1:8001/consumers';
        $data = array('username'=>$username);
        $res = $this->postCurl($data,$url);
        $res = json_decode($res,true);
        return $res;
    }



    /**
     * 创建网关密钥
     */
    public function get_oauth_secret($id,$username){
        $url = 'http://127.0.0.1:8001/consumers/'.$id.'/hmac-auth';
        $data = array('username'=>$username);
        $res = $this->postCurl($data,$url);
        $res = json_decode($res,true);
        return $res;
    }



    /**
     * @param $data
     * @param $url
     * @return mixed
     */
    private static function getCurl($data, $url){
        $ch = curl_init();
        //如果有配置代理这里就设置代理
        //curl_setopt($ch,CURLOPT_PROXY, $proxyHost);
        //curl_setopt($ch,CURLOPT_PROXYPORT, $proxyPort);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        if($data){
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        $re = curl_exec($ch);
        //返回结果
        if($re){
            curl_close($ch);
            return $re;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            echo "出错，错误码:$error";exit;
        }
    }




    private static function postCurl($data, $url){
        $ch = curl_init();
        //如果有配置代理这里就设置代理
        //curl_setopt($ch,CURLOPT_PROXY, $proxyHost);
        //curl_setopt($ch,CURLOPT_PROXYPORT, $proxyPort);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch,CURLOPT_URL, $url);
        //curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        if($data){
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }
        $re = curl_exec($ch);

        //返回结果
        if($re){
            curl_close($ch);
            return $re;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            echo "出错，错误码:$error";exit;
        }
    }

}