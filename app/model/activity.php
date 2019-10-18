<?php

/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-07-27 14:51:11
 * Desc: 限时抢购
 */

namespace app\ctrl\mobile;
use app\model\product as productModel;
use app\validate\IDMustBeRequire;
use app\validate\ActivityValidate;

class activity extends PublicController
{

	public $rob_time_M;
	public $productM;
	public $product_S;
	public function __initialize()
	{
		$this->productM = new ProductModel();
    }
    
    public function limited_time()
    {
        $rob_time_M = new \app\model\rob_time();
        $where['end_time[>]'] = time();
        $data = $rob_time_M->lists_all($where);
        foreach($data as &$vo){
            if($vo['begin_time']>time()){
                $vo['types']='未开始';
                $vo['distance_begin_time']=$vo['begin_time']-time();
            }else{
                $vo['types']='已开始';
                $vo['distance_end_time']=$vo['end_time']-time();
            }
        }
        return $data;
    }

    public function limited_time_lists()
    {
        (new IDMustBeRequire)->goCheck();
        (new \app\validate\PageValidate())->goCheck();      
        $page=post("page",1);
        $page_size = post("page_size",10);
        $time_id = post("id");
      
        $where['time_id'] = $time_id;
        $where['types'] = 7;
        $where['show'] = 1;
		$where['is_check'] = 1;
		$where['discount_limit[>]'] = 0;
        $data = $this->productM->lists_by_mobile($page,$page_size,$where);  
        foreach($data as &$vo){
            $vo['limited_price']='';
            $price = explode("-", $vo['price']);
            if (is_array($price)) {
                foreach ($price as $vos) {
                    if ($vos) {
                            $vo['limited_price'] .= sprintf("%.2f",($vos*($vo['discount_rob']/10))) . '-';
                    }
                }
            }
            $vo['limited_price'] = rtrim($vo['limited_price'], "-");
            $vo['sold']=($vo['real_sale']/$vo['discount_limit'])*100;
        }
        return $data;  
    }

    public function redeem_lists()
    {
        (new IDMustBeRequire)->goCheck();
        (new \app\validate\PageValidate())->goCheck();      
        $page=post("page",1);
        $page_size = post("page_size",10);
        
        $where['types'] = 2;
        $where['show'] = 1;
		$where['is_check'] = 1;
		$where['score_rob[>]'] = 0;
        $data = $this->productM->lists_by_mobile($page,$page_size,$where);  
        return $data;  
    }



}