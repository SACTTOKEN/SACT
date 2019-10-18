<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-16 13:51:24
 * Desc: 收货地址
 */
namespace app\ctrl\mobile;
use app\ctrl\mobile\BaseController;
use app\model\user_address as UserAddressModel;
use app\validate\IDMustBeRequire;

class address extends BaseController
{
	public $user_address_M;

	public function __initialize(){
		$this->user_address_M = new UserAddressModel();
	}


	public function add_address(){
		(new \app\validate\UcenterValidate())->goCheck('scene_add_address');
		$uid = $GLOBALS['user']['id'];
		$data = post(['name','tel','province','city','area','town','address','is_show','sort','areaCode']);
		$data['uid'] = $uid;
		$res = $this->user_address_M->save($data);	
		empty($res) && error('添加失败',400);
		if(isset($data['is_show']) && $data['is_show']==1){
			$where['uid']=$uid;
			$where['id[!]']=$res;
			$this->user_address_M->up_all($where,['is_show'=>0]);	
		}
		$adderss=$this->user_address_M->find($res);
 		return $adderss;	
	}


	public function edit_address(){
		(new IDMustBeRequire())->goCheck();
		$uid = $GLOBALS['user']['id'];
		$id = post('id'); //user_address表id
		$data = post(['name','tel','province','city','area','town','address','is_show','sort','areaCode']);
		$res = $this->user_address_M->up($id,$data);	
		empty($res) && error('修改失败',400);
		if(isset($data['is_show']) && $data['is_show']==1){
			$where['uid']=$uid;
			$where['id[!]']=$id;
			$this->user_address_M->up_all($where,['is_show'=>0]);	
		}
		$adderss=$this->user_address_M->find($id);
 		return $adderss;	
	}


	public function del_address(){
		(new IDMustBeRequire())->goCheck();
		$uid = $GLOBALS['user']['uid'];
		$id = post('id'); //user_address表id
		$res = $this->user_address_M->del($id,$uid);
		empty($res) && error('删除失败',400);
 		return $res;
	}


	public function show_address(){
		$uid = $GLOBALS['user']['id'];
		$res = $this->user_address_M->lists_all(["uid"=>$uid,'ORDER'=>["sort"=>"DESC","id"=>"DESC"]]);
 		return $res;
	}


}
