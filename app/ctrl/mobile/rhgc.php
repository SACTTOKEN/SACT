<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-18 09:45:09
 * Desc: 互转
 */

namespace app\ctrl\mobile;
use app\model\rhgc_tjb as rhgc_tjb_Model;
use app\model\rhgc as rhgcModel;
use app\validate\rhgcValidate;

class rhgc extends BaseController{
	

	public $rhgc_M;
    public $flag=0;
	public function __initialize(){
		$this->rhgc_M = new rhgcModel();
		$this->rhgc_tjb_M = new rhgc_tjb_Model();
	}

	public function index(){
       // empty(c('change_imred')) && error('敬请期待',10007);
        $user = $GLOBALS['user'];
        /*
        $uwhere['id']=$user['id'];
        $uwhere['pay_password']='';
        $user_ar=(new \app\model\user())->is_find($uwhere);
        if($user_ar){
            $err['info']='请先设置支付密码';
            $err['url']='/setting/pay_password';
            error($err,10008);	
        }*/
		
		$rhgc_zsl=C('rhgc_zsl');
		$data['rhgc_zsl']=$rhgc_zsl;
		$rhgc_csjg=C('rhgc_csjg');
		$rhgc_zjjs=C('rhgc_zjjs');
		$rhgc_zjjg=C('rhgc_zjjg');
		
		$where1=array();
		$rhgc_tjb_M=new \app\model\rhgc_tjb();
		$rhgc_ydhsl_ar=$rhgc_tjb_M->have($where1);
		
		if(empty($rhgc_ydhsl_ar)) {
			 $rhgc_ydhsl=0;
		 }else{
			 $rhgc_ydhsl= $rhgc_ydhsl_ar['money'];
		}
		
		$rhgc_sysl=$rhgc_zsl-$rhgc_ydhsl;
		if($rhgc_sysl<0){
		$rhgc_sysl=0;
		}
		$rhgc_sysl=sprintf("%.2f",$rhgc_sysl);
		$rhgc_jg=$rhgc_csjg+$rhgc_zjjg*floor($rhgc_ydhsl/$rhgc_zjjs);
		$rhgc_jg=sprintf("%.4f",$rhgc_jg);
		$data['rhgc_sysl']=$rhgc_sysl;
		$data['rhgc_jg']=$rhgc_jg;
		
		

		
        $coin=explode("|","USDT");
        $i=0;
        foreach($coin as $vo){
            if($vo){
                $data['coin'][$i]['title']=find_reward_redis($vo);
                $data['coin'][$i]['iden']=$vo;
                $data['coin'][$i]['money']=$user[$vo];
                $i++;
            }
        }
        return $data;
    }

    
  
