<?php 
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-03-28 09:23:02
 * Desc: IM
 */
namespace app\ctrl\mobile;
use app\model\hjzj_order as hjzj_order_Model;
use app\validate\hjzjValidate;
use app\validate\IDMustBeRequire;

class hjzj extends BaseController{
	
 
	
	public $hjzj_order_M;
	public function __initialize(){
		$this->hjzj_order_M = new hjzj_order_Model();
	}
	
	
    //首页
    public function index()
    {
        $user = $GLOBALS['user'];
        $data['username']=$user['username'];
	    $data['coin_title']=find_reward_redis('coin');
		$data['coin']=$user['coin'];
        return $data;
    }
	
	 public function lists()
    {
        $user = $GLOBALS['user'];
        (new \app\validate\PageValidate())->goCheck();
		$page=post("page",1);
        $page_size = post("page_size",10);		
		$where['uid']=$user['id'];
		//$where['order']=['status'=>'ASC','id'=>'DESC'];
		$hjzj_order_M=new hjzj_order_Model();
        $ar=$this->hjzj_order_M->lists($page,$page_size,$where);
		$jtgdsj=date("w");
        foreach($ar as &$vo){
			$vo['status1']=1;
            if($vo['status']==0){
                $vo['status_cn']="攻打中";
				if($jtgdsj==2){
				$vo['status1']=0;
				}
            }else{
                $vo['status_cn']="攻打完成";
            }
        }
        $data['data'] = $ar; 
        return $data;
    }
	
    /*兑换购买*/
	public function saveadd(){
		(new hjzjValidate())->goCheck('saveadd');
		$jtgdsj=date("w");
		if($jtgdsj>0){
			 error('只有周天才能攻打',404); 
		}
		
		$user = $GLOBALS['user'];
		$where['uid']=$user['uid'];
		$where['rd_time[>=]']=strtotime(date("Y/m/d")." 00:00:00");
		$hjzj_order_M=new hjzj_order_Model();
		$is_have = $hjzj_order_M->is_have($where);
		if($is_have){
			error('本周您已经攻打了',10006); 
		}
		
		$money=post('money');
		
        if($money<c('hjzj_zdsl')){
            error('攻打最低数量'.c('hjzj_zdsl'),400);
        }
        if(!(is_int($money/c('hjzj_bs')))){
            error('攻打倍数数量'.c('hjzj_zdsl'),400);
        }
		
		$user_M=new \app\model\user();
        $user_coin = $user_M->find($user['uid'],"coin");
		if($money-$user_coin>0){
           error('金额不足,攻打失败',10003);
        }
		flash_god($user['id']);
        $redis = new \core\lib\redis();
        $Model = new \core\lib\Model();
        $Model->action();
        $redis->multi();
        $data['money']=$money;
        $data['uid']=$user['id'];
		$data['rd_time']=time();
        $hjzj_order_ar=$this->hjzj_order_M->save_by_oid($data);
        empty($hjzj_order_ar) && error('攻打失败',10006);
        $money_S = new \app\service\money();
        $money_S->minus($user['uid'],$money,"coin",'gdhjzj',$hjzj_order_ar['oid'],$user['uid'],'攻打黄金战舰'); //记录资金流水
		$money_S->plus($user['uid'],$money,"LMJJC",'gdhjzj',$hjzj_order_ar['oid'],$user['uid'],'攻打黄金战舰'); //记录资金流水
        $Model->run();
        $redis->exec();
        return "攻打成功";
    }
	
    /*兑换购买*/
	public function savedel(){
		$id = post('id');
        (new IDMustBeRequire())->goCheck();
        $jtgdsj=date("w");
		if($jtgdsj==2){}
		else{
			 error('只有周三才能选择返航',404); 
		}
		$hjzj_order_M=new hjzj_order_Model();
		$machine=$hjzj_order_M->find($id);
		
        empty($machine) && error('攻打记录不存在',404); 
		if($machine['status']==0){}
		else
		{
		   error('已经攻打完成',404);
	   }
	   $user = $GLOBALS['user'];
	   if($machine['uid']<>$user['uid']){
		   print_r($machine);
		    error('该攻打记录不是您的',404);
	   }
	  
	    $bc_money=$machine['money']*C('hjzj_sdqfb')/1000;
	    $money=$machine['money']+$bc_money;
		flash_god($user['id']);
        $redis = new \core\lib\redis();
        $Model = new \core\lib\Model();
        $Model->action();
        $redis->multi();
		$yj_ar['status']=1;
		$yj_ar['bc_money']=$bc_money;
		$yj_ar['sf_time']=time();
		$hjzj_order_M = new \app\model\hjzj_order();
		$hjzj_order_M->up($machine['id'],$yj_ar);
		
		$money_S = new \app\service\money();
		$oid = $machine['oid'];
		$remark = "攻打黄金战舰返航";
		
		$money_res = $money_S->plus($user['uid'],$money,'coin','hjzj_del',$oid,$user['uid'],$remark); //记录资金流水
		empty($money_res) && error('返航失败',10005);  
	   
	   
       
        $Model->run();
        $redis->exec();
		
		
	    return "返航成功";
    }
 

}

 