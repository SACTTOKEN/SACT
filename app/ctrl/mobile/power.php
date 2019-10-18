<?php

/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-08 10:29:09
 * Desc: 权限控
 */

namespace app\ctrl\mobile;

use app\validate\UserValidate;
use app\service\token;
use app\model\user as UserModel;

class power extends PublicController
{

    public $userM;
    public function __initialize()
    {
        $this->userM  = new UserModel();
    }

    //小程序登录
    public function applets()
    {
        $code = post('code');
        $tshare = post('tshare', ''); //分享参数
        $applets_S = new \app\service\applets();
        $openid = $applets_S->code2Session($code);
        empty($openid) && error('登陆错误', 404);
        return $this->check_openid($openid, $tshare);
    }


    /*读取某类banner*/
    public function find_by_cate()
    {
        $data = (new \app\model\banner())->list_cate('vue_login_bg');
        foreach ($data as $one) {
            $ar['piclink'] = $one['piclink'];
        }
        $ar['title'] = c('head');
        return $ar;
    }

    /*微信登录step0:入口*/
    public function weixin_login()
    {
        $final_link  = post('final_link', '');
        //$final_link  = "http://ad.cn1218.com/cloud/clouddetails?tid=22&tshare=9&id=685&VNK=9f382947";

        $my_len = strpos($final_link, 'cloud/clouddetails');
        if ($my_len > 0) {
            $ar = explode('tid=', $final_link);
            $len = strpos($ar[1], '&');
            $my_tid = substr($ar[1], 0, $len);
            $user_M = new \app\model\user();
            $is_have = $user_M->find($my_tid);
            if (!$is_have) {
                $my_len2 = strpos($final_link, 'tshare=');
                if ($my_len2 > 0) {
                    $ar2 = explode('tshare=', $final_link);
                    $len2 = strpos($ar2[1], '&');
                    $my_tshare = substr($ar2[1], 0, $len2);
                    $final_link = str_replace('tid=' . $my_tid, 'tid=' . $my_tshare, $final_link);
                    $final_link = str_replace('&tshare=' . $my_tshare, '', $final_link);
                } else {
                    $final_link = str_replace('tid=' . $my_tid, '', $final_link);
                }
            }
        }

        //error($final_link,404);

        set_cookie("final_link", $final_link, time() + 60);

        $tshare = post('tshare', ''); //分享参数
        $is_open = c('open_wx_login');
        if (empty($is_open)) {
            $err['info'] = '';
            $err['url'] = '/login?tshare='.$tshare.'&url='.$final_link;
            error($err, 10008);
        }
        $wechatObj = new \app\service\wechat();
        $url = $wechatObj->wechat_code($tshare); //step1
     
        return $url;
    }

    /*微信登录step4:根据openid判断是否存在微信用户*/
    public function check_openid($openid, $tshare = '')
    {
        empty($openid) && error('openid丢失', 400);
        $userM = new \app\model\user();
        $uid = $userM->find_by_openid($openid['openid']);

        if (empty($uid)) {
            $new_uid = $this->wx_reg($openid, $tshare); //不存在则 直接微信注册并登录
            if ($new_uid) {
                return $this->wx_login($new_uid);
            } else {
                return false;
            }
        } else {
            return $this->wx_login($uid,$openid);
        }
    }

    /*微信登录step5:生成token*/
    public function wx_login($uid,$openid=[])
    {
        //登录统一执行
        $users = new \app\service\user();
        $users->logins_run($uid);

        $token = new \app\service\token();
        $res = $token->usertoken($uid);

        //更新登录IP和地址
        $ip = ip();
        $data['last_ip'] = $ip;
        $data['login_time'] = time();
        if(EXTRA=='applets'){
            if(isset($openid['session_key'])){
              $data['session_key'] = $openid['session_key'];
            }
          }
        $this->userM->up($uid, $data);
        set_cookie("user", '{"uid":"' . $uid . '","user_key":"' . $res['user_key'] . '"}');
        return $res;
    }

