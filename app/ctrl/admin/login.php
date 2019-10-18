<?php
/**
 * Created by yaaaaa_god 
 * User: GOD
 * Date: 2018-12-13 16:28:04
 * Desc: 后台登录类
 */

namespace app\ctrl\admin;

use app\model\admin as AdminModel;
use app\validate\AdminValidate;
use app\service\token;

class login{
	public function config()
	{
		$data=(new \app\model\config)->lists_all('admin');
        return $data; 
	}

	/*所有插件开放状态 redis */
    public function plugin_open_all(){
        $plugin_M = new \app\model\plugin();
		$plugin = $plugin_M->open_status();
		$plugin = array_column($plugin, NULL, 'iden');
        return $plugin;
    }
	
	public function index()
	{		
		(new AdminValidate())->goCheck('scene_local_login');
		$username=post("username");
		$password=post("password");
		
		$adminM = new AdminModel();   
        $password = rsa_decrypt($password);

        $password = md5($password.'inex10086');

        $auth = $adminM->check_user($username,$password);       
        empty($auth['id']) && error("登录失败",400);

        
        $token=new token();             
        $res = $token->addtoken($auth); 
       	$data['last_ip'] = ip();
       	$data['last_login'] = time();
		$adminM->up($auth['id'],$data);
		
		$redis = new \core\lib\redis();
		$code = strtolower(post('code'));
		$unicode = post('unicode');
		$redis_code = $redis->get("verification:".$unicode);
		if($code != $redis_code){
			error('验证码错误',400);
		}

		$GLOBALS['admin']=$auth;
        admin_log('后台密码登录',$auth['id']);      	
        return $res;
	}

	public function sendcode(){
		$tel = post("tel");
		$msms_C = new \app\service\msms();
		$res = $msms_C->send($tel);
		if($res['status']==0){
            error($res['info'],404);
        }
        return $res['info'];
	}	

	//短信验证码登录
	public function phonelogin(){
		$tel = post("tel");
		$code = post("code");
		$unicode = post("unicode");

		$redis = new \core\lib\redis();

		$vue_value = $code."@".$unicode;
		$redis_value = $redis->get("sms:".$tel);

		if($vue_value != $redis_value){
			error("验证码错误",400);
		}

		$adminM = new AdminModel(); 

		$auth = $adminM->find_by_tel($tel);
		empty($auth['id']) && error("登录失败~",400);

		$token=new token();             
        $res = $token->addtoken($auth); 

       	$data['last_ip'] = ip();
       	$data['last_login'] = time();
		$adminM->up($auth['id'],$data);
		
		$GLOBALS['admin'] = $auth;
        admin_log('后台短信登录',$auth['id']);      	
        return $res;
	} 


	/*退出登录*/
	public function logout(){
		set_cookie("token","");
		return '退出成功';	
	}




}