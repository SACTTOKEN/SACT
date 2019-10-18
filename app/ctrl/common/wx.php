<?php
/**
 * Created by yaaaaa_god
 * User: god
 * Date: 2019-01-03
 * Desc: 微信功能接口(不查数据库，纯功能层可接收GET传值)
 */
namespace app\ctrl\common;
class wx{

    //首次关注与自动回复
	public function index(){
		$wechatObj = new \app\service\wechat();
		if(get('echostr')){
			$wechatObj->valid();
		}else{	
			$wechatObj->responseMsg();				
		}		
        exit();
	}

	//微信公众号菜单
    public function wx_menu(){
  		$wechatObj = new \app\service\wechat();
		$wechatObj->pubMenu();
    }

    //生成二维码
    public function wx_ewm(){
        $wechatObj = new \app\service\wechat();
        $res = $wechatObj->gzh_ewm($uid);
        return $res;
    }

    //微信认证回调地址（入口源于：/mobile/power/weixin_login,前后端分离这里是出口,回跳到前端 因为要get传参，所以放common/wx里）
    public function wx_login()
    {     
        $wechatObj = new \app\service\wechat(); 
        $code = get('code'); //微信登录step2:获取认证code
        $state = get('state','');

        $openid_str = $wechatObj->wechat_openid($code); //微信登录step3:获取openid
        $openid=array();
      	$openid['openid']=$openid_str;
        //$openid='o0_V91TfO_a7g3-aCOlkJCKT1VLg';
        $power = new \app\ctrl\mobile\power();
        $power->__initialize();
        $final_res = $power->check_openid($openid,$state);  

        $final_link = $_COOKIE['final_link'];

        if(empty($final_link)){
            $web = c('wx_mobile_web');
            header('Location:'.$web);
        }else{
            header('Location:'.$final_link);
        }

        //if($final_res){
        //}else{
        //    header('Location:'.$web.'/recommend');
        //}
    }

    public function wx_pay()
    {
        $code = get('code');
        $wechat=new \extend\wechat_pay\jsapi();
        $wechat->index('',$code);     
        $web = c('wx_mobile_web');
        header('Location:'.$web);   
    }

    public function wx_app_pay()
    {
        $wechat=new \extend\wechat_pay\jsapi('wechat_app');
        $wechat->index('','');     
        $web = c('wx_mobile_web');
        header('Location:'.$web);   
    }

}
	
	




