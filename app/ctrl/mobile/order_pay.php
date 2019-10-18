<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-14 17:25:08
 * Desc: 订单
 */
namespace app\ctrl\mobile;

use app\model\order as OrderModel;
use app\validate\IDMustBeRequire;


class order_pay extends PublicController
{
    public $orderM;
    public $pay_M;
    public function __initialize()
    {
        $this->orderM = new OrderModel();
        $this->pay_M = new \app\model\pay();
    }


    //支付列表
    public function lists()
    {
        (new IDMustBeRequire())->goCheck();
        $id = post('id');
        $pay_type = post('pay_type');
        $or_where['id'] = $id;
        $or_where['uid'] = $GLOBALS['user']['id'];
        $order_ar = $this->orderM->have($or_where);
        if (empty($order_ar)) {
            return ['info' => '订单不存在'];
        }
        if($order_ar['types']==2 && $order_ar['money']==0){
            $redis = new \core\lib\redis();
            $Model = new \core\lib\Model();
            $Model->action();
            $redis->multi();
            $data_ar['is_pay']=1;
            $data_ar['status']='已支付';
            $data_ar['pay_time']=time();
            $data_ar['pay']='积分兑换';
            $this->orderM->up($order_ar['id'],$data_ar);
            $res=(new \app\service\order())->split($order_ar['id']);
            if(!$res){
                error('拆单错误',404);
            }
            $Model->run();
            $redis->exec();
            $err['info']='';
            $err['url']='/order/paydetails?id='.$order_ar['id'];
            error($err,10008);            
        }
        if ($order_ar['is_pay'] == 1) {
            return ['info' => '订单已支付'];
        }
        $time = $order_ar['created_time'] + c("zdscddsj") * 60 - time();
        if ($order_ar['status'] == '已关闭' || $time <= 0) {
            return ['info' => '订单过期未支付，已关闭'];
        }
        $data['is_pay'] = 0;
        $data['money'] = $order_ar['money'];
        $data['time'] = $time;
        $pay_S = new \app\service\pay();
        $data['pay'] =$pay_S->types($pay_type);

        return $data;
    }

    //提交支付
    public function pay()
    {
        (new IDMustBeRequire())->goCheck();
        (new \app\validate\PayValidate())->goCheck('pay');
        $id = post('id');
        $or_where['id'] = $id;
        $or_where['is_pay'] = 0;
        $or_where['status'] = '未支付';
        $or_where['uid'] = $GLOBALS['user']['id'];
        $order_ar = $this->orderM->have($or_where);
        empty($order_ar) && error('订单不存在', 404);
        $time = $order_ar['created_time'] + c("zdscddsj") * 60 - time();
        if ($time <= 0) {
            error('订单过期未支付，已关闭', 404);
        }
        $pay_id = post('pay_id');
        $where['id'] = $pay_id;
        $where['show'] = 1;
        $pay_ar = $this->pay_M->have($where);
        empty($pay_ar) && error('支付方式已关闭', 404);

        $order['id'] = $order_ar['id'];
        $order['oid'] = $order_ar['oid'];
        $order['money'] = $order_ar['money'];
        $order['subject'] = "订单号为：" . $order_ar["oid"] . "的支付";

        //flash_god($GLOBALS['user']['id']);
        $pay_S = new \app\service\pay();
        $ar=$pay_S->index($pay_ar['iden'],$order_ar['oid']);
        return $ar;
    }

    //支付成功
    public function success()
    {
        (new IDMustBeRequire())->goCheck();
        $id = post('id');
        $or_where['id'] = $id;
        $or_where['uid'] = $GLOBALS['user']['id'];
        $order_ar = $this->orderM->have($or_where);

        $my_oid = $order_ar['oid'];
        //cs($this->orderM->log(),1);
        if (empty($order_ar)) {
            return ['info' => '订单不存在'];
        }
        $where['oid[~]'] = $order_ar['oid'];
        $order_ar = $this->orderM->lists_all($where);
        $data['oid'] = '';
        $data['money'] = 0;
        $data['send_score'] = 0;
        foreach ($order_ar as $vo) {
            $data['oid'] = $data['oid'] . $vo['oid'] . ',';
            $data['money'] = $data['money'] + $vo['money'];
            $data['send_score'] = $data['send_score'] + $vo['send_score'];
            $data['is_pay'] = $vo['is_pay'];
            $data['pay_time'] = $vo['pay_time'];
        }
        $data['oid'] = rtrim($data['oid'], ",");
        if ($data['send_score'] > 0){
            $data['send_score'] = '确认完成订单赠送' . $data['send_score'] . find_reward_redis('integral');
        }


        if(c('is_wcycgwty')==1){
        $new_duty_S = new \app\service\new_duty();
        $new_duty_S->paid_reward($GLOBALS['user']['id'],'wcycgwty'); //新手任务-完成一次购物体验        
        }

        if(plugin_is_open('xfhb')==1){
        //消费红包发放BEGIN 无需领到coupon中，满足条件直接奖励
            $uid = $GLOBALS['user']['id'];
            $where_c['oid'] =  $my_oid;
            $where_c['uid'] = $uid;
            $coupon_M = new \app\model\coupon();
            $is_have_coupon = $coupon_M->is_have($where_c); //防反复领取
            if(!$is_have_coupon){
                $coupon_S = new \app\service\coupon();
                $new_ar = $coupon_S -> packet_xf_pj($uid,'xf',$my_oid); 
            }
        //消费红包发放END
        }
                      
        if($new_ar){
            $data['coupon'] = $new_ar; //在notify_url里奖了消费红包，在这里显示给前端 
        }

        return $data;
    }

    //支付成功推荐商品
    public function product()
    {
        $where['is_check'] = 1;
        $where['show'] = 1;
        $data['product'] = (new \app\model\product)->lists_tj($where);
        return $data;
    }
}
