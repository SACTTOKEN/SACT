<?php

/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-07-27 14:51:11
 * Desc: 拼团
 */

namespace app\ctrl\mobile;

use app\model\order;
use app\model\product as productModel;
use app\validate\IDMustBeRequire;

class group extends PublicController
{

	public $rob_time_M;
	public $productM;
	public $product_S;
	public function __initialize()
	{
		$this->productM = new ProductModel();
    }

    //广告
    public function banner()
    {
        return c('group_banner');
    }
 

    //列表
    public function lists()
    {
        (new \app\validate\PageValidate())->goCheck();      
        $page=post("page",1);
        $page_size = post("page_size",10);
       
        $where['types'] = 4;
        $where['show'] = 1;
		$where['is_check'] = 1;
        $data = $this->productM->lists_by_mobile($page,$page_size,$where);  
        foreach($data as &$vo){
            $vo['group_price']='';
            $price=$vo['price'];
            if (is_array($price)) {
                foreach ($price as $vos) {
                    if ($vos) {
                            $vo['group_price'] .= $vos*$vo['group_discount']/10 . '-';
                    }
                }
            }
            $vo['group_price'] = rtrim($vo['group_price'], "-");
        }
        return $data;  
    }

    public function info()
    {
        (new \app\validate\IDMustBeRequire())->goCheck();
        $id = post('id'); 
        $where['id']=$id;
        $where['show'] = 1;
        $where['is_check'] = 1;
        $data = $this->productM->have($where);
        empty($data) && error('商品不存在',10007);	
        $data=(new \app\service\group())->info($data);    //限时特惠
        return $data;
    }


    //推荐拼团
    public function recommend()
    {
        $where['types'] = 4;
        $where['show'] = 1;
		$where['is_check'] = 1;
        $data = $this->productM->lists_tj($where);  
        foreach($data as &$vo){
            $vo['group_price']='';
            $price=$vo['price'];
            if (is_array($price)) {
                foreach ($price as $vos) {
                    if ($vos) {
                            $vo['group_price'] .= $vos*$vo['group_discount']/10 . '-';
                    }
                }
            }
            $vo['group_price'] = rtrim($vo['group_price'], "-");
        }
        return $data;  
    }


    /*拼团订单详情*/
	public function detail(){
        $id=post("id");
        (new IDMustBeRequire())->goCheck();
        //订单
		$where['id']=$id;
		$data['order'] = (new \app\model\order())->have($where);
        empty($data['order']) && error('订单不存在',10007);
        if($data['order']['is_pay']==0){
            error('订单未支付',10007);
        }
        if($data['order']['types']!=4){
            error('不是拼团订单',10007);
        }


        //商品
        $p_where['id']=$data['order']['cid'];
        $p_where['show'] = 1;
        $p_where['is_check'] = 1;
        $data['product'] = $this->productM->find($p_where);
        empty($data['product']) && error('商品不存在',10007);	
    	if($data['product']['sku_json']){$data['product']['sku_json'] = json_decode($data['product']['sku_json'],true);}
        $data['product']['attr']=(new \app\model\product_attr())->show_attr($data['product']['id']);

        //拼团
        $data['groups']=(new \app\model\groups())->have(['oid'=>$id]);
        $data['groups']['lists']=(new \app\model\groups())->lists_all(['head_oid'=>$data['groups']['head_oid'],'is_pay'=>1]);
        foreach($data['groups']['lists'] as &$vo){
            $users=user_info($vo['uid']);
            $vo['nickname']=$users['nickname']?$users['nickname']:$users['username'];
            $vo['avatar']=$users['avatar'];
            $vo['end_time_second']=$vo['end_time']-time();
            $vo['difference']=$vo['group_people']-$vo['now_people'];
        }
            
		return $data;
	}


}