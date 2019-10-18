<?php 
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-03-28 09:23:02
 * Desc: IM
 */
namespace app\ctrl\mobile;
use app\model\coin_price as coin_price_Model;
use app\model\vip_order as vip_order_Model;
use app\model\vip_rating as vip_rating_Model;
use app\validate\IDMustBeRequire1;
class vip extends BaseController{

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
        $vip_order_M=new vip_order_Model();
        $data['coin_number']=$vip_order_M->user_count($user['uid']);
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
        $vip_rating_M=new vip_rating_Model();
		$where['ljrd[>]']=0;
        $data=$vip_rating_M->lists($page,$page_size,$where);
		$user = $GLOBALS['user'];
		$dzrd_ptbqfb=C('dzrd_ptbqfb')/10;
		$price=$this->coin_price_M->price();
		
        foreach($data as &$vo){
			if($user['vip_rating']>$vo['id'])
			{
				$vo['rdxz']=0;
			}
			else
			{
				$vo['rdxz']=1;
			}
			if($user['vip_rating']>1)
			{
				$vo['xyxz']=1;
			}
			else
			{
				$vo['xyxz']=0;
			}
			$vo['rd_ptb']=$dzrd_ptbqfb;
			$vo['rd_ptb_sl']=ceil($vo['ljrd']/$price*10000)/10000;
			
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
        $vip_order_M=new vip_order_Model();
        $where['uid']=$user['uid'];
        $order=['status'=>'ASC','id'=>'DESC'];
        $data=$vip_order_M->lists_sort($page,$page_size,$where,$order);
        foreach($data as &$vo){
      
        if($vo['status']==1){
            $vo['status_cn']="已出局";
        }else{
            $vo['status_cn']="未出局";
        }
        }
        $res['data'] = $data; 
        return $res; 
    }

    /*兑换购买*/
	public function saveadd(){
		
		$dzrd_xzsl=C('dzrd_xzsl');
		if($dzrd_xzsl>0){
		$user = $GLOBALS['user'];
		$where['uid']=$user['uid'];
		$where['status']=0;
		$vip_order_M=new vip_order_Model();
		$wcjds=$vip_order_M->new_count($where);
		if($wcjds-$dzrd_xzsl>=0){
			 error('已达限购条件',404); 
		} 
		}
		$id = post('id');
		$xzlx = post('xzlx');
        (new IDMustBeRequire1())->goCheck();
        
        $coin_S = new \app\service\vip();
		$price=$this->coin_price_M->price();
		
        $coin_S->buy($id,$xzlx,$price);

		return true;
    }
 

}

 