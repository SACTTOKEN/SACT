<?php
/**
 * Created by yaaaaa_god
 * User: god
 * Date: 2018/12/13
 * Desc: 控制器测试公用
 */
namespace app\ctrl\admin;
use core\lib\redis;
class PublicController extends \core\imooc{
	
	function __construct(){
		$redis = new redis();
		$uid = find_server('HTTP_HEADUID');	
		$redis_name = 'admin:'.$uid;
		$GLOBALS['admin'] = $redis->hget($redis_name,'info');
	}

}