<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-03-01 17:18:54
 * Desc: 用户相关
 */
namespace app\service;
use app\model\gdxq_order as gdxq_order_Model;
use app\model\gdxqsz as gdxqsz_Model;
use app\model\user_attach as user_attach_Model;
use core\lib\redis;
use core\lib\Model;

class gdxq{
    public $gdxq_order_M;
    public function __construct()
    {
        $this->gdxq_order_M=new gdxq_order_Model();
    }

    /*买矿机*/
    public function buy($id)
    { 
       $user = $GLOBALS['user'];
       $gdxqsz_M=new gdxqsz_Model();
	   $machine=$gdxqsz_M->find($id);
       empty($machine) && error('攻打星球不存在已下架',404); 
       $user_M = new \app\model\user();
       $ar = $user_M->find($user['uid']);
	   $gdxq_zdfc=C('gdxq_zdfc');
	   if($ar['mxq_fcsl']-$gdxq_zdfc<0){
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
       $data['gid']=$machine['id'];
       $data['g_pic']=$machine['piclink'];
	   $data['g_title']=$machine['title'];
       $data['g_money']=$ar['mxq_fcsl'];
       $data['rd_time']=time();
       $data['sf_time'] = time();
       $res=$this->gdxq_order_M->save_by_oid($data);
       empty($res) && error('添加失败',10006);	
	   /*
       //扣金额  
       $money_S = new \app\service\money();
       $oid = $res['oid'];
       $remark = "购买M星球";
       $money_res = $money_S->minus($user['uid'],$ljrd_ptb,'coin','mxq_buy',$oid,$user['uid'],$remark); //记录资金流水
       empty($money_res) && error('添加失败2',10005);  
	   //
	   $yj_ar['mxq_cysl[+]']=1;
       $user_M=new \app\model\user();
       $user_M->up($user['uid'],$yj_ar);
	   */
       $Model->run();
       $redis->exec();
    }
    
	
	
    


}