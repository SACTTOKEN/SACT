<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-04-27 15:21:54
 * Desc: 币价管理
 */
namespace app\ctrl\admin;

use app\model\coin_price as CoinRechargeModel;
use app\ctrl\admin\BaseController;
use app\validate\IDMustBeRequire;
use app\validate\DelValidate;
use app\validate\CoinPriceValidate;

class coin_price extends BaseController{
	
	public $coin_p_M;
	public function __initialize(){
		$this->coin_p_M = new CoinRechargeModel();
	}

	/*按id查找*/
    public function edit(){
    	$id = post('id');
    	(new IDMustBeRequire())->goCheck();
    	$data = $this->coin_p_M->find($id);
    	$data['price'] = sprintf('%.2f',$data['price']);
    	empty($data) && error('数据不存在',404);    	
        return $data;      
    }	

    /*保存*/
	public function saveadd(){
		(new CoinPriceValidate())->goCheck('scene_saveadd');
		$data = post(['effective_time','price','circulation_coin','user_number','user_coin']);
		$res=$this->coin_p_M->save($data);
		empty($res) && error('添加失败',400);	 		
		admin_log('添加虚拟币价格',$res);  
		return $res;
	}

	/*按id删除*/
	public function del(){
		$id_str = post('id_str');
		(new DelValidate())->goCheck();
		$id_ar = explode('@',$id_str);
		$res=$this->coin_p_M->del($id_ar);
		empty($res) && error('删除失败',400);		
		admin_log('删除虚拟币价格',$id_str);  
		return $res;
	}

	/*按id修改*/
	public function saveedit()
	{	
		(new CoinPriceValidate())->goCheck('scene_saveedit');
		$id = post('id');	
    	$data = post(['effective_time','price','circulation_coin','user_number','user_coin']);
		$res=$this->coin_p_M->up($id,$data);
		// var_dump($this->coin_p_M->log());
		// exit();
		empty($res) && error('修改失败',404);	
		admin_log('修改虚拟币价格',$id);  
 		return $res; 
	}

	/*查某一类*/
	public function lists()
	{
		(new \app\validate\AllsearchValidate())->goCheck();
		(new \app\validate\PageValidate())->goCheck();
		$where = [];
		$created_time_begin = post('created_time_begin');
		$created_time_end = post('created_time_end');
		$created_time_end = intval($created_time_end) + 3600*24;

		if($created_time_begin){
			$where['effective_time[<>]'] = [$created_time_begin,$created_time_end];
		}
		$price = post('price');
		if($price){
			$where['price'] = $price;
		}



		$page=post("page",1);
		$page_size = post("page_size",10);
		$data=$this->coin_p_M->lists($page,$page_size,$where);

		$count = $this->coin_p_M->new_count($where);
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data; 
        // var_dump($this->product_review_M->log());
        // exit();
        return $res; 
	}

//================= 以上是基础方法 ==================

}