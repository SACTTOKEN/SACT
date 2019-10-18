<?php 
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-03-28 09:23:02
 * Desc: IM
 */
namespace app\ctrl\admin;
use app\ctrl\admin\BaseController;
class im_admin extends BaseController{
    public function login_im()
    {
        $admin = $GLOBALS['admin'];
        $im = new \app\service\im();  
         //im注册
        if(!plugin_is_open("imhyjsnt") && !plugin_is_open('btkfxt')){
           error("未开启IM系统",400); 
        }
        if($admin['im']==""){
		$head=(new \app\model\banner())->have(['cate'=>'head'],'piclink');
        $im->login_admin($admin['id'],$admin['username'],$admin['nick_name'],$head);
        $admin['im']=c("ptid")."_admin_".$admin['id'];
        }
        $im_sig = $im->create_sig($admin['im']);
        empty($im_sig) && error('IM签名失败',400);

        $loginInfo['identifier']=$admin['im'];
        $loginInfo['userSig']=$im_sig;
        return $loginInfo;
    }



}