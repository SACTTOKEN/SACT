<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-15 17:02:46
 * Desc: 会员中心控
 */
namespace app\ctrl\mobile;
use app\model\user as UserModel;

class user extends BaseController
{
	public $user_M;
	
	public function __initialize(){
		$this->user_M = new UserModel();
    }
    
    public function logout()
    {
		$uid=$GLOBALS['user']['id'];
		$redis_name = 'user:'.$uid;
        (new \core\lib\redis())->hset($redis_name,'user_token','');
        (new \core\lib\redis())->hset($redis_name,'user_key','');
		set_cookie("user","",0);
		set_cookie("user_token","",0);
		return '退出成功';	
    }
}