<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-03 15:16:08
 * Desc: 物流地区类
 */
namespace app\ctrl\supplier;

use app\model\mail_area as MailAreaModel;
use app\validate\IDMustBeRequire;
use app\validate\MailAreaValidate;

class mail_area extends BaseController{
	public $mail_area_M;
	public function __initialize(){
		$this->mail_area_M = new MailAreaModel();
	}

    /*保存*/
	public function saveadd(){
		$data = post(['cid','province','first_weight','continued_weight']);
		(new MailAreaValidate())->goCheck('scene_add');
		$res=$this->mail_area_M->save($data);
		$new_id = $this->mail_area_M->id();
		empty($res) && error('添加失败',400);	 
        admin_log('添加物流地区',$res);    
		return $new_id;
	}

	/*按id删除*/
	public function del(){
		$id = post('id');
		(new MailAreaValidate())->goCheck('scene_find');
		$res=$this->mail_area_M->del($id);
		empty($res) && error('删除失败',400);
        admin_log('删除物流地区',$id);    
		return $res;
	}

	/*修改*/
	public function edit()
	{
		(new IDMustBeRequire())->goCheck();
		$id=post("id");
		$res=$this->mail_area_M->find($id);
		empty($res) && error('数据不存在',404); 
		return $res;
	}

	/*按id修改*/
	public function saveedit()
	{
		$id = post('id');
    	(new MailAreaValidate())->goCheck('scene_find');
    	$data = post(['cid','province','first_weight','continued_weight']);
		$res=$this->mail_area_M->up($id,$data);
		empty($res) && error('修改失败',404);		
        admin_log('修改物流地区',$id);    
 		return $res; 
	}

	public function lists(){
		$cid = post('cid','');
		(new MailAreaValidate())->goCheck('scene_lists');
		$data = $this->mail_area_M->lists_all($cid);
		foreach($data as &$rs){
			$rs['show'] = 0;
			$rs['first_weight'] = sprintf("%.2f",$rs['first_weight']);
			$rs['continued_weight'] = sprintf("%.2f",$rs['continued_weight']);
		}
		return $data;

	}

//================= 以上是基础方法 ==================

	

}