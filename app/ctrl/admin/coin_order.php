<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-05-06 20:18:27
 * Desc: 交易订单 (有分页，无添加，无删除，只查看)
 */
namespace app\ctrl\admin;

use app\model\coin_order as CoinOrderModel;
use app\ctrl\admin\BaseController;
use app\validate\IDMustBeRequire;
use app\validate\DelValidate;

class coin_order extends BaseController{
	
	public $coin_order_M;
	public function __initialize(){
		$this->coin_order_M = new CoinOrderModel();
	}

	/*按id查找*/
    public function edit(){
    	$id = post('id');
    	(new IDMustBeRequire())->goCheck();
    	$data = $this->coin_order_M->find($id);

		$users=user_info($data['uid']);
		$data['uid_cn'] = $users['username'];
		$data['nickname'] = $users['nickname'];
		if($data['status']==1){
			$data['status_cn'] = '已支付';
		}else{
			$data['status_cn'] = '已完成';
		}



    	empty($data) && error('数据不存在',404);    	
        return $data;      
    }	

	/*分页列表*/
	public function lists()
	{
		(new \app\validate\AllsearchValidate())->goCheck();
		(new \app\validate\PageValidate())->goCheck();
		$where = [];
		$username = post('username');
		$nickname = post('nickname');
		$oid = post('oid');
		$status = post('status');

		if($username){
   				$user_M = new \app\model\user();
  				$where['uid'] = $user_M->find_mf_uid($username);
  		}

  		if($nickname){
   				$user_M = new \app\model\user();
  				$where['uid'] = $user_M->find_mf_uid_plus($nickname);
  		}

  		if($oid){
  			$where['oid[~]'] = $oid;
  		}

  		if($status){
  			$where['status'] = $status;
		}
		if(isset($status) && $status==2){
			$order=['update_time'=>'DESC'];
		}
  	
		$page=post("page",1);
		$page_size = post("page_size",10);
		$data=$this->coin_order_M->lists_sort($page,$page_size,$where,$order);

		foreach($data as &$one){
			$users=user_info($one['uid']);
			$one['uid_cn'] = $users['username'];  
			$one['nickname'] =$users['nickname'];   
			$one['avatar'] =$users['avatar'];   
			$one['coin_rating_cn'] =$users['coin_rating_cn'];
		if($one['status']==1){
				$one['status_cn'] = '已支付';
			}else{
				$one['status_cn'] = '已完成';
			}
		}
		$count = $this->coin_order_M->new_count($where);
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data; 
        // var_dump($this->product_review_M->log());
        // exit();
        return $res; 
	}

//================= 以上是基础方法 ==================

}