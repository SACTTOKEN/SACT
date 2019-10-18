<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-07-09 14:15:25
 * Desc: 签到控制器
 */

namespace app\ctrl\admin;

use app\model\sign_in as SignInModel;
use app\ctrl\admin\BaseController;
use app\validate\IDMustBeRequire;

class sign_in extends BaseController{
	
	public $sign_in_M;
	public function __initialize(){
		$this->sign_in_M = new SignInModel();
	}


}