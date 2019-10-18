<?php

/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-14 11:38:07
 * Desc: zb接口
 */

namespace app\ctrl\common;

use app\model\c2c_order as c2c_order_Model;
use app\model\config as config_Model;
use app\model\user as user_Model;
use app\model\order as order_Model;

class tasks
{

    public $c2c_S;
    public $c2c_order_M;
    public $config_M;
    public $user_M;
    public $orderM;
    public $money_S;

    public function __construct()
    {
        $this->c2c_S = new \app\service\c2c();
        $this->c2c_order_M = new c2c_order_Model();
        $this->config_M = new config_Model();
        $this->user_M = new user_Model();
        $this->orderM = new order_Model();
        $this->money_S = new \app\service\money();


    }

    public function index()
    {
        $redis = new \core\lib\redis();
        $Model = new \core\lib\Model();
        $Model->action();
        $redis->multi();
        $this->c2c();   //c2c计划任务
        $Model->run();
        $redis->exec();


        $Model->action();
        $redis->multi();
        $this->coin_win();   //币定盈奖励 AICQ存储到期 转AICQ活动
        $Model->run();
        $redis->exec();

        $Model->action();
        $redis->multi();
        $this->red();   //红包发放过期退回
        $Model->run();
        $redis->exec();

        $Model->action();
        $redis->multi();
        $this->order();   //订单
        $Model->run();
        $redis->exec();


        $Model->action();
        $redis->multi();
        $this->groups();   //拼团
        $Model->run();
        $redis->exec();
    }

    public function groups()
    {
        //时间到未
        $groups_M=new \app\model\groups();
        $where['status']=0;
        $where['end_time[<=]']=time();
        $groups_ar=$groups_M->lists_all($where);
        if($groups_ar){
            foreach($groups_ar as $vo){
                $groups_M->up($vo['id'],['status'=>2]);
                $this->orderM->up($vo['oid'],['status'=>'已关闭']);
                if($vo['is_pay']==1){
                    $order_ar=$this->orderM->find($vo['oid'],['money','oid']);
                    $this->money_S->plus($vo['uid'],$order_ar['money'],'money','group_return',$order_ar['oid'],$vo['uid']);
                }
            }
        }
    }

    public function order()
    {
        $this->close_order(); //过期未支付关闭订单
        $this->confirms_order(); //时间到自动确认收货
        $this->pay_order(); //会员商品付款就返利
        $this->ship_order(); //普通商品付款就发货
        $this->virtual_order(); //虚拟商品自动完成
    }

    //普通商品付款就发货
    public function ship_order()
    {
        if(res_plugin_is_open('kdlwnjk')){
            $kdn_ship = $this->config_M->find('kdn_ship', 'value');
            if($kdn_ship){
                $where['is_pay'] = 1;
                $where['types'] = 0;
                $where['mail_type'] = 0;
                $where['is_return'] = 0;
                $where['is_virtual'] = 0;
                $order = $this->orderM->lists_all($where,'id');
                if ($order) {
                    $kdn_S = new \app\service\kdn();
                    foreach($order as $vo){
                        if($vo){
                            $data_up=$kdn_S->ship($vo);
                        }
                    }
                }
            }
        }
    }

    //会员商城付款就返利
    public function pay_order()
    {
        $fksfjfl = $this->config_M->find('fksfjfl', 'value');
        if($fksfjfl){
            $where['is_pay'] = 1;
            $where['is_settle'] = 0;
            $where['types'] = 1;
            $order = $this->orderM->lists_all($where,'id');
            if ($order) {
                foreach($order as $vo){
                    if($vo){
                        (new \app\service\order_complete())->complete($vo);
                    }
                }
            }
        }
    }

    //时间到自动确认收货
    public function confirms_order()
    {
        $mrddzdwc = $this->config_M->find('mrddzdwc', 'value');
        $where['status'] = '已发货';
        $where['is_pay'] = 1;
        $where['is_return'] = 0;
        $where['mail_time[<]'] = time() - $mrddzdwc * 60;
        $order = $this->orderM->lists_all($where,'id');
        if ($order) {
            foreach($order as $vo){
                if($vo){
                    $this->orderM->up($vo, ['status' => '已完成', 'complete_time' => time()]);
                    (new \app\service\order_complete())->complete($vo);
                }
            }
        }
    }

    //虚拟商品自动完成
    public function virtual_order()
    {
        $where['is_virtual'] = 1;
        $where['is_pay'] = 1;
        $where['is_return'] = 0;
        $order = $this->orderM->lists_all($where,'id');
        if ($order) {
            foreach($order as $vo){
                if($vo){
                    $this->orderM->up($vo, ['status' => '已完成', 'complete_time' => time()]);
                    (new \app\service\order_complete())->complete($vo);
                }
            }
        }
    }