    /*微信注册*/
    public function wx_reg($openid, $tshare = '')
    {
        $user_ar = $this->userM->have(['openid' => $openid['openid']]);
        if ($user_ar) {
            if (empty($user_ar['tid'])) {
                if ($tshare) {
                    $t_res = $this->userM->is_have(['tid' => $user_ar['id']]);
                    if ($t_res) {
                        return;
                    }
                    $o_res = (new \app\model\order())->is_have(['uid' => $user_ar['id'], 'status[!]' => '已关闭']);
                    if ($o_res) {
                        return;
                    }
                    $tid = $this->userM->find_by_share($tshare);
                    if (empty($tid)) {
                        return;
                    }

                    if ($tid == $user_ar['id']) {
                        return;
                    }

                    $data['tid'] = $tid;
                } else {
                    return;
                }
                $new_uid = $this->userM->up($user_ar['id'], $data); //$user_ar  新用户
                if (empty($new_uid)) {
                    return;
                }
                mb_sms('recommend_user_reg', $user_ar['id']); //给推荐人发模板消息
                $users = new \app\service\user();
                $users->reg_run($user_ar['id']);
            }
            return;
        }
        if(EXTRA=='applets'){
            $userinfo['nickname']=post('nickname');
            $userinfo['headimgurl']=post('headimgurl');
            $userinfo['openid']=$openid['openid'];
            //$userinfo['session_key']=$openid['session_key'];
        }else{
            $wechatObj = new \app\service\wechat();
            $userinfo = $wechatObj->wechat_userinfo($openid['openid']);
            //$userinfo['session_key']='';
        }
        if (!empty($userinfo['openid'])) {
            if ($tshare) {
                $tid = $this->userM->find_by_share($tshare);
            }
            $tid = $tid ? $tid : 0;
            $data['username'] = $this->userM->get_sharecode();
            $data['nickname'] = $userinfo['nickname'];
            $data['avatar']   = $userinfo['headimgurl'];
            $data['openid']   = $userinfo['openid'];
            $data['province']   = $userinfo['province'];
            $data['city']   = $userinfo['city'];
            $data['password'] = md5($data['openid'] . 'inex10086');
            $data['tid']      = $tid;
            $new_uid = $this->userM->save($data);    //生成新用户   
            $this->queue('user_reg', $new_uid, $tid); //写入redis消息队列   __FUNCTION__
            //im注册
            if (plugin_is_open("imhyjsnt")) {
                $im = new \app\service\im();
                $im->login_one($new_uid, $data['username'], $data['nickname'], $data['avatar']);
            }

            //注册统一执行
            if ($data['tid']) {
                $users = new \app\service\user();
                $users->reg_run($new_uid);
            }
            return $new_uid;
        }
    }

    /*用户注册*/
    public function user_reg()
    {
        (new UserValidate())->goCheck('scene_add');
        $tel = post("username");        //手机号
        $password = post("password");
        $tshare  = post('tshare');    //就是账号,无推荐码字段
        $tid = 0;

        if ($tshare) {
            $tid = $this->userM->find_by_share($tshare);
            empty($tid) && error('推荐人不存在', 400);
            $data['tid'] = $tid;
        }

        if (c('reg_permission') == 1) {
            empty($tid) && error('推荐人必须', 400);
        }

        if ($tel) {
            $is_have = $this->userM->find_by_tel($tel);
            !empty($is_have) && error('手机号已被注册', 400);
        }

        //手机短信验证BEGIN
        $is_yzm = plugin_is_open('btdx');
        $is_gjyzm = plugin_is_open('gjdx');
        if (($is_yzm == 1 || $is_gjyzm == 1) && c('zcdxyz') == 1) {
            $unicode = post("unicode");
            $tel  = post('username', '');
            $code = post('code', '');
            $vue_code = $code . "@" . $unicode;
            empty($tel) && error('手机号错误', 400);
            $redis = new \core\lib\redis();
            $redis_code = $redis->get("sms:" . $tel);
            if ($vue_code != $redis_code) { //$code."@".uniqid();
                error('验证码错误！', 400);
            }
            $redis->set("sms:" . $tel, '');
        }
        //手机短信验证END

        $quhao = post('quhao', 86);
        $password = rsa_decrypt($password);
        empty($password) && error('密码非法', 400);
        $password = md5($password . 'inex10086');
        $data['username'] = $this->userM->get_sharecode(); //生成六位英文与数字的随机推广码,是求唯一，英文不能有o
        $data['password'] = $password;
        $data['tel'] = $tel;
        $data['quhao'] = $quhao;
        $data['reg_ip'] = ip();

        $new_uid = $this->userM->save($data);
        $this->queue('user_reg', $new_uid, $tid);   //发消息


        //im注册
        if (plugin_is_open("imhyjsnt")) {
            $im = new \app\service\im();
            $im->login_one($new_uid, $data['username']);
        }

        //注册统一执行
        if ($data['tid']) {
            $users = new \app\service\user();
            $users->reg_run($new_uid);
        }

        //注册完直接登录
        $token = new \app\service\token();
        $res = $token->usertoken($new_uid);

        return $res;
    }

    /* 手机号或用户名 都不存在返回true */
    public function is_have_user()
    {
        $username = post("username");
        $tid = $this->userM->find_by_username($username);
        !empty($tid) && error('用户已存在', 400);
        return true;
    }

