<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2018-12-18 13:50:53
 * Desc: 优惠券（红包）控制器
 */

namespace app\ctrl\supplier;

use app\model\coupon as CouponModel;
use app\validate\IDMustBeRequire;
use app\validate\DelValidate;

class coupon extends BaseController{
	
	public $coupon_M;
	public function __initialize(){
		$this->coupon_M = new CouponModel();
	}

	/*按id查找*/
    public function edit(){
    	$id = post('id');
    	(new IDMustBeRequire())->goCheck();
    	$data = $this->coupon_M->have(['id'=>$id,'sid'=>$GLOBALS['user']['id']]);
    	empty($data) && error('数据不存在',404);    	
        return $data;      
    }	


	/*按id删除*/
	public function del(){
		(new DelValidate())->goCheck();
		$id_str = post('id_str');
		$id_ar = explode('@',$id_str);
		foreach($id_ar as $vo){
			if($vo){
				$data = $this->coupon_M->have(['id'=>$vo,'sid'=>$GLOBALS['user']['id']]);
				empty($data) && error('数据不存在',404);    	
				$res=$this->coupon_M->del($vo);
				empty($res) && error('删除失败',400);
			}
		}
		admin_log('删除优惠券红包',$id_str);  
		return $res;
	}


	/*查某一类*/
	public function lists()
	{
		(new \app\validate\AllsearchValidate())->goCheck();
		(new \app\validate\PageValidate())->goCheck();
		$where['sid'] = $GLOBALS['user']['id'];
		$page=post("page",1);
		$page_size = post("page_size",10);

		$data=$this->coupon_M->lists($page,$page_size,$where);

		foreach($data as &$one){
			$users=user_info($one['uid']);
			$one['username'] = $users['username'];
			$users=user_info($one['sid']);
			$one['sid_cn'] = $users['username'];

			if($one['is_use']==1){
				$one['is_use_cn'] = "已使用";
			}else{
				$one['is_use_cn'] = "未使用";
			}
			
		}
		unset($one);

		$count = $this->coupon_M->new_count($where);
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data;
        return $res; 
	}


}