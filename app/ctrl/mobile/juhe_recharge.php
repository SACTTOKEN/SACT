<?php

/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-08-09 23:11:49
 * Desc: 话费/流量/油卡 VUE
 */

namespace app\ctrl\mobile;

use app\validate\IDMustBeRequire;
use app\model\juhe_recharge as JuheRechargeModel;
use app\validate\JuHeRechargeValidate;

class juhe_recharge extends BaseController
{

    public $juhe_M;
    public function __initialize(){
        $this->juhe_M = new JuheRechargeModel();

        $is_allow = plugin_is_open('hfynyk');
        $is_open  = c('juhe_open');
        if(!($is_allow && $is_open)){
            error('暂未开放',400); //是否开放
        }
    }

    
    //初始化 充值面额
    public function init(){
        $types = post('types');
        $tel = post('tel');
        $fee = c('juhe_fee');
        switch ($types) {
            case '话费':
                $hf_1 =50*$fee/1000;
                $hf_2 =100*$fee/1000;
                $hf_3 =50*$fee/1000;
                $ar[] = ['inprice'=>50,'money'=>$hf_1];
                $ar[] = ['inprice'=>100,'money'=>$hf_2];
                $ar[] = ['inprice'=>200,'money'=>$hf_3];
                break;
            case '流量':
                $ar_0 = $this->liuliang_query($tel);
                $ar_1 = $ar_0['flows'];        
                $ar = [];
                foreach($ar_1 as $one){
                    $one['money'] = $one['inprice']*$fee/1000;
                    $ar[] = $one;
                }
                break;
            case '油卡':
                $yk_1 =100*$fee/1000;
                $yk_2 =200*$fee/1000;
                $yk_3 =500*$fee/1000;
                $yk_4 =1000*$fee/1000;
                $ar[] = ['card_id'=>'10001','inprice'=>100,'money'=>$yk_1];
                $ar[] = ['card_id'=>'10002','inprice'=>200,'money'=>$yk_2];
                $ar[] = ['card_id'=>'10003','inprice'=>500,'money'=>$yk_3];
                $ar[] = ['card_id'=>'10004','inprice'=>1000,'money'=>$yk_4];
                break;    
        }
        return $ar;
    }


    //生成订单
    public function saveadd(){
        //(new JuHeRechargeValidate())->goCheck('scene_save');      
        $types = post('types');
        $game_userid = post('tel'); //充值对象  手机号/加油卡卡号
        $money = post('money'); //实付

        $game_money = post('game_money'); //话费面值/油卡面值 如100，200
        $card_id = post('card_id');  //流量套餐ID/油卡ID
        $card_id = $card_id ? $card_id : 0; 

        $uid = $GLOBALS['user']['id'];
        $data['card_id'] = $card_id;
        $data['uid'] = $uid;
        $data['money'] = $money;
        $data['types'] = $types;
        $data['game_money'] = $game_money;
        $data['game_userid'] = $game_userid;
        $ar = $this->juhe_M->save_by_oid($data);
        empty($ar) && error('生成订单失败',400);
        return $ar['id'];
    }



    //流量套餐
    public function liuliang_query($tel){
        (new JuHeRechargeValidate())->goCheck('scene_query');
        $ll_S = new \app\service\juhe_liuliang();
        $res = $ll_S ->telcheck($tel);
        if($res['error_code']==0){
            return $res['result'][0];
        }else{
            return false;
        }
    }


    //支付列表
    public function lists()
    {
       (new JuHeRechargeValidate())->goCheck('scene_lists');
        $pay_type = post('pay_type');
        $where['uid'] = $GLOBALS['user']['id'];
        $id = post('id');
        $where['id'] = $id;
        $order_ar = $this->juhe_M->have($where);
        if (empty($order_ar)) {
            return ['info' => '订单不存在'];
        }      
        if ($order_ar['is_pay'] == 1) {
            return ['info' => '订单已支付'];
        }

        $data['is_pay'] = 0;
        $data['money'] = $order_ar['money'];
        $data['time'] = time();

        $pay_S = new \app\service\pay();
        $data['pay'] =$pay_S->types($pay_type);

        return $data;
    }


    //提交支付
    public function pay()
    {
        (new IDMustBeRequire())->goCheck();
        (new \app\validate\PayValidate())->goCheck('pay');
        $pay_M = new \app\model\pay();

        $id = post('id');
        $or_where['id'] = $id;
        $or_where['is_pay'] = 0;
        $or_where['uid'] = $GLOBALS['user']['id'];
        $order_ar = $this->juhe_M->have($or_where);
        empty($order_ar) && error('订单不存在', 404);
    
        $pay_id = post('pay_id');
        $where['id'] = $pay_id;
        $where['show'] = 1;
        $pay_ar = $pay_M->have($where);
        empty($pay_ar) && error('支付方式已关闭', 404);

        $order['id'] = $order_ar['id'];
        $order['oid'] = $order_ar['oid'];
        $order['money'] = $order_ar['money'];
        $order['subject'] = "订单号为：" . $order_ar["oid"] . "的支付";

        //flash_god($GLOBALS['user']['id']);
        $pay_S = new \app\service\pay_recharge();
        $ar=$pay_S->index($pay_ar['iden'],$order_ar['oid']);  //iden : wechat,alipay,money,wechat_app,alipay_app
        return $ar;
    }


    //充值记录
    public function logs(){       
        $uid = $GLOBALS['user']['id'];
        $user = user_info($uid);
        $where['uid'] = $uid;
        $page=post("page",1);
        $page_size = post('page_size',10);
        $data  = $this->juhe_M->lists($page,$page_size,$where);
        $count = $this->juhe_M->new_count($where);
        foreach($data as &$one){         
            $one['avatar'] = $user['avatar'];
            $one['username'] = $user['username'];
            $one['nickname'] = $user['nickname'];
        }
        return $data;
    }






}
