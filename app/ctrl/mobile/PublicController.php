<?php
/**
 * Created by yaaaaa_god
 * User: god
 * Date: 2018/12/13
 * Desc: 控制器测试公用
 */
namespace app\ctrl\mobile;
use core\lib\redis;
class PublicController extends \core\imooc{	
	function __construct(){
		if(c('wzkg')==0){
            $err['info']='';
            $err['url']=c('gztp');
            error($err,10008);	
		}
		$redis = new redis();
		$uid = find_server('HTTP_UID');	
		//renew_user($uid);
		$redis_name = 'user:'.$uid;
		$GLOBALS['user'] = $redis->hget($redis_name);
	}
}