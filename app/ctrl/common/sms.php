<?php

/**
 * Created by yaaaaa_god
 * User: god
 * Date: 2019-01-03
 * Desc: 消息模板库(所有消息模板集合,放计划任务时刷，暂定一分钟一刷)
 */

namespace app\ctrl\common;

use app\service\sms as sms_S;

class sms extends sms_S
{

    /*调用DEMO*/
    public function demo($params = [])
    {
        if (empty($params['xid'])) {
            return;
        }
        $users = user_info($params['xid']);
        $my_name = $users['nickname'] ? $users['nickname'] : $users['username'];
        $ar['sms_id'] = 1;                                      //模板ID
        $ar['head_ar'] = [$my_name];                            //内容替换
        $ar['body_ar'] = ['发货提醒', date('Y-m-d H:i:s')];      //底部内容
        $ar['user'] = $users;                                   //用户
        $ar['url'] = '/huoyi/huoyiorder';                       //跳转链接
        $ar['piclink'] = $users['avatar'];                      //图片
        $this->notice($ar);
    }


    //注册新用户
    public function user_reg($params = [])
    {
        if (empty($params['xid'])) {
            return;
        }
        $users = user_info($params['xid']);
        if (empty($users)) {
            return;
        }
        $my_name = $users['nickname'] ? $users['nickname'] : $users['username'];
        $ar['sms_id'] ='注册成功';                                     
        $ar['head_ar'] = [$my_name];                            
        $ar['body_ar'] = [$my_name, date('Y-m-d H:i:s')];      
        $ar['user'] = $users;                                   
        $ar['url'] = '/';                       
        $ar['piclink'] = $users['avatar'];                      
        $this->notice($ar);
        $this->recommend_user_reg($params);
    }

    //推荐人发消息
    public function recommend_user_reg($params = [])
    {
        if (empty($params['xid'])) {
            return;
        }
        $users = user_info($params['xid']);
        if (empty($users)) {
            return;
        }
        $my_name = $users['nickname'] ? $users['nickname'] : $users['username'];
        if ($users['tid']) {
            $t_ar = user_info($users['tid']);
            $ar2['sms_id'] = '推荐注册';                                   
            $ar2['title'] = '推荐用户';                                 
            $ar2['head_ar'] = [$my_name];                            
            $ar2['body_ar'] = [$my_name, date('Y-m-d H:i:s')];      
            $ar2['user'] = $t_ar;                                   
            $ar2['url'] = '/im/imindex';                       
            $ar2['piclink'] = $users['avatar'];                      
            $this->notice($ar2);
        }
    }


    //支付成功
    public function pay_order($params)
    {
        if (empty($params['xid'])) {
            return;
        }
        $order_ar=$this->order_M->find($params['xid'],['oid','uid','money','is_pay']);
        if($order_ar && $order_ar['is_pay']==1){
            $product=$this->order_product_M->have(['oid'=>$order_ar['oid']],['piclink','title']);
            $users = user_info($order_ar['uid']);
            $my_name = $users['nickname'] ? $users['nickname'] : $users['username'];
            $ar['sms_id'] = '订单支付';
            $ar['head_ar'] = [$my_name,$order_ar['oid'],$order_ar['money'],$product['title']];
            $ar['body_ar'] = [$order_ar['oid'], date('Y-m-d H:i:s')];
            $ar['user'] = $users;
            $ar['url'] = '/order/orderlist';
            $ar['piclink'] = $product['piclink'];
            $this->notice($ar);
        }
        $this->supplier($params);
        $this->stock($params);
    }

