<?php 
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-03-28 09:23:02
 * Desc: IM
 */
namespace app\ctrl\mobile;
use app\ctrl\mobile\BaseController;
class im extends BaseController{
    public function login()
    {
        $user = $GLOBALS['user'];
        $im = new \app\service\im();  
         //im注册
        if(!plugin_is_open("imhyjsnt")){
           error("未开启IM系统",400); 
        }
        if($user['im']==""){
            $im->login_one($user['id'],$user['username'],$user['nickname'],$user['avatar']);
            $user['im']=c("ptid")."_".$user['id'];
        }
        $im_sig = $im->create_sig($user['im']);
        empty($im_sig) && error('IM签名失败',400);
        return $im_sig;
    }

}