    //提交转币
	public function saveadd(){
		(new rhgcValidate())->goCheck('saveadd');
		//empty(c('change_coin')) && error('敬请期待',400);
		
        $user = $GLOBALS['user'];
       
        $money=post('money');
       
        if($money<1){
            error('交易最低金额1',400);
        }
        if(!(is_int($money/1))){
            error('交易倍数1',400);
        }
        
        $coin=explode("|","coin");
		
        $idens='coin';
       
		
        empty($idens) && error('账户类型不支持',400);
		$user_M=new \app\model\user();
        $user_coin = $user_M->find($user['uid'],"USDT");
		
       
        if($money-$user_coin>0){
        error('金额不足,交易金额'.$money,10003);
        }
		
		$rhgc_zsl=C('rhgc_zsl');
		$rhgc_csjg=C('rhgc_csjg');
		$rhgc_zjjs=C('rhgc_zjjs');
		$rhgc_zjjg=C('rhgc_zjjg');
		
		$where1=array();
		$rhgc_tjb_M=new \app\model\rhgc_tjb();
		$rhgc_ydhsl_ar=$rhgc_tjb_M->have($where1);
		if(empty($rhgc_ydhsl_ar)) {
			 $rhgc_ydhsl=0;
		 }else{
			 $rhgc_ydhsl= $rhgc_ydhsl_ar['money'];
		}
		$rhgc_sysl=$rhgc_zsl-$rhgc_ydhsl;
		if($rhgc_sysl<0){
		$rhgc_sysl=0;
		}
		$rhgc_sysl=sprintf("%.2f",$rhgc_sysl);
		$rhgc_jg=$rhgc_csjg+$rhgc_zjjg*floor($rhgc_ydhsl/$rhgc_zjjs);
		$rhgc_jg=sprintf("%.4f",$rhgc_jg);
		
		$actual=$money/$rhgc_jg;
		$fee=$rhgc_jg;
		if($actual-$rhgc_sysl>0){
			error('RH池剩余不足',400);
		}
		$money1=$money;
		$rhgc_jjlma=C('rhgc_jjlma');
		
		$money2=$money1*$rhgc_jjlma/1000;
		
		$money1=$money1-$money2;
		
		//$idens_en=find_reward_redis("USDT");
		//$idens_en1=find_reward_redis($idens);
        flash_god($user['id']);

        $redis = new \core\lib\redis();
        $Model = new \core\lib\Model();
        $Model->action();
        $redis->multi();

        $data['fee']=$fee;
        $data['money']=$money;
        $data['actual']=$actual;
        //$data['other_id']=$user_ar['id'];
        $data['status']=1;
        $data['uid']=$user['id'];
        $data['cate']=$idens;
        $bbdh_ar=$this->rhgc_M->save_by_oid($data);
        empty($bbdh_ar) && error('添加失败',10006);
		$rhgc_tjb_M=new \app\model\rhgc_tjb();
		$rhgc_ydhsl_ar=$rhgc_tjb_M->have($where1);
		if(empty($rhgc_ydhsl_ar)) {
			 $data_gc['money']=$actual;
             $data_gc['money1']=$money1;
			 $data_gc['money2']=$money2;
             $this->rhgc_tjb_M->save($data_gc);
		 }else{
			 $data_gc['money[+]']=$actual;
			 $data_gc['money1[+]']=$money1;
			 $data_gc['money2[+]']=$money2;
			 $this->rhgc_tjb_M->up($rhgc_ydhsl_ar['id'],$data_gc);
		}
		
		
		

        $money_S = new \app\service\money();
	
        $money_S->minus($user['uid'],$money,"USDT",'rhgc',$bbdh_ar['oid'],$user['uid'],"RH共冲"); //记录资金流水
	
        $money_S->plus($user['uid'],$actual,$idens,'rhgc',$bbdh_ar['oid'],$user['uid'],"RH共冲"); //记录资金流水
	
		if($money1>0){
			  $money_S->plus($user['uid'],$money1,"XJJJ",'rhgc',$bbdh_ar['oid'],$user['uid'],"RH共冲"); //记录资金流水
		}
		
		if($money2>0){
			  $money_S->plus($user['uid'],$money2,"LMJJA",'rhgc',$bbdh_ar['oid'],$user['uid'],"RH共冲"); //记录资金流水
		}
   
        $Model->run();
        $redis->exec();
        return "RH交易成功";
	}


    public function lists()
    {
        $user = $GLOBALS['user'];
        (new \app\validate\PageValidate())->goCheck();
		$page=post("page",1);
        $page_size = post("page_size",10);		
		$where['uid']=$user['id'];
        $ar=$this->rhgc_M->lists($page,$page_size,$where);
		$idens_en=find_reward_redis("rhgc");
		
        foreach($ar as &$vo){
            //$vo['title']=$idens_en."交易".(new \app\model\reward())->find_redis($vo['cate']);
			$vo['title']=$idens_en;
            if($vo['status']==0){
                $vo['status']="交易中";
            }else{
                $vo['status']="交易成功";
            }
        }
        $data['data'] = $ar; 
        return $data;
    }
}