    /*用户登录*/
    public function user_login()
    {
        (new UserValidate())->goCheck('scene_login');
        $username = post("username");
        $password = post("password");
        $quhao = post("quhao");
        $password = rsa_decrypt($password);
        $password = md5($password . 'inex10086');
        $auth = $this->userM->check_user($username, $password);

        empty($auth['id']) && error("用户名或密码错误", 400);
        if ($auth['show'] != 1) {
            error("您的账号已冻结!", 404);
        }
        $is_gjyzm = plugin_is_open('gjdx');
        if ($is_gjyzm == 1) {
            if ($quhao != $auth["quhao"]) {
                error("区号选择错误!", 404);
            }
        }
        //登录统一执行
        $users = new \app\service\user();
        $users->logins_run($auth['id']);

        $token = new token();
        $res = $token->usertoken($auth['id']);

        //更新登录IP和地址
        $ip = ip();
        $data['last_ip'] = $ip;
        $data['login_time'] = time();
        $this->userM->up($auth['id'], $data);
        return $res;
    }


    /*消息队列*/
    public function queue($action, $uid = 0, $tid = 0)
    {
        $redis = new \core\lib\redis();
        $params_ar['tid'] = $tid;
        $params_ar['xid'] = $uid;
        $params_ar['action'] = $action;
        $params_ar['desc'] = '新用户注册';
        $params = json_encode($params_ar, JSON_UNESCAPED_UNICODE);
        $redis->lpush('mbxx', $params);
    }


    /*绑定手机号 已废弃*/
    public function bind_mobile()
    {
        return;
        (new UserValidate())->goCheck('scene_bindmobile');
        $username = post('username', '');
        $id = post('id', 0);
        $code = post('code', '');
        $data['tel'] = post('tel');
        $is_yzm = plugin_is_open('btdx');
        if ($is_yzm == 1) {
            //验证code BEGIN
            $redis = new \core\lib\redis();
            $redis_code = $redis->get("sms:" . $data['tel']);
            if ($code != $redis_code) {
                error('验证码错误！', 400);
            }
            //验证code END
        }
        $rs = $this->userM->find($id);
        if (empty($rs['username'])) {
            $data['username'] = $username;
            $res = $this->userM->up($id, $data);
        }
        empty($res) && error('绑定失败', 404);
        return $res;
    }

    /*忘记密码*/
    public function change_password()
    {
        $new_password = post("password");
        $new_password = rsa_decrypt($new_password);
        $new_password = md5($new_password . 'inex10086');
        $tel  = post('username', '');

        $btdx = plugin_is_open('btdx'); //是否开放短信
        $gjdx = plugin_is_open('gjdx'); //是否开放国际短信
        if (!($btdx == 1 || $gjdx == 1)) {
            error('短信未开放', 400);
        }
        if (c('zhmmkg') != 1) {
            error('短信未开放', 400);
        }

        //验证手机begin
        empty($tel) && error('手机号错误1', 400);
        $where['tel'] = $tel;
        $uid = $this->userM->have($where, 'id');
        empty($uid) && error('手机号错误' . $uid, 400);

        //验证code BEGIN
        $unicode = post("unicode");
        $code = post('code', '');
        $vue_code = $code . "@" . $unicode;
        $redis = new \core\lib\redis();
        $redis_code = $redis->get("sms:" . $tel);
        if ($vue_code != $redis_code) {  //$code."@".uniqid();
            error('验证码错误！', 400);
        }
        $redis->set("sms:" . $tel, '');
        //验证code END
        //验证手机end    
        $res = $this->userM->change_password($uid, $new_password);

        empty($res) && error('修改失败', 404);
        return $res;
    }

    /*发送手机验证码*/
    public function sendcode()
    {
        $tel = post("tel");
        $quhao = post("quhao");
        $quhao = $quhao ? $quhao : '86';
        $msms_C = new \app\service\msms();
        $res = $msms_C->send($tel, $quhao);
        if ($res['status'] == 0) {
            error($res['info'], 404);
        }
        return $res['info'];
    }


    /*查配置值*/
    public function find_iden()
    {
        $iden = post('iden');
        $res = c($iden);
        return $res;
    }

