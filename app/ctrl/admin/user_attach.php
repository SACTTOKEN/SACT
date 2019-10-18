<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-24 13:48:22
 * Desc: 用户副表控制器
 */

namespace app\ctrl\admin;

use app\model\user_attach as user_attach_Model;
use app\ctrl\admin\BaseController;
use app\validate\UserAttachValidate;
use app\validate\IDMustBeRequire;

class user_attach extends BaseController{
	
	public $user_attach_M;
	public function __initialize(){
		$this->user_attach_M = new user_attach_Model();
	}

	/*按uid查找*/
    public function edit(){
    	$uid = post('uid');
    	(new UserAttachValidate())->goCheck('scene_find');
    	$data = $this->user_attach_M->find($uid);
    	empty($data) && error('数据不存在',404);    	
        return $data;     
    }	

 
	

	/*按uid修改*/
	public function saveedit()
	{	
		$uid = post('uid');
    	(new UserAttachValidate())->goCheck('scene_find');
    	$data = post(['name','alipay']);
		$res=$this->user_attach_M->up($uid,$data);
		empty($res) && error('修改失败',404);
		admin_log('修改用户信息',$uid);    
 		return $res; 
	}

//================= 以上是基础方法 ==================

		/*收款信息*/
	public function collections_edit(){
    	(new IDMustBeRequire())->goCheck();
		$uid = post('id');
		$data = $this->user_attach_M->find_collections($uid);
		return $data;
	}

	/*收款信息保存*/
	public function collections_saveedit(){
		(new UserAttachValidate())->goCheck('scene_collections_edit');
		$uid = post('id');
		$data['alipay'] = post('alipay');
		$data['alipay_name'] = post('alipay_name');
		$data['wechat'] = post('wechat');
		$data['bank_card'] = post('bank_card');
		$data['bank_name'] = post('bank_name');
		$data['bank'] = post('bank');
		$data['bank_network'] = post('bank_network');
		$data['bank_province'] = post('bank_province');
		$data['bank_city'] = post('bank_city');
		$data['alipay_pic'] = post('alipay_pic');
		$data['wechat_pic'] = post('wechat_pic');

		$res = $this->user_attach_M->up($uid,$data);
		admin_log('修改用户收款信息',$uid);   
		return $res;
	}





}