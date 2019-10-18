<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-15 09:09:40
 * Desc: 库存
 */

namespace app\service;

use app\model\order as OrderModel;
use app\model\order_product as OrderProductModel;

class order{	
	public $orderM;
    public $OrderProductM;
    public function __construct()
    {
		$this->orderM = new OrderModel();
		$this->order_pro_M = new OrderProductModel();
    }

	//拆单
	public function split($id)
	{	
		$where['id']=$id;
		$where['is_pay']=1;
		$data = $this->orderM->have($where);
		if(empty($data)){return false;}
        $product =  $this->order_pro_M->find_by_oid($data['oid']);  
		$product_ar=[];
		foreach($product as &$rs){
			$product_ar[$rs['sid']]['info']=$rs['sid'];
			$product_ar[$rs['sid']]['data'][]=$rs;
        }
        sort($product_ar);
        if(count($product_ar)>1){
            $integral_dk_per=$data['integral_dk_per'];
            foreach($product_ar as $key=>$vo){
                //写入订单
                $order='';
                $order=$data;
                unset($order['id']);
                $order['sid']=$vo['info'];
                $order['sum_price']=0;
                $order['sum_mail']=0;
                $order['send_score']=0;
                $order['cost']=0;
                $order['is_virtual']=1;
                $order['is_red']=0;
                $order['red_id']=0;
                $order['red_money']=0;
                $order['is_integral']=0;
                $order['integral_dk_per']=0;
                $order['integral_dk_money']=0;
                $order['is_split']=1;

                foreach($vo['data'] as $vos){
                    $order['sum_price']=$order['sum_price']+$vos['money'];
                    $order['send_score']=$order['send_score']+$vos['send_score'];
                    $order['cost']=$order['cost']+$vos['cost'];
                    $order['sum_mail']=$vos['mail'];
                    if($vos['is_virtual']==0){
                        $order['is_virtual']=0;
                    }
                    //红包
                    if($vos['pid']==$data['red_pid']){
                        $order['is_red']=$data['is_red'];
                        $order['red_id']=$data['red_id'];
                        $order['red_pid']=$data['red_pid'];
                        $order['red_money']=$data['red_money'];
                    }
                    //积分
                    if($integral_dk_per>0 && $data['integral_dk_money']>0 and $data['is_integral']==1){
                        if($vos['integral_dk_per']>$integral_dk_per){
                            $vos['integral_dk_per']=$integral_dk_per;
                        }
                        if($vos['integral_dk_per']>0){
                            $order['integral_dk_per']=$order['integral_dk_per']+$vos['integral_dk_per'];
                            $order['integral_dk_money']=$order['integral_dk_money']+bcdiv($vos['integral_dk_per'],10,2);;
                            $order['is_integral']=1;
                            $integral_dk_per=$integral_dk_per-$vos['integral_dk_per'];
                        }
                    }
                }
                $money=$order['sum_price']+$order['sum_mail']-$order['integral_dk_money']-$order['red_money'];
                $order['money']=$money;
                if($key==0){
                    $res=$this->orderM->up($data['id'],$order);
                    if(empty($res)){return false;}
                    mb_sms('pay_order',$data['id']);
                }else{
                    $new_oid=(string)$data['oid'].'-'.(string)$key;
                    $order['oid']=$new_oid;
                    $order_res=$this->orderM->save($order);
                    if(empty($order_res)){return false;}
                    $product_where['oid']=$data['oid'];
                    $product_where['sid']=$vo['info'];
                    $res=$this->order_pro_M->up_all($product_where,['oid'=>$new_oid]);    
                    if(empty($res)){return false;}     
                    mb_sms('pay_order',$order_res['id']);
                }     
            }
        }else{
            $res=$this->orderM->up($data['id'],['sid'=>$product_ar[0]['info'],'is_split'=>1]);
            if(empty($res)){return false;} 
            //拼团
            if($data['types']==4){
            (new \app\service\group())->payment_successful($data['id']);
            }
            mb_sms('pay_order',$data['id']);
        }
		return true;
    }

   
    //退货
    public function return_order($order_ar,$order_pro_ar)
    {
        $where['oid']=$order_ar['oid'];
        $where['id[!]']=$order_pro_ar['id'];
        $where['status[!]']=4;
        $order_pro=$this->order_pro_M->lists_all($where);
        if(empty($order_pro)){
            //单个商品
            if($order_ar['status']=='已支付'){
                $data['money']=$order_ar['money'];
                $data['status']='已完成';
                $data['complete_time']=time();
                $data['settle_time']=time();
                $data['is_settle']=1;
                $money=$order_ar['money'];
            }else{
                $data['money']=$order_ar['sum_mail'];
                $data['status']='已完成';
                $data['complete_time']=time();
                $data['settle_time']=time();
                $data['is_settle']=1;
                $money=$order_ar['money']-$order_ar['sum_mail'];
            }
        }else{
            //多个商品
            $data['sum_price']=0;
            $data['send_score']=0;
            $data['cost']=0;
            $data['red_money']=0;
            $data['is_red']=0;
            $data['red_id']=0;
            $data['red_pid']=0;
            $data['is_integral']=0;
            $data['integral_dk_per']=0;
            $data['integral_dk_money']=0;
            $integral_dk_per=$order_ar['integral_dk_per'];
            foreach($order_pro as $vos){
                $data['sum_price']=$data['sum_price']+$vos['money'];
                $data['send_score']=$data['send_score']+$vos['send_score'];
                $data['cost']=$data['cost']+$vos['cost'];
                //红包
                if($vos['pid']==$order_ar['red_pid']){
                    $data['is_red']=$order_ar['is_red'];
                    $data['red_id']=$order_ar['red_id'];
                    $data['red_pid']=$order_ar['red_pid'];
                    $data['red_money']=$order_ar['red_money'];
                }
                //积分
                if($integral_dk_per>0 && $order_ar['integral_dk_money']>0 and $order_ar['is_integral']==1){
                    if($vos['integral_dk_per']>$integral_dk_per){
                        $vos['integral_dk_per']=$integral_dk_per;
                    }
                    if($vos['integral_dk_per']>0){
                        $data['integral_dk_per']=$data['integral_dk_per']+$vos['integral_dk_per'];
                        $data['integral_dk_money']=$data['integral_dk_money']+bcdiv($vos['integral_dk_per'],10,2);;
                        $data['is_integral']=1;
                        $integral_dk_per=$integral_dk_per-$vos['integral_dk_per'];
                    }
                }
            }
            $order_money=$data['sum_price']+$order_ar['sum_mail']-$data['integral_dk_money']-$data['red_money'];
            $data['money']=$order_money;
            $money=$order_ar['money']-$order_money;
        }
        $res=$this->orderM->up($order_ar['id'],$data);
        if(empty($res)){return false;} 
        if($money>0){
            $money_S=new \app\service\money();
            $money_S->plus($order_ar['uid'], $money, 'money', 'order_return', $order_ar['oid'],$order_ar['sid'],$order_pro_ar['title'],''); //记录资金流水
        }
		return true;
    }

