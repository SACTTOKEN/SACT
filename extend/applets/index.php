<?php
/**
 * Created by yaaaaa_god
 * User: yaaaaa
 * Date: 2018/12/13
 * Desc: 微信支付
 */

namespace extend\applets;

class index{
    public function __construct()
    {
        define('JSAPI_ROOT', dirname(__FILE__) . '/');
        require_once(JSAPI_ROOT . 'wxBizDataCrypt.php');
    }

    public function index($data)
    {
        $appid = c('applets_appid');
        $sessionKey = $data['sessionKey'];
        $encryptedData=$data['encryptedData'];
        $iv = $data['iv'];
        
        $pc = new \WXBizDataCrypt($appid, $sessionKey);
        $errCode = $pc->decryptData($encryptedData, $iv, $res );
        
        if ($errCode == 0) {
            return $res;
        } else {
            error('获取失败'.$errCode,404);
        } 
    }
}