    public function supplier($params)
    {
        if (empty($params['xid'])) {
            return;
        }
        $order_ar=$this->order_M->find($params['xid'],['sid','oid','uid','money','is_pay']);
        if($order_ar && $order_ar['sid']>0  && $order_ar['is_pay']==1){
            $product=$this->order_product_M->have(['oid'=>$order_ar['oid']],['piclink','title']);
            $supplier = user_info($order_ar['sid']);
            $users = user_info($order_ar['uid']);
            $my_name = $users['nickname'] ? $users['nickname'] : $users['username'];
            $ar['sms_id'] = '供应商发货提醒';
            $ar['head_ar'] = [$my_name,$order_ar['oid'],$order_ar['money'],$product['title']];
            $ar['body_ar'] = [$order_ar['oid'], date('Y-m-d H:i:s')];
            $ar['user'] = $supplier;
            $ar['url'] = '/supplier/supplierorder';
            $ar['piclink'] = $product['piclink'];
            $this->notice($ar);
        }
    }

    public function ship($params)
    {
        if (empty($params['xid'])) {
            return;
        }
        $order_ar=$this->order_M->have(['id'=>$params['xid'],'status'=>'已发货'],['oid','uid','money','mail_courier','mail_oid']);
        if($order_ar){
            $product=$this->order_product_M->have(['oid'=>$order_ar['oid']],['piclink','title']);
            $users = user_info($order_ar['uid']);
            $my_name = $users['nickname'] ? $users['nickname'] : $users['username'];
            $ar['sms_id'] = '订单发货';
            $ar['head_ar'] = [$my_name,$order_ar['oid'],$order_ar['money'],$product['title'],$order_ar['mail_courier'],$order_ar['mail_oid']];
            $ar['body_ar'] = [$order_ar['oid'], date('Y-m-d H:i:s')];
            $ar['user'] = $users;
            $ar['url'] = '/order/orderlist';
            $ar['piclink'] = $product['piclink'];
            $this->notice($ar);
        }
    }


    public function card($params)
    {
        if (empty($params['xid'])) {
            return;
        }
        $card_ar=(new \app\model\card())->have(['id'=>$params['xid'],'status'=>1]);
        if($card_ar){
            $product=$this->order_product_M->have(['oid'=>$card_ar['oid']],['piclink','title']);
            $users = user_info($card_ar['uid']);
            $my_name = $users['nickname'] ? $users['nickname'] : $users['username'];
            $ar['sms_id'] = '卡密通知';
            $ar['head_ar'] = [$my_name,$card_ar['oid'],$product['title'],$card_ar['key']];
            $ar['body_ar'] = [$card_ar['key'], date('Y-m-d H:i:s')];
            $ar['user'] = $users;
            $ar['url'] = '/order/orderlist';
            $ar['piclink'] = $product['piclink'];
            $this->notice($ar);
        }
    }



    //火蚁定制发货给代理商发消息
    public function stock($params)
    {
        if (empty($params['xid'])) {
            return;
        }
        $stock_ar = (new \made\show2\model\stock())->have(['oid' => $params['xid']]);
        if (empty($stock_ar)) {
            return;
        }
        $users = user_info($stock_ar['sid']);
        $my_name = $users['nickname'] ? $users['nickname'] : $users['username'];
        $ar['sms_id'] = '代理商发货通知';
        $ar['head_ar'] = [$my_name];
        $ar['body_ar'] = ['发货提醒', date('Y-m-d H:i:s')];
        $ar['user'] = $users;
        $ar['url'] = '/huoyi/huoyiorder';
        $ar['piclink'] = $users['avatar'];
        $this->notice($ar);
    }


    //云平台询价和留言给商户发消息
    public function to_sid($params){
        if (empty($params['xid'])) {
            return;
        }
        $users = user_info($params['xid']); //商户
        if (empty($users)) {
            return;
        }

        $tel = $params['tel'];
        $p_title = $params['p_title'];
        $my_name = $users['nickname'] ? $users['nickname'] : $users['username'];
        $ar['sms_id'] ='咨询';                                     
        $ar['head_ar'] = [$my_name,date('H:i'),$params['desc']];                            
        $ar['body_ar'] = [$p_title,$tel];      
        $ar['user'] = $users;                                   
        $ar['url'] =$params['url']; // /cloud/cloudmsglist   /cloud/cloudasklist
        $ar['piclink'] = $users['avatar'];         
        //cs($ar);             
        $this->notice($ar);
    }
  
}
