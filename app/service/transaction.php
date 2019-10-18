<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-03-01 17:18:54
 * Desc: 用户相关
 */
namespace app\service;

class transaction{
    public $transaction_M;
    public $transaction_order_M;
    public $money_S;
    public function __construct()
    {
        $this->transaction_M = new \app\model\transaction();
        $this->transaction_order_M = new \app\model\transaction_order();
        $this->money_S = new \app\service\money();
    }

    public function buy($order_ar)
    {
        if($order_ar['sy_number']<=0){
            return;
        }
        $where['status']=0;
        $where['sy_number[>]']=0;
        $where['types']=2;
        $where['iden']=$order_ar['iden'];
        $where['price[<=]']=$order_ar['price'];
        $where['ORDER']=['price'=>'ASC'];
        $transaction_ar=$this->transaction_M->have($where);
        if($transaction_ar){
            if($transaction_ar['sy_number']==$order_ar['sy_number']){
                $number=$order_ar['sy_number'];
                $buy_data['status']=1;
                $sell_data['status']=1;
            }elseif($transaction_ar['sy_number']>$order_ar['sy_number']){
                $number=$order_ar['sy_number'];
                $buy_data['status']=1;
            }else{
                $number=$transaction_ar['sy_number'];
                $sell_data['status']=1;
            }
            $buy_data['sy_number[-]']=$number;
            $buy_data['wc_number[+]']=$number;
			if($order_ar['buylx']==2){
			$this->money_S->plus($order_ar['uid'],$number*$order_ar['price'],'coin_storage','transaction',$order_ar['oid'],$transaction_ar['uid'],'大盘交易买入');
			}
			else{
            $this->money_S->plus($order_ar['uid'],$number,'coin','transaction',$order_ar['oid'],$transaction_ar['uid'],'大盘交易买入');
			}
            $this->transaction_M->up($order_ar['id'],$buy_data);

            $sell_data['sy_number[-]']=$number;
            $sell_data['wc_number[+]']=$number;
            $this->money_S->plus($transaction_ar['uid'],$number*$transaction_ar['price'],'viprd_usdt','transaction',$transaction_ar['oid'],$order_ar['uid'],'大盘交易卖出');
            $this->transaction_M->up($transaction_ar['id'],$sell_data);

            $order_data['buy_uid']=$order_ar['uid'];
            $order_data['buy_oid']=$order_ar['oid'];
            $order_data['buy_price']=$order_ar['price'];
            $order_data['sell_uid']=$transaction_ar['uid'];
            $order_data['sell_oid']=$transaction_ar['oid'];
            $order_data['sell_price']=$transaction_ar['price'];
            $order_data['number']=$number;
            $order_data['iden']=$order_ar['iden'];
            $this->transaction_order_M->save($order_data);
            if($buy_data['status']==1){
            $order_ar2=$this->transaction_M->find($order_ar['id']);
            $this->buy($order_ar2);
            }
        }
    }
    
    public function sell($order_ar)
    {
        if($order_ar['sy_number']<=0){
            return;
        }
        $where['status']=0;
        $where['sy_number[>]']=0;
        $where['types']=1;
        $where['iden']=$order_ar['iden'];
        $where['price[>=]']=$order_ar['price'];
        $where['ORDER']=['price'=>'DESC'];
        $transaction_ar=$this->transaction_M->have($where);
      
        if($transaction_ar){
            if($transaction_ar['sy_number']==$order_ar['sy_number']){
                $number=$order_ar['sy_number'];
                $sell_data['status']=1;
                $buy_data['status']=1;
            }elseif($transaction_ar['sy_number']>$order_ar['sy_number']){
                $number=$order_ar['sy_number'];
                $sell_data['status']=1;
            }else{
                $number=$transaction_ar['sy_number'];
                $buy_data['status']=1;
            }
            $sell_data['sy_number[-]']=$number;
            $sell_data['wc_number[+]']=$number;
			
            $this->money_S->plus($order_ar['uid'],$number*$order_ar['price'],'viprd_usdt','transaction',$order_ar['oid'],$transaction_ar['uid'],'大盘交易买入');
            $this->transaction_M->up($order_ar['id'],$sell_data);

            $buy_data['sy_number[-]']=$number;
            $buy_data['wc_number[+]']=$number;
            //$this->money_S->plus($transaction_ar['uid'],$number,'coin','transaction',$transaction_ar['oid'],$order_ar['uid'],'大盘交易卖出');
			if($transaction_ar['buylx']==2){
			$this->money_S->plus($transaction_ar['uid'],$number*$transaction_ar['price'],'coin_storage','transaction',$transaction_ar['oid'],$order_ar['uid'],'大盘交易卖出');
			}
			else{
            $this->money_S->plus($transaction_ar['uid'],$number,'coin','transaction',$transaction_ar['oid'],$order_ar['uid'],'大盘交易卖出');
			}
			
			
            $this->transaction_M->up($transaction_ar['id'],$buy_data);

            $order_data['sell_uid']=$order_ar['uid'];
            $order_data['sell_oid']=$order_ar['oid'];
            $order_data['sell_price']=$order_ar['price'];
            $order_data['buy_uid']=$transaction_ar['uid'];
            $order_data['buy_oid']=$transaction_ar['oid'];
            $order_data['buy_price']=$transaction_ar['price'];
            $order_data['number']=$number;
            $order_data['iden']=$order_ar['iden'];
            $this->transaction_order_M->save($order_data);
            if($buy_data['status']==1){
            $order_ar2=$this->transaction_M->find($order_ar['id']);
            $this->sell($order_ar2);
            }
        }
    }

}