<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-24 13:48:22
 * Desc: 用户收货地址控制器
 */

namespace app\ctrl\admin;

use app\model\user_address as user_address_Model;
use app\ctrl\admin\BaseController;
use app\validate\UserAddressValidate;

class user_address extends BaseController{
	
	public $user_address_M;
	public function __initialize(){
		$this->user_address_M = new user_address_Model();
	}


    /*查用户所有分类地址*/
	public function lists()
	{
		$uid = post('uid');
		(new UserAddressValidate())->goCheck('scene_list');
		$data=$this->user_address_M->lists_all($uid);
        return $data; 
	}	


//================= 以上是基础方法 ==================

	





}