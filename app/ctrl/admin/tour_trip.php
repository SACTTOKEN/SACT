<?php 
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-09-18 11:19:58
 * Desc: 旅游电商之出行程
 */
namespace app\ctrl\admin;

use app\validate\TourTripValidate;

class tour_trip extends BaseController{

	public $tour_trip_M;
	public function __initialize(){
		$this->tour_trip_M = new \app\model\tour_trip();
	}

	/*保存*/
	public function saveadd(){
		(new TourTripValidate())->goCheck('scene_saveadd');
		$data = post(['pid','trip_day','trip_time','trip_title','trip_content','trip_id','trip_pic']);
		$res=$this->tour_trip_M->save($data);
		empty($res) && error('添加失败',400);	 
		return $res;
	}


	/*修改*/
	public function saveedit(){
		(new \app\validate\IDMustBeRequire())->goCheck();
		$id = post('id');
		$data = post(['trip_day','trip_time','trip_title','trip_content','trip_id','trip_pic']);
		cs($data,1);

		$res=$this->tour_trip_M->up($id,$data);
		empty($res) && error('添加失败',400);	 
		return $res;
	}

	/*删除*/
	public function del(){
		(new \app\validate\DelValidate())->goCheck();
		$id_str = post('id_str');
		$id_ar = explode('@',$id_str);
		foreach($id_ar as $id){
			$is_have = $this->tour_trip_M->is_have(['trip_id'=>$id]);
			if($is_have){error('请先删除当日时间点的行程',400);}
		}
		$res=$this->tour_trip_M->del($id_ar);
		empty($res) && error('删除失败',400);
		return $res;
	}


	/*列表*/
	public function lists(){
		(new \app\validate\IDMustBeRequire())->goCheck();
		$id = post('id'); //商品ID, 旅游项目ID
		$where['pid'] = $id;
		$where['trip_id'] = 0;
		$where['ORDER'] = ['trip_day'=>'ASC'];
		$ar = $this->tour_trip_M->lists_all($where);
		foreach($ar as &$one){
			$ar2 = $this->tour_trip_M->lists_all(['trip_id'=>$one['id'],'ORDER'=>['trip_time'=>'ASC']]);
			$one['today'] = $ar2;
		}
		unset($one);
		return $ar;
	}


	




	
  

}