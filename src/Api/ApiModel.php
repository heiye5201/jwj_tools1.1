<?php
/**
 * Created by PhpStorm.
 * User: 11208
 * Date: 2018/10/8
 * Time: 15:31
 */

namespace jwj_tools\tools\Api;


class ApiModel
{


    public function getData($url,$param){
        $rs = $this->getRes($url,$param);
        if($rs->getRet() == 200){
            $data = $rs->getData();
            return array('status'=>true,'data'=>$data);
        }else{
            return array('status'=>false,'msg'=>$rs->getMsg(),'code'=>$rs->getRet());
        }
    }



    public function updateData($url,$param){
        $rs = $this->getRes($url,$param);
        if($rs->getRet() == 200){
            return array('status'=>true,'msg'=>$rs->getMsg());
        }else{
            return array('status'=>false,'msg'=>$rs->getMsg(),'code'=>$rs->getRet());
        }
    }



    /**
     * @param $url
     * @param $param
     * @return PhalApiClientResponse
     */
    private function getRes($url, $param){
        $oauth_user_name = isset($param['oauth_user_name']) ? $param['oauth_user_name'] :env('three_api_oauth.user_name') ;
        $oauth_secret = isset($param['oauth_secret']) ? $param['oauth_secret'] :env('three_api_oauth.secret')  ;
        $client = PhalApiClient::create()
            ->withHost(env('three_api.base_url')); //config('service_api')[$api] BASE_URL
        $client->reset()
            ->withService($url)
            ->withTimeout(3000);
        $user_id = session('user_id');
        if(empty($user_id)){
            $user_id = 0;
        }
        $client->withParams('operate_admin_id',$user_id);
        $client->withParams('oauth_user_name',$oauth_user_name);
        $client->withParams('oauth_secret',$oauth_secret);
        foreach ($param as $paramName => $paramVal){
            if(is_array($paramVal)){
                $client->withParams($paramName,implode(',',$paramVal));
            }else{
                $client->withParams($paramName,$paramVal);
            }
        }
        $rs = $client->request();
        return $rs;
    }



}