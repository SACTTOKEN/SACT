<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-18 09:45:09
 * Desc: 互转
 */

namespace app\ctrl\mobile;

use app\model\transfer as transferModel;
use app\validate\TransferValidate;

class transfer extends BaseController{
	
	public $transfer_M;
    public $flag=0;
	public function __initialize(){
		$this->transfer_M = new transferModel();
	}

	public function index(){
        empty(c('change_imred')) && error('敬请期待',10007);
        $user = $GLOBALS['user'];

        $uwhere['id']=$user['id'];
        $uwhere['pay_password']='';
        $user_ar=(new \app\model\user())->is_find($uwhere);
        if($user_ar){
            $err['info']='请先设置支付密码';
            $err['url']='/setting/pay_password';
            error($err,10008);	
        }

		$data['fee']=c("imred_fee")/10;
		//$data['jiage']=c("fcjg_usdt");
        $coin=explode("|",c("imred_balance"));
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

    public function userinfo()
    {
        $username=post('username');
        $user_M=new \app\model\user();
        $where['OR']['username']=$username;
        $where['OR']['tel']=$username;
        $where['show']=1;
        $user_ar=$user_M->have($where,['id','username','nickname','avatar']);
        empty($user_ar) && error('转入用户不存在',400);
        $user_ar['nickname']=$user_ar['nickname']?$user_ar['nickname']:$user_ar['username'];
        return $user_ar;
    }

	public function transfer_index(){
        empty(c('change_coin')) && error('敬请期待',10007);
        $user = $GLOBALS['user'];
        /*
        $uwhere['id']=$user['id'];
        $uwhere['pay_password']='';
        $user_ar=(new \app\model\user())->is_find($uwhere);
        if($user_ar){
            $err['info']='请先设置支付密码';
            $err['url']='/setting/pay_password';
            error($err,10008);	
        }
        */

		$data['fee']=c("transfer_fee")/10;
        $coin=explode("|",c("transfer_balance"));
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
    
    //IM红包
	public function red_send(){  
        (new TransferValidate())->goCheck('red_send');
        empty(c('change_imred')) && error('敬请期待',400);
        $user = $GLOBALS['user'];
        if(c('imred_rating')==0){
            if($user['rating']==1){
                error('游客无发红包权限',400);
            }
        }
        if(c('imred_coin_rating')==0){
            if($user['coin_rating']==1){
                error('游客无发红包权限',400);
            }
        }
        $password=post("password");
        $password = rsa_decrypt($password);
        $password = md5($password.'inex10086');
        $auth = (new \app\model\user())->check_user_pay($user['username'],$password);
        empty($auth['id']) && error("密码错误",400);

        $im=post('im');
        $iden=post('iden');
        $money=post('money');
        $content=post('content','恭喜发财');
        
        if($money<c('imred_min')){
            error('发红包最低金额'.c('imred_min'),400);
        }
        if(!(is_int($money/c('imred_bs')))){
            error('发红包倍数'.c('imred_bs'),400);
        }

        $user_M=new \app\model\user();
        $where['im']=$im;
        $where['show']=1;
        $user_ar=$user_M->have($where,['id','username']);
        empty($user_ar) && error('转入用户不存在',400);
        if($user_ar['username']==$user['username']){
            error('不能发给自己',400);
        }

        $coin=explode("|",c("imred_balance"));
        $idens='';
        foreach($coin as $vo){
            if($vo){
                if($vo==$iden){
                    $idens=$iden;
                }
            }
        }
        empty($idens) && error('账户类型不支持',400);
        $user_coin = $user_M->find($user['uid'],$idens);
        
        $fee=$money*c("imred_fee")/1000;
        if($money+$fee-$user_coin>0){
        error('金额不足,红包金额'.$money.',手续费'.$fee,10003);
        }
        $actual=$money;

        flash_god($user['id']);


        $redis = new \core\lib\redis();
        $Model = new \core\lib\Model();
        $Model->action();
        $redis->multi();

        $data['fee']=$fee;
        $data['money']=$money;
        $data['actual']=$actual;
        $data['other_id']=$user_ar['id'];
        $data['status']=0;
        $data['uid']=$user['id'];
        $data['cate']=$idens;
        $data['types']=1;
        $data['content']=$content;
        $transfer_ar=$this->transfer_M->save_by_oid($data);
        empty($transfer_ar) && error('添加失败',10006);

        $money_S = new \app\service\money();
        $money_S->minus($user['uid'],$money+$fee,$idens,'transfer',$transfer_ar['oid'],$user_ar['id'],$idens_en.'发红包'); //记录资金流水
        
        $im_S=new \app\service\im();
        $im_S->redenvelope($content,$transfer_ar['oid'],$user['im'],$im);
    
        $Model->run();
        $redis->exec();
        return "红包发放成功";
    }
    

    //拆红包
    public function red_open()
    {
        $user = $GLOBALS['user'];
        (new TransferValidate())->goCheck('red_open');
        $oid=post("oid");
        $where['oid']=$oid;
        $where['types']=1;
        $where['OR']=[
            'uid'=>$user['id'],
            'other_id'=>$user['id']
        ];
        $transfer_ar=$this->transfer_M->have($where);
        empty($transfer_ar) && error('订单不存在',404);
        if($transfer_ar['uid']!=$user['id']){
        if($transfer_ar['status']==0){            
            flash_god($user['id']);
            $data['status']=1;
            $this->transfer_M->up($transfer_ar['id'],$data);
            $idens_en=find_reward_redis($transfer_ar['cate']);
            $money_S = new \app\service\money();
            $money_S->plus($transfer_ar['other_id'],$transfer_ar['money'],
            $transfer_ar['cate'],'transfer',$transfer_ar['oid'],$transfer_ar['uid'],$idens_en.'收红包');
        }
        $ar['title']="给你发了个红包";
        }
        $transfer_ar=$this->transfer_M->find($transfer_ar['id']);
        if($transfer_ar['status']==0){
            $ar['status_cn']='未领取';
        }elseif($transfer_ar['status']==1){
            $ar['status_cn']='已领取';
        }elseif($transfer_ar['status']==2){
            $ar['status_cn']='过期退回';
        }
        $ar['content']=$transfer_ar['content'];
        $idens_en=find_reward_redis($transfer_ar['cate']);
        $ar['money']=sprintf("%.2f",$transfer_ar['money']).$idens_en;
        return $ar;
    }

    //提交转币
	public function saveadd(){   
        (new TransferValidate())->goCheck('saveadd');
        empty(c('change_coin')) && error('敬请期待',400);
        $user = $GLOBALS['user'];
        if(c('transfer_rating')==0){
            if($user['rating']==1){
                error('请升级AICQ居民',400);
            }
        }
        if(c('transfer_coin_rating')==0){
            if($user['coin_rating']==1){
                error('请升级AICQ居民',400);
            }
        }
        /*
        $password=post("password");
        $password = rsa_decrypt($password);
        $password = md5($password.'inex10086');
        $auth = (new \app\model\user())->check_user_pay($user['username'],$password);
        empty($auth['id']) && error("密码错误",400);
        */
        $username=post('username');
        $iden=post('iden');
        $money=post('money');
        
        if($money<c('transfer_min')){
            error('互转最低金额'.c('transfer_min'),400);
        }
        if(!(is_int($money/c('transfer_bs')))){
            error('互转倍数'.c('transfer_bs'),400);
        }


        $user_M=new \app\model\user();
        $where['OR']['username']=$username;
        $where['OR']['tel']=$username;
        $where['show']=1;
        $user_ar=$user_M->have($where,['id','username']);
        empty($user_ar) && error('转入用户不存在',400);
        if($username==$user['username']){
            error('不能自己转自己',400);
        }
		
		
		

        $coin=explode("|",c("transfer_balance"));
        $idens='';
        foreach($coin as $vo){
            if($vo){
                if($vo==$iden){
                    $idens=$iden;
                }
            }
        }
        empty($idens) && error('账户类型不支持',400);
        $user_coin = $user_M->find($user['uid'],$idens);
        $fee=$money*c("transfer_fee")/1000;
        if($money+$fee-$user_coin>0){
        error('金额不足,转账金额'.$money.',手续费'.$fee,10003);
        }
        $actual=$money;
		
		if(c('transfer_ytx')==1){
		$where1=[];
		$where1['uid']=$user['id'];
		$where1['tid']=$user_ar['id'];
		$user_gx_M = new \app\model\user_gx();
		$is_have = $user_gx_M->is_have($where1);
		if(empty($is_have)){
			 $where2=[];
			 $where2['uid']=$user_ar['id'];
			 $where2['tid']=$user['id'];
			 $is_have1 = $user_gx_M->is_have($where2);
			 if(empty($is_have1)){
				 error('只能团队互转',10006); 
			 }
		}
		}
        
        flash_god($user['id']);

        $redis = new \core\lib\redis();
        $Model = new \core\lib\Model();
        $Model->action();
        $redis->multi();

        $data['fee']=$fee;
        $data['money']=$money;
        $data['actual']=$actual;
        $data['other_id']=$user_ar['id'];
        $data['status']=1;
        $data['uid']=$user['id'];
        $data['cate']=$idens;
        $transfer_ar=$this->transfer_M->save_by_oid($data);
        empty($transfer_ar) && error('添加失败',10006);

        $money_S = new \app\service\money();
        $money_S->minus($user['uid'],$money+$fee,$idens,'transfer',$transfer_ar['oid'],$user_ar['id'],$idens_en.'金额转出'); //记录资金流水
        $money_S->plus($user_ar['id'],$actual,$idens,'transfer',$transfer_ar['oid'],$user['uid'],$idens_en.'金额转入'); //记录资金流水
   
        $Model->run();
        $redis->exec();
        return "互转成功";
	}


    public function lists()
    {
        $user = $GLOBALS['user'];
        (new \app\validate\PageValidate())->goCheck();
		$page=post("page",1);
        $page_size = post("page_size",10);		
        $where['OR']=[
            'uid'=>$user['id'],
            'other_id'=>$user['id']
        ];
        $ar=$this->transfer_M->lists($page,$page_size,$where);
        foreach($ar as &$vo){
            if($vo['uid']==$user['id']){
                $vo['title']=(new \app\model\reward())->find_redis($vo['cate']).'金额转出，转入用户：'.user_info($vo['other_id'],'username');
            }else{
                $vo['title']=(new \app\model\reward())->find_redis($vo['cate']).'金额转入，转出用户'.user_info($vo['uid'],'username');     
            }
            if($vo['status']==0){
                $vo['status']="互转中";
            }else{
                $vo['status']="互转成功";
            }
        }
        $data['data'] = $ar; 
        return $data;
    }
}