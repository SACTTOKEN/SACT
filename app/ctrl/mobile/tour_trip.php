<?php 
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-09-18 11:19:58
 * Desc: 旅游电商之出行程
 */
namespace app\ctrl\mobile;

use app\validate\TourPersonValidate;

class tour_trip extends BaseController{

	public $tour_trip_M;
	public function __initialize(){
		$this->tour_trip_M = new \app\model\tour_trip();
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