<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-05-06 19:21:58
 * Desc: 区块链主流币
 */

namespace app\ctrl\admin;

use app\model\coin_currency as CoinCurrencyModel;
use app\ctrl\admin\BaseController;
use app\validate\IDMustBeRequire;


class coin_currency extends BaseController{
	
	public $coin_cu_M;
	public function __initialize(){
		$this->coin_cu_M = new CoinCurrencyModel();
	}

	/*按id查找*/
    public function edit(){    	
    	$id = post('id');
    	(new IDMustBeRequire())->goCheck();
    	$data = $this->coin_cu_M->find($id);
    	empty($data) && error('数据不存在',404);
        return $data;      
    }	


	/*查某一类*/
	public function lists()
	{
		$data=$this->coin_cu_M->lists_all();
        return $data; 
	}

//================= 以上是基础方法 ==================


	

}