<?php

/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-07-27 14:51:11
 * Desc: dapp
 */

namespace app\ctrl\mobile;

use app\validate\FundsValidate;

class dapp extends BaseController
{
	public $money_S;
	public function __initialize()
	{
		$this->money_S = new \app\service\money();
    }

    public function balance()
    {
        return $GLOBALS['user']['ETH'];
    }

  
    //入单
    public function recharge()
    {
        $money=post('money');    
        (new FundsValidate())->goCheck('dapp');    
       
        $user=$GLOBALS['user'];
        flash_god($user['id']);
        $recharge_M=new \app\model\recharge();
        $order_ar2=$recharge_M->have(['uid'=>$user['id'],'status'=>2]);
        if($order_ar2){
            error('你有一笔交易支付中，请稍后再充值',404);
        }
        $order_ar=$recharge_M->have(['money'=>$money,'uid'=>$user['id'],'status'=>0]);
        if($order_ar){
            $recharge_M->up($order_ar['id'],['created_time'=>time()]);
            $data['oid']=$order_ar['oid'];
            $data['money']=$order_ar['money'];
            $data['account']=c('dapp_account');
            return $data;
        }
        //入单
        $order['uid']=$user['uid'];
        $order['money']=$money;
        $order['cate']='USDT';
        $order['types']=1;
        $order['imtoken']=$user['imtoken'];
        $order_ar=$recharge_M->save_by_oid($order);

        $data['oid']=$order_ar['oid'];
        $data['money']=$money;
        $data['account']=c('dapp_account');
        return $data;
    }


    public function pay()
    {
        $oid=post('oid');
        $hash=post('hash');
        (new FundsValidate())->goCheck('dapp_pay');    
        $user = $GLOBALS['user'];
        //$dapp_hash_M=new \app\model\dapp_hash();
        //$dapp_hash_M->save(['oid'=>$oid,'hash'=>$hash,'uid'=>$user['id']]);

        $recharge_M=new \app\model\recharge();
        $order_ar=$recharge_M->have(['oid'=>$oid,'uid'=>$user['id']]);
  
        empty($order_ar) && error('订单不存在',404);
        if($order_ar['status']!=0){
            error('订单已支付',404);
        }
        $redis = new \core\lib\redis();
        $Model = new \core\lib\Model();
        $Model->action();
        $redis->multi();

        //充值记录
        $data['pay_time']=time();
        $data['types']=1;
        $data['pay']='DAPP转入';
        $data['status']=2;
        $data['hash']=$hash;
        $order=$recharge_M->up($order_ar['id'],$data);
        empty($order) && error('添加失败',10006);
        //$this->money_S->plus($order_ar['uid'], $order_ar['money'], 'USDT', "dapp_recharge", $order_ar['oid'], $order_ar['uid'],$hash,'');
        
        $Model->run();
        $redis->exec();
        return "支付成功";
    }

    
    

}