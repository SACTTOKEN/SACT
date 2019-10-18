<?php 
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-03-28 09:23:02
 * Desc: IM
 */
namespace app\ctrl\mobile;
use app\model\coin_price as coin_price_Model;
use app\model\coin_order as coin_order_Model;
use app\model\rhgc_tjb as rhgc_tjb_Model;
use app\model\coin_machine as coin_machine_Model;
use app\validate\IDMustBeRequire;
class coin extends BaseController{

    public $coin_price_M;

	public function __initialize(){
		$this->coin_price_M = new coin_price_Model();
	}

    //首页
    public function index()
    {
        $user = $GLOBALS['user'];
        $data['viprd_ptb']=$user['viprd_ptb'];
        $data['mxq_cysl']=$user['mxq_cysl'];
        $data['coin']=$user['coin'];
        $data['coin_storage']=$user['coin_storage'];
        $data['usdt']=$user['USDT'];
		$data['XJJJ']=$user['XJJJ'];
		$data['sactloop']=$user['sactloop'];
        $data['viprd_usdt']=$user['viprd_usdt'];
        $data['username']=$user['username'];
        $data['price']=$this->coin_price_M->price_all();
        $data['left']=$user['coin_yvip'];
        $data['right']=$user['coin_zvip'];
		$rhgc_zsl=C('rhgc_zsl');
		$data['rhgc_zsl']=$rhgc_zsl;
		$rhgc_csjg=C('rhgc_csjg');
		$rhgc_zjjs=C('rhgc_zjjs');
		$rhgc_zjjg=C('rhgc_zjjg');
		$data['rhgc_zsl']=$rhgc_zsl;
		
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
		
        $coin_order_M=new coin_order_Model();
        $data['coin_number']=$coin_order_M->user_count($user['uid']);
        if($data['coin_number']>6){
            $data['coin_number']=6;
        }
        return $data;
    }

    //AICQ指数
    public function stardetails()
    {
        $user = $GLOBALS['user'];
        $data['avatar']=$user['avatar'];
        $data['username']=$user['username'];
        $data['price']=$this->coin_price_M->price();
        $price_list=$this->coin_price_M->price_list();
        foreach($price_list as $vo){
            $data['price_list']['categories'][]=date("m-d",$vo['effective_time']);
            $data['price_list']['series']['data'][]=$vo['price'];
        }
        return $data;
    }

    //星球市场
    public function star()
    {
		(new \app\validate\AllsearchValidate())->goCheck();
        $page=post("page",1);
        $page_size = post("page_size",10);		
        $coin_machine_M=new coin_machine_Model();
        $where['is_show']=1;
        $data=$coin_machine_M->lists($page,$page_size,$where);
        foreach($data as &$vo){
            $vo['m_life']=$vo['m_life']/24;
        }
		
        $res['data'] = $data; 
        return $res; 
    }

    //持有星球
    public function mystar()
    {
        (new \app\validate\AllsearchValidate())->goCheck();
        $user = $GLOBALS['user'];
		$page=post("page",1);
        $page_size = post("page_size",10);		
        $coin_order_M=new coin_order_Model();
        $where['uid']=$user['uid'];
        $order=['status'=>'ASC','id'=>'DESC'];
        $data=$coin_order_M->lists_sort($page,$page_size,$where,$order);
        foreach($data as &$vo){
        $vo['total']=$vo['z_money'];
        if($vo['status']==2){
            $vo['y_life']=(int)$vo['m_life']/24;
        }else{
            $vo['y_life']=intval((time()-$vo['created_time'])/86400);
        }
        }
        $res['data'] = $data; 
        return $res; 
    }

    /*兑换购买*/
	public function saveadd(){
		$id = post('id');
        (new IDMustBeRequire())->goCheck();
        
        $coin_S = new \app\service\coin();
        $coin_S->buy($id);

		return true;
    }
    
    /* 领取奖励 */
    public function reward()
    {
        $user = $GLOBALS['user'];
        $today=time()-3600;
        //判断是否发放过奖励
        $money_M=new \app\model\money();
        $where['uid']=$user['id'];
        $where['iden']='coin_kjjl';
        $where['created_time[>]']=$today;
        $ar=$money_M->have($where);
        if($ar){
            //奖励已发放
            error($ar['created_time']+3600,10004);	
        }
        //发放奖励
        $coin_S = new \app\service\coin();
        $money=$coin_S->day_reward();
        if($money<=0){
            error(time()+600,10004);
        }
        $data['time']=time()+3600;
        $data['money']='进入矿机收益'.$money.'AICQ';
        return $data;
    }

}

 