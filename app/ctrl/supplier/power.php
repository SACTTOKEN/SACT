<?php 
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-08 10:29:09
 * Desc: 权限控
 */
namespace app\ctrl\supplier;
use app\validate\UserValidate;
use app\service\token;
use app\model\user as UserModel; 
class power{

    public $userM;
    public function __initialize(){
        $this->userM  = new UserModel();
    }

    
    /* 手机号或用户名 都不存在返回true */ 
    public function is_have_user(){
        $username=post("username");
        $tid = $this->userM->find_by_username($username); 
        !empty($tid) && error('用户已存在',400);
        return true;
    }

    /*用户登录*/
    public function user_login(){
        (new UserValidate())->goCheck('scene_login');
        $username=post("username");
        $password=post("password");
        $quhao=post("quhao");
        $password = rsa_decrypt($password);
        $password = md5($password.'inex10086');
        $auth = $this->userM->check_user($username,$password);
       
        empty($auth['id']) && error("用户名或密码错误",400);
        if($auth['show']!=1){error("您的账号已冻结!",404);} 
        if($auth['is_supplier']!=1){error("不是供应商!",404);} 
        $is_gjyzm = plugin_is_open('gjdx');
        if($is_gjyzm==1){
        if($quhao!=$auth["quhao"]){error("区号选择错误!",404);}
        }
        //登录统一执行
        $users = new \app\service\user();
        $users -> logins_run($auth['id']);

        $token=new token();             
        $res = $token->supplier_token($auth['id']);
      
        //更新登录IP和地址
        $ip = ip();	           
        $data['last_ip'] = $ip;
        $data['login_time'] = time();
        $this->userM->up($auth['id'],$data);
        return $res;
    }   



    /*所有插件开放状态 redis */
    public function plugin_open_all(){
        $plugin_M = new \app\model\plugin();
        $plugin = $plugin_M->open_status();
        $plugin = array_column($plugin,null,'iden');
        $is_yzm = plugin_is_open('btdx');
        $is_gjyzm = plugin_is_open('gjdx');

        if($is_yzm==1 || $is_gjyzm==1){          
            $arr['ht_dxdl'] = c('ht_dxdl');
            $arr['zhmmkg'] = c('zhmmkg');
            $arr['zfmmdxyz'] = c('zfmmdxyz');
            $arr['xgmmdxyz'] = c('xgmmdxyz');
            $arr['bdsjhm'] = c('bdsjhm');
            $arr['zcdxyz'] = c('zcdxyz');
        }else{
            $arr['ht_dxdl']  = 0;
            $arr['zhmmkg']   = 0;
            $arr['zfmmdxyz'] = 0;
            $arr['xgmmdxyz'] = 0;
            $arr['bdsjhm']   = 0;
            $arr['zcdxyz']   = 0;
        }
        
        $shop['head']=c('head');
        $shop['logo']=c('logo');
        $shop['app_down_ewm']=c('app_down_ewm');

        $data['sms']    = $arr;
        $data['plugin'] = $plugin;
        $data['shop'] = $shop;
        $data['footer'] = (new \app\model\banner())->list_cate('footer');
        return $data;
    }


    /*用户协议*/
    public function contact(){
        $news_M = new \app\model\news();
        $res = $news_M->find('1');
        if(empty($res)){$res = [];}
        return $res;
    }


    public function logout()
    {
		$uid=$GLOBALS['user']['id'];
		$redis_name = 'user:'.$uid;
        (new \core\lib\redis())->hset($redis_name,'supplier_token','');
        (new \core\lib\redis())->hset($redis_name,'supplier_key','');
		set_cookie("supplier_key","",0);
		set_cookie("supplier_token","",0);
		return '退出成功';	
    }

}