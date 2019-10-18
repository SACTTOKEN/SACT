<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-03-01 17:18:54
 * Desc: 用户相关
 */
namespace app\service;
use app\model\mxq_order as mxq_order_Model;
use app\model\mxqsz as mxqsz_Model;
use app\model\user_attach as user_attach_Model;
use core\lib\redis;
use core\lib\Model;

class mxq{
    public $mxq_order_M;
    public function __construct()
    {
        $this->mxq_order_M=new mxq_order_Model();
    }

    /*买矿机*/
    public function buy($id,$price)
    { 
       $user = $GLOBALS['user'];
       $mxqsz_M=new mxqsz_Model();
	   $machine=$mxqsz_M->find($id);
       empty($machine) && error('M星球不存在已下架',404); 
       //判断金额
	   if($machine['ljrd']<=0){
		   error('M星球不存在已下架',10003);
	   }
       $user_M = new \app\model\user();
       $ar = $user_M->find($user['uid']);
	    
	   if($machine['gmid']>$ar['vip_rating']){
		    error('达到限购条件',404);
	   }  
	   if($ar['mxq_cysl']>2){
		   error('达到限购条件',404);
	   }
	   $ljrd_ptb=$machine['ljrd']/$price;
	   
	   if($ljrd_ptb-$ar['coin']>0){
			error('金额不足',10003);
	   }
       flash_god($user['id']);
	 
       //开始
       $redis = new redis();
       $Model = new Model();
       $Model->action();
       $redis->multi();
	  
       //添加订单
       $data['uid']=$user['uid'];
       $data['mid']=$machine['id'];
       $data['m_pic']=$machine['piclink'];
	   $data['m_title']=$machine['title'];
       $data['m_money']=$machine['ljrd'];
       $data['m_money1']=$ljrd_ptb;
       $data['rd_time']=time();
       $data['sf_time'] = time();
       $res=$this->mxq_order_M->save_by_oid($data);
       empty($res) && error('添加失败',10006);	
       //扣金额  
       $money_S = new \app\service\money();
       $oid = $res['oid'];
       $remark = "购买M星球";
       $money_res = $money_S->minus($user['uid'],$ljrd_ptb,'coin','mxq_buy',$oid,$user['uid'],$remark); //记录资金流水
	   $money_res = $money_S->plus($user['uid'],$ljrd_ptb,'LMJJC','mxq_buy',$oid,$user['uid'],$remark); //记录资金流水
       empty($money_res) && error('添加失败2',10005);  
	   //
	   $yj_ar['mxq_cysl[+]']=1;
	   $yj_ar['mxq_buy[+]']=$machine['ljrd'];
       $user_M=new \app\model\user();
       $user_M->up($user['uid'],$yj_ar);
	   
       $Model->run();
       $redis->exec();
    }
    
	
	/*退出M星球*/
    public function del($id,$price)
    { 
       $user = $GLOBALS['user'];
       $mxq_order_M=new mxq_order_Model();
	   $machine=$mxq_order_M->find($id);
       empty($machine) && error('M星球不存在已下架',404); 
       //判断金额
	   if($machine['status']==0){
	   }
	   else
	   {
		   error('M星球已退出',10003);
	   }
	   if($machine['uid']<>$user['uid']){
		    error('该星球不是您的',10003);
	   }
       $user_M = new \app\model\user();
	   $ljrd_ptb=$machine['m_money']/$price;
       flash_god($user['id']);
	 
       //开始
       $redis = new redis();
       $Model = new Model();
       $Model->action();
       $redis->multi();
	   
       //退出
	   $yj_ar['status']=1;
	   $mxq_order_M = new \app\model\mxq_order();
	   $mxq_order_M->up($machine['id'],$yj_ar);
	   
       //返回金额  
       $money_S = new \app\service\money();
       $oid = $machine['oid'];
       $remark = "退出M星球";
       $money_res = $money_S->plus($user['uid'],$ljrd_ptb,'coin','mxq_del',$oid,$user['uid'],$remark); //记录资金流水
	   $money_res = $money_S->minus($user['uid'],$ljrd_ptb,'LMJJC','mxq_del',$oid,$user['uid'],$remark); //记录资金流水
       empty($money_res) && error('添加失败2',10005);  
	  
	   $yj_ar1['mxq_cysl[-]']=1;
	   $yj_ar1['mxq_buy[-]']=$machine['m_money'];
       $user_M=new \app\model\user();
       $user_M->up($user['uid'],$yj_ar1);

	   
       $Model->run();
       $redis->exec();
    }

    


}