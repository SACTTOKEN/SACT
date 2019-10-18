<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-27 11:44:36
 * Desc: 微信小程序
 */
namespace app\service;

class applets{  

    public $appID = '';
    public $appsecret = '';

    public function  __construct(){
        $this->appID = c('applets_appid');
        $this->appsecret = c('applets_appsec');
    }

    public function code2Session($code)
    {
        $get_user_info_url = "https://api.weixin.qq.com/sns/jscode2session?appid=".$this->appID."&secret=".$this->appsecret."&js_code=".$code."&grant_type=authorization_code";
        $userinfo = $this->https_request($get_user_info_url);
        $userinfo = json_decode($userinfo,true);
        //$userinfo['openid']='o1PcZ42KbOzvb8KZCvwHm9Bt_rq4'; //测试
        return $userinfo;
    }


    public function https_request($url, $data = null)
    {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    if (!empty($data)){
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    curl_close($curl);
    return $output;
    }

}
