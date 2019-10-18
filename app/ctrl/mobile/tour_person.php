<?php 
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-09-18 11:19:58
 * Desc: 旅游电商之出行人
 */
namespace app\ctrl\mobile;

use app\validate\TourPersonValidate;

class tour_person extends BaseController{

	public $tour_person_M;
	public function __initialize(){
		$this->tour_person_M = new \app\model\tour_person();
	}

	/*保存*/
	public function saveadd(){
		(new TourPersonValidate())->goCheck('scene_saveadd');
		$data = post(['name','sfz','tel']);
		$data['uid'] = $GLOBALS['user']['id'];
		$res=$this->tour_person_M->save($data);
		empty($res) && error('添加失败',400);	 
		return $res;
	}

	/*按id删除*/
	public function del(){
		$id_str = post('id_str');
		(new \app\validate\DelValidate())->goCheck();
		$id_ar = explode('@',$id_str);
		$uid = $GLOBALS['user']['id'];
		foreach($id_ar as $id){
			$check_uid = $this->tour_person_M->find($id,'uid');
			if($check_uid!=$uid){error('只能删除自已设置的出行人',400);}
		}
		$res=$this->tour_person_M->del($id_ar);
		empty($res) && error('删除失败',400);
		return $res;
	}

	/*按id修改*/
	public function saveedit()
	{	
		(new TourPersonValidate())->goCheck('scene_saveedit');
		(new \app\validate\IDMustBeRequire())->goCheck();
		$id = post('id');	
		$uid = $GLOBALS['user']['id'];
		$check_uid = $this->tour_person_M->find($id,'uid');
		if($check_uid!=$uid){error('只能修改自已设置的出行人',400);}
    	$data = post(['name','sfz','tel']);
		$res=$this->tour_person_M->up($id,$data);
		empty($res) && error('修改失败',404);
 		return $res; 
	}

	/*单个人的*/
	public function person(){
		(new \app\validate\IDMustBeRequire())->goCheck();
		$id = post('id');
		$ar = $this->tour_person_M->find($id);
		return $ar;
	}


	/*查自已设置的所有出行人*/
	public function lists()
	{
		$where = [];
		$uid = $GLOBALS['user']['id'];
		$where['uid'] = $uid;
		$data=$this->tour_person_M->lists_all($where);
        return $data; 
	}
  

}