    //过期未支付关闭订单
    public function close_order()
    {
        $zdscddsj = $this->config_M->find('zdscddsj', 'value');
        $where['is_pay'] = 0;
        $where['status[!]'] = '已关闭';
        $where['created_time[<]'] = time() - $zdscddsj * 60;
        $order_ar=$this->orderM->lists_all($where,['id','oid']);
        $order_product_M=new \app\model\order_product();
        $stock_S=new \app\service\stock();
        foreach($order_ar as $vo){
            $order_product_ar=$order_product_M->lists_all(['oid'=>$vo['oid']],['pid','sku_id','number']);
            $stock_S->plus_stock($order_product_ar);
            $this->orderM->up($vo['id'], ['status' => '已关闭']);
        }
    }

    public function red()
    {
        $transfer_M = new \app\model\transfer();
        $where['status'] = 0;
        $where['types'] = 1;
        $where['created_time[<]'] = time() - 24 * 3600;
        $transfer_ar = $transfer_M->have($where);
        if ($transfer_ar) {
            $data['status'] = 2;
            $transfer_M->up($transfer_ar['id'], $data);
            $idens_en = find_reward_redis($transfer_ar['cate']);
            $money_S = new \app\service\money();
            $money_S->plus($transfer_ar['uid'], $transfer_ar['money'] + $transfer_ar['fee'], $transfer_ar['cate'], 'transfer', $transfer_ar['oid'], $transfer_ar['other_id'], $idens_en . '红包过期退回');
        }
    }



    public function coin_win(){

        $money_S = new \app\service\money();
        $coin_win = new \app\model\coin_win();
        $where['is_end'] = 0;
        $where['stage[<=]'] = time();
        $ar = $coin_win->lists_all($where); 

        foreach($ar as $one){

            switch ($one['stage_type']) {

            case 'stage_0': 
                $win = c('coin_win_stage_0');  
                $cycle = 30;
                break;
            case 'stage_7': 
                $win = c('coin_win_stage_7');  
                $cycle = 7;
                break;
            case 'stage_30':
                $win = c('coin_win_stage_30');
                $cycle = 30;
            break;
            case 'stage_50':
                $win = c('coin_win_stage_50');
                $cycle = 50;
            break; 
            default:
                continu;
            break;        
            }

            $money = floatval($one['stake']) * floatval($win)/100;
            $money = $money + floatval($one['stake']);
            $uid = $one['uid'];
            $remark = '币定盈奖出';
            $money_S->plus($uid,$money,'coin','coin_win_out',$one['oid'],$uid,$remark);
            $money_S->minus($uid,$one['stake'],'coin_storage','coin_win_out',$one['oid'],$uid,$remark);
            $res = $coin_win->up($one['id'],['is_end'=>1]);   //结束
        }      
    }


    private function c2c()
    {
        $this->cancel_order(); //买家未付款自动取消订单扣诚信值
        $this->confirm_order(); //卖家未确认自动确认扣诚信值
        $this->del_buy(); //发布无人购买自动删除
    }

    private function cancel_order()
    {
        $where['status'] = 1;
        $where['state'] = 0;
        $c2c_payment_time = $this->config_M->find('c2c_payment_time', 'value');
        $where['created_time[<]'] = time() - $c2c_payment_time * 3600;
        $order_ar = $this->c2c_order_M->have($where);
        if ($order_ar) {
            $this->c2c_S->cancel($order_ar);
            $data['remark'] = '时间到自动取消订单';
            $this->c2c_order_M->up($order_ar['id'], $data);
            //判断金额
            $integrity = $this->user_M->find($order_ar['uid_buy'], 'integrity');
            if ($integrity > 0) {
                $money_S = new \app\service\money();
                $money_S->minus($order_ar['uid_buy'], 1, 'integrity', 'coin_c2c', $order_ar['oid_buy'], $order_ar['uid_sell'], '时间到自动取消订单'); //记录资金流水
            }
        }
    }


    private function confirm_order()
    {
        $where['status'] = 2;
        $where['state'] = 0;
        $c2c_receipt_time = $this->config_M->find('c2c_receipt_time', 'value');
        $where['payment_time[<]'] = time() - $c2c_receipt_time * 3600;
        $order_ar = $this->c2c_order_M->have($where);
        if ($order_ar) {
            $this->c2c_S->carry_out($order_ar);
            $data['remark'] = '时间到自动确认订单';
            $this->c2c_order_M->up($order_ar['id'], $data);
            $integrity = $this->user_M->find($order_ar['uid_sell'], 'integrity');
            if ($integrity > 0) {
                $money_S = new \app\service\money();
                $money_S->minus($order_ar['uid_sell'], 1, 'integrity', 'coin_c2c', $order_ar['oid_sell'], $order_ar['uid_buy'], '时间到自动确认订单'); //记录资金流水
            }
        }
    }

    private function del_buy()
    {
        # code...
    }
}
