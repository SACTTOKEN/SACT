<?php 
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-03-28 09:23:02
 * Desc: IM
 */
namespace app\ctrl\mobile;
use app\model\coin_price as coin_price_Model;
use app\model\mxq_order as mxq_order_Model;
use app\model\mxqsz as mxqsz_Model;
use app\validate\IDMustBeRequire;
class mxq extends BaseController{

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
        $mxq_order_M=new mxq_order_Model();
        $data['coin_number']=$mxq_order_M->user_count($user['uid']);
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
        $mxqsz_M=new mxqsz_Model();
		$where['ljrd[>]']=0;
        $data=$mxqsz_M->lists($page,$page_size,$where);
		$vip_rating_M=new \app\model\vip_rating();
		$user = $GLOBALS['user'];
		$price=$this->coin_price_M->price();
        foreach($data as &$vo){
			if($user['vip_rating']>=$vo['gmid'] && $user['mxq_cysl']<3)
			{
				$vo['rdxz']=1;
			}
			else
			{
				$vo['rdxz']=0;
			}
			$vo['rd_ptb_sl']=ceil($vo['ljrd']/$price*10000)/10000;
			$vo['gm_title']=$vip_rating_M->find($vo['gmid'],'title');
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
        $mxq_order_M=new mxq_order_Model();
        $where['uid']=$user['uid'];
        $order=['status'=>'ASC','id'=>'DESC'];
        $data=$mxq_order_M->lists_sort($page,$page_size,$where,$order);
        foreach($data as &$vo){
      
        if($vo['status']==1){
            $vo['status_cn']="已退出";
        }else{
            $vo['status_cn']="未退出";
        }
        }
        $res['data'] = $data; 
        return $res; 
    }

    /*兑换购买*/
	public function saveadd(){
		$id = post('id');
        (new IDMustBeRequire())->goCheck();
        $coin_S = new \app\service\mxq();
		$price=$this->coin_price_M->price();
        $coin_S->buy($id,$price);
		return true;
    }
	
    /*兑换购买*/
	public function savedel(){
		$id = post('id');
        (new IDMustBeRequire())->goCheck();
        $coin_S = new \app\service\mxq();
		$price=$this->coin_price_M->price();
        $coin_S->del($id,$price);
		return true;
    }
 

}

 