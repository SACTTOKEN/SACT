<?php
/**
 * Created by yaaaaa_god
 * User: yaaaaa
 * Date: 2018/12/13
 * Desc: 控制器公用
 */
namespace app\ctrl\admin;
use core\lib\redis;
class BaseController extends \core\imooc{

	function __construct(){	
      
		$redis = new redis();
		$headuid = find_server('HTTP_HEADUID');	
		$redis_name = 'admin:'.$headuid;
		$token = $redis->hget($redis_name,'token');
		$GLOBALS['admin'] = $redis->hget($redis_name,'info');  //在网上管理员登录后测OK，本地需redis支持
		empty($GLOBALS['admin']) && error('此账号已在其它地方登录，请重新登录',602);
		($GLOBALS['admin']['show']==0) && error('账号被冻结',602);
			
		//权限
		if($GLOBALS['admin']['role_con'] != 'god'){
          
			$HTTP_REFERER=strstr(str_replace("//","",$_SERVER["HTTP_REFERER"]),'/');
			$HTTP_REFERER=rtrim($HTTP_REFERER,"/");

			if(!strstr($HTTP_REFERER,"?action=")){
				$HTTP_REFERER=explode("?",$HTTP_REFERER);
				$HTTP_REFERER=$HTTP_REFERER[0];
			}
			if(!($HTTP_REFERER=='/index' or $HTTP_REFERER=='')){
				$menuM = new \app\model\menu;
				$rs = $menuM->competence($HTTP_REFERER,$GLOBALS['admin']['role_con']);
				empty($rs) && error('权限不足',401);
			}
		}

		if(!DEBUG){
			//身份是否合法
			if($token=='' || $token != $_COOKIE['token']){ 
				set_cookie("token","");
				error('此账号已在其它地方登录，请重新登录',602); //无效token
			}
			//验签
			$ret = $this->checksign();
			if($ret!==true){
				set_cookie("token","");
				error('此账号已在其它地方登录，请重新登录',602);
			} 
		}
	}


	/**
 	*   验签,用headuid到redis内查找到admin_key,用admin_key生成sign比对$route = new \core\lib\route();
 	*   @param sign 签名
 	*   @return  boolean
 	*/
	public function checksign(){
		$timestamp = isset($_SERVER['HTTP_TIMESTAMP']) ? $_SERVER['HTTP_TIMESTAMP'] : '';
		$extra = isset($_SERVER['HTTP_EXTRA']) ? $_SERVER['HTTP_EXTRA'] : '';
		$headuid = isset($_SERVER['HTTP_HEADUID']) ? $_SERVER['HTTP_HEADUID'] : error('用户不存在',602);
		$meid = isset($_SERVER['HTTP_MEID']) ? $_SERVER['HTTP_MEID'] : '';
		$req_params = array_merge($_POST);
		if($timestamp-time()>1800 || $timestamp-time()<-1800){
            error("签名错误",400);
        }
		$req_params['timestamp'] = $timestamp;
		$req_params['headuid'] = $headuid;		
		$req_params['extra'] = $extra;		
		$req_params['meid'] = $meid;
			
		$redis = new redis();
		$redis_name = 'admin:'.$headuid;
		$admin_key = $redis->hget($redis_name,'admin_key');
		$req_params['admin_key'] = $admin_key;
		
		foreach ($req_params as $key => $val)
		{
			if(is_array($val)){
            	foreach($val as $keys=>$vo){
                	$req_params[$key.'['.$keys.']']=$vo;
                }
        		unset($req_params[$key]);
            }
		}
		
        unset($req_params['sign']);
		ksort($req_params);


		$sign_url = '';
		foreach ($req_params as $key => $val)
		{
			$sign_url.=$key.'='.$val.'&';
		}
		$sign_url = substr($sign_url, 0,-1);
		$sign = md5('@'.$sign_url.'@');

		$sign_str = post('sign');
        if($sign !== $sign_str){
        	/*$error[]="接收参数排序后：".$sign_url;
        	$error[]="接收参数签名:".$sign;
        	$error[]="提交签名:".$sign_str;
        	$error[]="redis取出admin_key:".$admin_key;
        	cs($error,1);*/
        	return false;
        }
		return true;
	}
    
}