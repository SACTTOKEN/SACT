<?php

/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-14 11:19:11
 * Desc: token相关类  哈希类型
 */

namespace app\service;

use core\lib\redis;

class token
{


    /*生成管理员token */
    function addtoken($data)
    {

        $admin_token = getRandChar(32);
        set_cookie("token", $admin_token);

        $redis = new redis();
        $rd_name = 'admin:' . $data['id'];
        $rd_key  = 'token';
        $redis->hset($rd_name, $rd_key, $admin_token);

        $rd_key  = 'info';
        $redis->hset($rd_name, $rd_key, $data);

        $admin_key = getRandChar(16);
        $redis->hset($rd_name, 'admin_key', $admin_key);

        $ar['key'] = $admin_key;
        $ar['uid'] = $data['id'];
        return $ar;
    }


    /*生成用户token*/
    function usertoken($uid)
    {
        //$extra等于小程序
        if(EXTRA=='applets'){
            return $this->appletstiken($uid);
        }
        renew_user($uid);
        $user_token = getRandChar(32);
        $user_key = getRandChar(16);
        set_cookie("user_token", $user_token);
        $userM = new \app\model\user();
        $data = $userM->find_me($uid);
        unset($data['password']);
        unset($data['pay_password']);
        $redis = new redis();
        $rd_name = 'user:' . $uid;
        $rd_key = 'user_token';
        $redis->hset($rd_name, $rd_key, $user_token);
        $data['id'] = $data['uid'];
        if ($data['rating']) {
            $rating_M = new \app\model\rating();
            $rating_cn = $rating_M->find($data['rating'], 'title');
            if ($rating_cn) {
                $data['rating_cn'] = $rating_cn;
            }
        }
        //是否有会员商品 商品类型types 0:普通商品 1:会员商品 2：积分兑换 3：砍价 4：拼团 5：众筹
        $product_M = new \app\model\product();
        $is_have_vip_goods = $product_M->is_have(['types' => 1]);
        $data['is_have_vip_goods'] = $is_have_vip_goods;
        //IM
        $im = new \app\service\im();
        if (plugin_is_open("imhyjsnt")) {
            if ($data['im'] == "") {
                $im->login_one($data['id'], $data['username'], $data['nickname'], $data['avatar']);
                $data['im'] = C("ptid") . "_" . $data['id'];
            }
        }
        if(!empty($data['im'])){
        $im_sig = $im->create_sig($data['im']);
        $data['im_sig'] = $im_sig;
        }else{
        $data['im_sig'] = '';
        }
        foreach ($data as $key => $rs) {
            $redis->hset($rd_name, $key, $rs);
        }
        $rd_key = 'user_key';
        $redis->hset($rd_name, $rd_key, $user_key);
        $data['user_key'] = $user_key;
        return $data;
    }

    /*生成用户token*/
    function supplier_token($uid)
    {
        renew_user($uid);
        $supplier_token = getRandChar(32);
        $supplier_key = getRandChar(16);
        set_cookie("supplier_token", $supplier_token);
        $userM = new \app\model\user();
        $data = $userM->find_me($uid);
        unset($data['password']);
        unset($data['pay_password']);
        $redis = new redis();
        $rd_name = 'user:' . $uid;
        $rd_key = 'supplier_token';
        $redis->hset($rd_name, $rd_key, $supplier_token);
        $data['id'] = $data['uid'];
        if ($data['rating']) {
            $rating_M = new \app\model\rating();
            $rating_cn = $rating_M->find($data['rating'], 'title');
            if ($rating_cn) {
                $data['rating_cn'] = $rating_cn;
            }
        }
        //是否有会员商品 商品类型types 0:普通商品 1:会员商品 2：积分兑换 3：砍价 4：拼团 5：众筹
        $product_M = new \app\model\product();
        $is_have_vip_goods = $product_M->is_have(['types' => 1]);
        $data['is_have_vip_goods'] = $is_have_vip_goods;
        //IM
         $im = new \app\service\im();
        if (plugin_is_open("imhyjsnt")) {
            if ($data['im'] == "") {
                $im->login_one($data['id'], $data['username'], $data['nickname'], $data['avatar']);
                $data['im'] = C("ptid") . "_" . $data['id'];
            }
        }
        if(!empty($data['im'])){
        $im_sig = $im->create_sig($data['im']);
        $data['im_sig'] = $im_sig;
        }else{
        $data['im_sig'] = '';
        }
        $data['im_sig'] = $im_sig;
        foreach ($data as $key => $rs) {
            $redis->hset($rd_name, $key, $rs);
        }
        $rd_key = 'supplier_key';
        $redis->hset($rd_name, $rd_key, $supplier_key);
        $data['supplier_key'] = $supplier_key;
        return $data;
    }

    public function appletstiken($uid)
    {
        renew_user($uid);
        $user_token = getRandChar(32);
        $user_key = getRandChar(16);
        $userM = new \app\model\user();
        $data = $userM->find_me($uid);
        unset($data['password']); 
        unset($data['pay_password']); 

        $redis = new redis();
        $rd_name = 'user:'.$uid;

        $data['id'] = $data['uid'];
        if($data['rating']){
            $rating_M = new \app\model\rating();
            $rating_cn= $rating_M->find($data['rating'],'title');
            if($rating_cn){
                $data['rating_cn'] = $rating_cn;
            }       
        }

        //IM
        if(plugin_is_open("imhyjsnt")){
            $im = new \app\service\im();  
            if($data['im']==""){
                $im->login_one($data['id'],$data['username'],$data['nickname'],$data['avatar']);
            }
            $im_sig = $im->create_sig($data['im']);
        }
        $data['im_sig']=$im_sig;
        
        $admin_M=new \app\model\admin();
        $data['service_ar']=$admin_M->find($data['service_id'],['id','tel','im','nick_name','tel']);
        foreach($data as $key=>$rs){
            $redis->hset($rd_name,$key,$rs);
        }
        
        $rd_key = 'applets_token';
        $redis->hset($rd_name,$rd_key,$user_token);
        $rd_key = 'applets_key';
        $redis->hset($rd_name,$rd_key,$user_key);  

        $data['applets_key'] = $user_key;
        $data['applets_token'] = $user_token;
        return $data; 
    }
    
}