    //修改订单退货状态
    public function order_is_raturn($order_ar)
    {
        $where['status']=[1,2,3];
        $where['oid']=$order_ar['oid'];
        $order_pro=$this->order_pro_M->lists_all($where);
        if($order_pro){
            $this->orderM->up($order_ar['id'],['is_return'=>0]);
        }
    }


    //赠送订单
    public function gift_order($uid,$pid,$pay)
    {
        $data=(new \app\model\product())->find($pid);
		//收货地址
		if($data['is_virtual']==0){
			$address=(new \app\model\user_address())->have(['uid'=>$uid,'ORDER'=>['is_show'=>'DESC']]);
			empty($address) && error('收货地址不存在',404);
			$data['address']=$address;
        }
        $sku=(new \app\model\product_sku())->have(['pid'=>$data['id']]);
        $iden_ar = explode('@',$sku['iden']);
        $sku_cn = '';                
        $pro_attr_M = new \app\model\product_attr();
        foreach($iden_ar as $attr){
            if($attr){
            $sku_ar = [];
            $attr_ar = explode(':',$attr);               
            $sku_ar = $pro_attr_M->findme($data['id'],$attr_ar[0],$attr_ar[1]);                        
            $sku_cn .= $sku_ar['parent_title'].":".$sku_ar['sku_title']." ";  
            }                         
        }  
        $car['pid']=$data['id'];
        $car['sku_id']=$sku['id'];
        $car['uid']=$uid;
        $car['sid']=$data['sid'];
        $car['number']=1;
        $car['sku_cn']=$sku_cn;
        $car['sum_price']=$sku['price'];
        $car['cost']=$sku['cost_price'];
        $car['sum_cost']=$sku['cost_price'];
        $car['price']=$sku['price'];
        $data['cart']=$car;
            
		//写入订单
		$order['integral_dk_money']=0;
		$order['integral_dk_per']=0;
		$order['red_money']=0;
        $order['uid']=$uid;
		$order['sum_price']=$data['cart']['sum_price'];
        $order['cost']=$data['cart']['sum_cost'];
        $order['sum_mail']=0;
        $order['sid']=$data['sid'];
        $order['is_virtual']=$data['is_virtual'];
        $order['types']=0;
        $order['types_cn']='普通商品';
        if(isset($data['address'])){
        $order['mail_name']=$data['address']['name'];
        $order['mail_tel']=$data['address']['tel'];
        $order['mail_province']=$data['address']['province'];
        $order['mail_city']=$data['address']['city'];
        $order['mail_area']=$data['address']['area'];
        $order['mail_town']=$data['address']['town'];
        $order['mail_address']=$data['address']['address'];
        }
        $order['made_01']=$data['made_01'];
        $order['made_02']=$data['made_02'];
        $order['made_03']=$data['made_03'];
        $order['cid']=$data['id'];
		$order['money']=$order['sum_price'];
		$order['status']='已支付';
		$order['pay']=$pay;
		$order['is_pay']=1;
		$order['pay_time']=time();
		$or_res=$this->orderM->save_by_oid($order);
		empty($or_res) && error('写入订单失败',400);   
		
        $product=[];
        $product['oid']=$or_res['oid'];
        $product['pid']=$data['id'];
        $product['title']=$data['title'];
        $product['piclink']=$data['piclink'];
        $product['sku_id']=$data['cart']['sku_id'];
        $product['sku_cn']=$data['cart']['sku_cn'];
        $product['cost']=$data['cart']['cost'];
        $product['price']=$data['cart']['price'];
        $product['uid']=$uid;
        $product['sid']=$data['sid'];
        $product['number']=1;
        $product['money']=$data['cart']['sum_price'];
        $product['made_01']=$data['made_01'];
        $product['made_02']=$data['made_02'];
        $product['made_03']=$data['made_03'];
        $res=$this->order_pro_M->save($product);
        empty($res) && error('写入订单商品失败',400);    

        $cart[0]['data'][0]=$data['cart'];

        $stock_S= new \app\service\stock();
		$stock_S->buckle_inventory($cart);//扣库存
		$stock_S->increase_sales($cart);//加销量
		return $or_res['id'];
    }
}