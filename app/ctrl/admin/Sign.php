<?php
/**
 * Created by yaaaaa_god
 * User: god
 * Date: 2018/12/13
 * Desc: 生成签名
 */
namespace app\ctrl\admin;
use app\exception\BaseException;



class Sign{
	
	
	/*生成签名,admin_key参与签名，不做参数,模拟前端*/
	public function index(){
        
		$timestamp = $_SERVER['HTTP_TIMESTAMP'];
		$extra = $_SERVER['HTTP_EXTRA'];
		$uid = $_SERVER['HTTP_HEADUID'];
		$meid = $_SERVER['HTTP_MEID'];

		$req_params = array_merge($_GET,$_POST);
	
		$req_params['timestamp'] = $timestamp;
		$req_params['meid'] = $meid;
		$req_params['extra'] = $extra;
		$req_params['uid'] = $uid;

		//$req_params['admin_key'] = post('admin_key');
		$req_params['admin_key'] = 'g3nM6AWiReyMj0Kr';

        unset($req_params['sign']);
       
		ksort($req_params);

		$sign_url = '';
	
		foreach ($req_params as $key => $val)
		{
		$sign_url.=$key.'='.$val.'&';
		}
	
		$sign_url = substr($sign_url, 0,-1);
	
		$sign = md5($sign_url);

		if(empty($sign) || empty($timestamp)){
			error('参数丢失',400);
		}

        return $sign;     
	}


	/*生成用户签名,user_key参与签名，不做参数,模拟前端*/
	public function user_sign(){
        
		$timestamp = $_SERVER['HTTP_TIMESTAMP'];
		$extra = $_SERVER['HTTP_EXTRA'];
		$uid = $_SERVER['HTTP_UID'];
		$meid = $_SERVER['HTTP_MEID'];

		$req_params = array_merge($_GET,$_POST);
	
		$req_params['timestamp'] = $timestamp;
		$req_params['meid'] = $meid;
		$req_params['extra'] = $extra;
		$req_params['uid'] = $uid;

		$req_params['user_key'] = post('user_key');
		//$req_params['admin_key'] = 'g3nM6AWiReyMj0Kr';

        unset($req_params['sign']);
       
		ksort($req_params);

		$sign_url = '';
	
		foreach ($req_params as $key => $val)
		{
		$sign_url.=$key.'='.$val.'&';
		}
	
		$sign_url = substr($sign_url, 0,-1);
	
		$sign = md5($sign_url);

		if(empty($sign) || empty($timestamp)){
			error('参数丢失',400);
		}

        return $sign;     
	}




}