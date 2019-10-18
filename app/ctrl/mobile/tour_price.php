<?php 
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-09-18 11:19:58
 * Desc: 旅游电商之价格
 */
namespace app\ctrl\mobile;

use app\validate\TourPriceValidate;

class tour_price extends BaseController{

	public $tour_price_M;
	public function __initialize(){
		$this->tour_price_M = new \app\model\tour_price();
	}

	/*日历价格列表*/
	public function lists(){
		$m = post('month'); //201909
		$m_len = mb_strlen($m);
		if($m_len!=6){error('月份格式不正确',400);}
		$pid = post('pid');
		$m1 = substr($m,0,4);
		$m2 = substr($m,4,2);

		$big_day = date('t',strtotime($m1.'-'.$m2.'-01'));
		$pro_M = new \app\model\product();
		$my_price = $pro_M->find($pid,'price'); //成人默认价格
		$my_baby_price = $pro_M->find($pid,'baby_price'); //小孩子默认价格
		$res = [];
		$where['pid'] = $pid;
		$where['day[~]'] = $m;
		$where['ORDER'] = ['day'=>'ASC'];
		$ar = $this->tour_price_M->lists_all($where);
		for($i=1;$i<=$big_day;$i++){
			if($i<10){$i = '0'.$i;}
			$day = $m.$i;
			$price = 0;
			$baby_price = 0;
			$id = 0;
			foreach($ar as $one){
				if($one['day'] == $day){
					$id = $one['id'];
					$price = $one['price'];
					$cost_price = $one['cost_price'];
					$baby_price = $one['baby_price'];
					$baby_cost_price = $one['baby_cost_price'];
					break;
				}
			}
			if($price == 0){
				$price = $my_price;
				$cost_price = $my_price;
			}

			if($baby_price==0){
				$baby_price  = $my_baby_price;
				$baby_cost_price = $my_baby_price;
			}

			$price_all['id'] = $id;
			$price_all['price'] = $price;
			$price_all['cost_price'] = $cost_price;
			$price_all['baby_price'] = $baby_price;
			$price_all['baby_cost_price'] = $baby_cost_price;

			$res[$day] = $price_all;
		}
		return $res;
	}

  

}