    /*所有插件开放状态 redis */
    public function plugin_open_all()
    {
        $plugin_M = new \app\model\plugin();
        $plugin = $plugin_M->open_status();
        $plugin = array_column($plugin, null, 'iden');
        $is_yzm = plugin_is_open('btdx');
        $is_gjyzm = plugin_is_open('gjdx');

        if ($is_yzm == 1 || $is_gjyzm == 1) {
            $arr['ht_dxdl'] = c('ht_dxdl');
            $arr['zhmmkg'] = c('zhmmkg');
            $arr['zfmmdxyz'] = c('zfmmdxyz');
            $arr['xgmmdxyz'] = c('xgmmdxyz');
            $arr['bdsjhm'] = c('bdsjhm');
            $arr['zcdxyz'] = c('zcdxyz');
        } else {
            $arr['ht_dxdl']  = 0;
            $arr['zhmmkg']   = 0;
            $arr['zfmmdxyz'] = 0;
            $arr['xgmmdxyz'] = 0;
            $arr['bdsjhm']   = 0;
            $arr['zcdxyz']   = 0;
        }

        $shop['head'] = c('head');
        $shop['logo'] = c('logo');
        $shop['app_down_ewm'] = c('app_down_ewm');
        $shop['mall_tel'] = c('mall_tel');
        $shop['reg_permission'] = c('reg_permission');
        $shop['coin_title'] = c('coin_title');
        $shop['coin_storage_title'] = c('coin_storage_title');
        $shop['viprd_ptb_title'] = c('viprd_ptb_title');
        $shop['viprd_usdt_title'] = c('viprd_usdt_title');
        $shop['dapp_kqcs'] = c('dapp_kqcs');  
        $shop['agent_name'] = c('agent_name');
        if (plugin_is_open('appbbxt') && !empty(c('appKey')) && !empty(c('master'))) {
            $shop['is_aurora'] = 1;
        }
        if (plugin_is_open('wxgzhbb') && c('open_wx_login') == 1) {
            $shop['is_wx_login'] = 1;
        }

        $data['sms']    = $arr;
        $data['plugin'] = $plugin;
        $data['shop'] = $shop;
        $data['footer'] = (new \app\model\banner())->list_cate('footer');
        return $data;
    }



    /*用户协议*/
    public function contact()
    {
        $news_M = new \app\model\news();
        $res = $news_M->find('1');
        if (empty($res)) {
            $res = [];
        }
        return $res;
    }


    
    //imtoken登录
    public function imtoken_login()
    {
        if(!plugin_is_open('dapp')){
            error('插件未开启',404);
        }
        (new UserValidate())->goCheck('imtoken_login');
        $imtoken = post("imtoken");
        $auth = $this->userM->have(['imtoken'=>$imtoken],['is_supplier','tid','id','username','nickname','show','im','quhao']);
        if(empty($auth['id'])){
            return false;
        }
        if ($auth['show'] != 1) {
            error("您的账号已冻结!", 404);
        }
        //登录统一执行
        $users = new \app\service\user();
        $users->logins_run($auth['id']);
        
        $token = new token();
        $res = $token->usertoken($auth['id']);

        //更新登录IP和地址
        $ip = ip();
        $data['last_ip'] = $ip;
        $data['login_time'] = time();
        $this->userM->up($auth['id'], $data);
        return $res;
    }

    //imtoken注册
    public function imtoken_reg()
    {
        if(!plugin_is_open('dapp')){
            error('插件未开启',404);
        }
        (new UserValidate())->goCheck('imtoken_login');
        $imtoken = post("imtoken");
        $tshare  = post('tshare');    //就是账号,无推荐码字段
        $tid = 0;

        $auth = $this->userM->is_have(['imtoken'=>$imtoken]);
        if($auth){
            error('用户已存在', 400);
        }

        if ($tshare) {
            $tid = $this->userM->find_by_share($tshare);
            empty($tid) && error('推荐人不存在', 400);
            $data['tid'] = $tid;
        }

        if (c('reg_permission') == 1) {
            empty($tid) && error('推荐人必须', 400);
        }

        $quhao = post('quhao', 86);
        $data['username'] = $this->userM->get_sharecode(); //生成六位英文与数字的随机推广码,是求唯一，英文不能有o
        $data['password'] = md5($imtoken . 'inex10086');;
        $data['imtoken'] = $imtoken;
        $data['quhao'] = $quhao;
        $data['reg_ip'] = ip();

        $new_uid = $this->userM->save($data);
        $this->queue('user_reg', $new_uid, $tid);   //发消息

        //im注册
        if (plugin_is_open("imhyjsnt")) {
            $im = new \app\service\im();
            $im->login_one($new_uid, $data['username']);
        }

        //注册统一执行
        if ($data['tid']) {
            $users = new \app\service\user();
            $users->reg_run($new_uid);
        }

        //注册完直接登录
        $token = new \app\service\token();
        $res = $token->usertoken($new_uid);

        return $res;
    }
}
