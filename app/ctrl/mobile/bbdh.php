<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-18 09:45:09
 * Desc: 互转
 */

namespace app\ctrl\mobile;
use app\model\coin_price as coin_price_Model;
use app\model\bbdh as bbdhModel;
use app\validate\bbdhValidate;

class bbdh extends BaseController{
	

	public $bbdh_M;
    public $flag=0;
	public function __initialize(){
		$this->bbdh_M = new bbdhModel();
		$this->coin_price_M = new coin_price_Model();
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

		$data['fee']=c("fcdh_usdtsxf")/10;
		//$data['fee1']=c("fcdh_ptbsxf")/10;
		$data['jiage']=c("fcjg_usdt");
		$data['iden']="USDT_storage";
		$data['title']=find_reward_redis($data['iden']);
        $coin=explode("|","mxq_fcsl");
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
		(new bbdhValidate())->goCheck('saveadd');
		if(c('sfkq_fcsd')==1){
		}
		else{
			error('敬请期待',400);
		}
	  
	 
        $user = $GLOBALS['user'];
        /*
        $password=post("password");
        $password = rsa_decrypt($password);
        $password = md5($password.'inex10086');
		
        $auth = (new \app\model\user())->check_user_pay($user['username'],$password);
		
        empty($auth['id']) && error("密码错误",400);
        */
        //$username=post('username');
        $iden=post('iden');
        $money=post('money');
       
        if($money<1){
            error('兑换最低金额1',400);
        }
        if(!(is_int($money/1))){
            error('兑换倍数1',400);
        }
        
        //$coin=explode("|","coin|USDT");
		$coin=explode("|","USDT_storage");
        $idens='';
        foreach($coin as $vo){
            if($vo){
                if($vo==$iden){
                    $idens=$iden;
                }
            }
        }
		
        empty($idens) && error('账户类型不支持',400);
		 $user_M=new \app\model\user();
        $user_coin = $user_M->find($user['uid'],"mxq_fcsl");
		if($idens=="coin"){
			$transfer_fee=c("fcdh_ptbsxf");
		}
		else{
		$transfer_fee=c("fcdh_usdtsxf");
		}
        $fee=$money*$transfer_fee/1000;
        if($money-$user_coin>0){
        error('金额不足,兑换金额'.$money,10003);
        }
        $actual=$money-$fee;
		
		$actual_dj=c("fcjg_usdt");
		
		$actual=$actual*$actual_dj; //USDT价格。。
		$fee=$fee*$actual_dj;
		if($idens=="coin"){  //如果转的是平台币、、除于价格
		    $price=$this->coin_price_M->price();
			$actual=$actual/$price; //USDT价格。。
			$fee=$fee/$price;
		}
		
		$idens_en=find_reward_redis("mxq_fcsl");
		$idens_en1=find_reward_redis($idens);
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
		
		
        $bbdh_ar=$this->bbdh_M->save_by_oid($data);
        empty($bbdh_ar) && error('添加失败',10006);
		

        $money_S = new \app\service\money();
        $money_S->minus($user['uid'],$money,"mxq_fcsl",'bbdh',$bbdh_ar['oid'],$user['uid'],$idens_en.'兑换'.$idens_en1); //记录资金流水
        $money_S->plus($user['uid'],$actual,$idens,'bbdh',$bbdh_ar['oid'],$user['uid'],$idens_en.'兑换'.$idens_en1); //记录资金流水
   
        $Model->run();
        $redis->exec();
        return "兑换成功";
	}


    public function lists()
    {
        $user = $GLOBALS['user'];
        (new \app\validate\PageValidate())->goCheck();
		$page=post("page",1);
        $page_size = post("page_size",10);		
		$where['uid']=$user['id'];
        $ar=$this->bbdh_M->lists($page,$page_size,$where);
		$idens_en=find_reward_redis("mxq_fcsl");
        foreach($ar as &$vo){
           // $vo['title']=$idens_en."兑换".(new \app\model\reward())->find_redis($vo['cate']);
		    $vo['title']=$idens_en."兑换USDT";
            if($vo['status']==0){
                $vo['status']="兑换中";
            }else{
                $vo['status']="兑换成功";
            }
        }
        $data['data'] = $ar; 
        return $data;
    }
}