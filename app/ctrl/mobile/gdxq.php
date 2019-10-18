<?php 
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-03-28 09:23:02
 * Desc: IM
 */
namespace app\ctrl\mobile;
use app\model\coin_price as coin_price_Model;
use app\model\gdxq_order as gdxq_order_Model;
use app\model\gdxqsz as gdxqsz_Model;
use app\validate\IDMustBeRequire;
class gdxq extends BaseController{

    public $coin_price_M;

	public function __initialize(){
		$this->coin_price_M = new coin_price_Model();
	}

    //首页
    public function index()
    {
        $user = $GLOBALS['user'];
        $data['username']=$user['username'];
        $data['price']=$this->coin_price_M->price_all();
        $data['left']=$user['coin_yvip'];
        $data['right']=$user['coin_zvip'];
        $gdxq_order_M=new gdxq_order_Model();
        $data['coin_number']=$gdxq_order_M->user_count($user['uid']);
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
        
        $gdxqsz_M=new gdxqsz_Model();
        $data=$gdxqsz_M->lists();
		//$vip_rating_M=new \app\model\vip_rating();
		$user = $GLOBALS['user'];
		$gdxq_zdfc=C('gdxq_zdfc');
		$rdxz=0;
		$ksgdsj=strtotime(date("Y/m/d")." 08:00:00");
		$jsgdsj=strtotime(date("Y/m/d")." 20:00:00");
		$xzgdsj=time();
		if($xzgdsj-$ksgdsj>=0&&$xzgdsj-$jsgdsj<0){
			$user_M = new \app\model\user();
			$mxq_fcsl = $user_M->find($user['uid'],'mxq_fcsl');
			if($mxq_fcsl-$gdxq_zdfc>=0){
				$rdxz=1;  //指定时间内才可以攻打
			}
		} 
        foreach($data as &$vo){
			$vo['rdxz']=$rdxz;
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
        $gdxq_order_M=new gdxq_order_Model();
        $where['uid']=$user['uid'];
        $order=['status'=>'ASC','id'=>'DESC'];
        $data=$gdxq_order_M->lists_sort($page,$page_size,$where,$order);
        foreach($data as &$vo){
        if($vo['status']==1){
            $vo['status_cn']="攻打成功";
        }else if($vo['status']==2){
            $vo['status_cn']="攻打失败";
        }else{
            $vo['status_cn']="攻打中";
        }
        }
        $res['data'] = $data; 
        return $res; 
    }

    /*兑换购买*/
	public function saveadd(){
		$id = post('id');
		$ksgdsj=strtotime(date("Y/m/d")." 08:00:00");
		$jsgdsj=strtotime(date("Y/m/d")." 20:00:00");
		$xzgdsj=time();
		if($xzgdsj-$ksgdsj<0&&$xzgdsj-$jsgdsj<0){
			error('未到攻打时间',10003); 
		}else if($xzgdsj-$jsgdsj>=0){
			error('今天攻打时间已结束',10003); 
		}
		$gdxq_order_M=new gdxq_order_Model();
		$user = $GLOBALS['user'];
		$where['uid']=$user['uid'];
		$where['rd_time[>=]']=strtotime(date("Y/m/d")." 00:00:00");
		/*
		$page=post("page",1);
        $page_size = post("page_size",10);	
		$data=$gdxq_order_M->lists($page,$page_size,$where);
		$res['data'] = $data; 
        return $res; 
		exit;
		*/
		$is_have = $gdxq_order_M->is_have($where);
		if($is_have){
			error('您今天已经攻打了',10006); 
		}
		else{
			(new IDMustBeRequire())->goCheck();
			$coin_S = new \app\service\gdxq();
			$coin_S->buy($id);
			return true;
		}
        
    }
	
   

}

 