<?php
/**
 * Created by yaaaaa_god
 * User: yaaaaa
 * Date: 2018/12/13
 * Desc: 控制器公用
 */
namespace app\ctrl\mobile;
use core\lib\redis;
class BaseController extends \core\imooc{

	function __construct(){
		if(c('wzkg')==0){
            $err['info']='';
            $err['url']=c('gztp');
            error($err,10008);	
		}
		$redis = new redis();
		$uid = find_server('HTTP_UID');	
	
		$redis_name = 'user:'.$uid;
		$token = $redis->hget($redis_name,'user_token');
		$GLOBALS['user'] = $redis->hget($redis_name);
		if(!DEBUG){
			if(EXTRA=='applets'){
				$token = $redis->hget($redis_name,'applets_token');
				$http_token = find_server('HTTP_TOKEN');
				if($token=='' || $http_token=='' || $http_token!=$token){      
          
					set_cookie("oauser","",0);
					set_cookie("user_token","",0);
					error('请先登录',602); //无效token
				}
			}else{
				//身份是否合法
				if($token=='' || $_COOKIE['user_token']=='' || $token != $_COOKIE['user_token']){  
					set_cookie("user","",0);
					set_cookie("user_token","",0);
					error('请先登录',602); //无效token
				}
				//验签
				$ret = $this->checksign();
				if($ret!==true){
					set_cookie("user","",0);
					set_cookie("user_token","",0);
					error('请先登录',602);
				} 
			}
		}else{
			if(isset($GLOBALS['user']['id'])){
				$GLOBALS['user']['show']=(new \app\model\user())->find($GLOBALS['user']['id'],'show');
			}		
		}

		if(empty($GLOBALS['user'])){
        	set_cookie("user","",0);
			set_cookie("user_token","",0);
			error('请先登录',602);
		}
        if(empty($GLOBALS['user']['show'])){
        	set_cookie("user","",0);
			set_cookie("user_token","",0);
			error('请先登录',602);
        }
        if($GLOBALS['user']['show']==0){
        	set_cookie("user","",0);
			set_cookie("user_token","",0);
			error('账号被冻结',602);
        }
		
	}

	/**
 	*   验签,用uid到redis内查找到admin_key,用admin_key生成sign比对$route = new \core\lib\route();
	*		$app=$route->app;
	*		$ctrlClass=$route->ctrl;
	*		$action=$route->action;
 	*   @param sign 签名
 	*   @return  boolean
 	*/
	public function checksign()
	{
		$timestamp = isset($_SERVER['HTTP_TIMESTAMP']) ? $_SERVER['HTTP_TIMESTAMP'] : '';
		$extra = isset($_SERVER['HTTP_EXTRA']) ? $_SERVER['HTTP_EXTRA'] : '';
		$uid = isset($_SERVER['HTTP_UID']) ? $_SERVER['HTTP_UID'] : error('用户不存在',602);
		$meid = isset($_SERVER['HTTP_MEID']) ? $_SERVER['HTTP_MEID'] : '';
		$req_params = array_merge($_POST);
		if($timestamp-time()>1800 || $timestamp-time()<-1800){
            error("签名错误",400);
        }
		$req_params['timestamp'] = $timestamp;
		$req_params['uid'] = $uid;		
		$req_params['extra'] = $extra;		
		$req_params['meid'] = $meid;
			
		$redis = new redis();
		$redis_name = 'user:'.$uid;
		$user_key = $redis->hget($redis_name,'user_key');
		$req_params['user_key'] = $user_key;
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
        	/* $error[]="接收参数排序后：".$sign_url;
        	$error[]="接收参数签名:".$sign;
        	$error[]="前台传的签名:".$sign_str;
        	$error[]="redis取出user_key:".$user_key;
        	var_dump($error);
        	exit();  */
        	return false;
        }
		return true;
	}
    
}