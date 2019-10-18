<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-18 13:50:53
 * Desc: 支付接口控制器
 */

namespace app\ctrl\supplier;

use app\validate\IDMustBeRequire;
use app\model\pay as PayModel;

class pay extends BaseController{
	
	public $payM;
	public function __initialize(){
		$this->payM = new PayModel();
	}

	
	/*配置列表 无分页*/
	public function lists()
	{				
		$data=$this->payM->lists_all();
        return $data; 
	}


}