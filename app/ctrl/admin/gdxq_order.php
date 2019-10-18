<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-05-06 20:18:27
 * Desc: vip订单 (有分页，无添加，无删除，只查看)
 */
namespace app\ctrl\admin;

use app\model\gdxq_order as Mxq_orderModel;
use app\ctrl\admin\BaseController;
use app\validate\IDMustBeRequire;
use app\validate\DelValidate;

class gdxq_order extends BaseController{
	
	public $gdxq_order_M;
	public function __initialize(){
		$this->gdxq_order_M = new Mxq_orderModel();
	}

	/*按id查找*/
    public function edit(){
    	$id = post('id');
    	(new IDMustBeRequire())->goCheck();
    	$data = $this->gdxq_order_M->find($id);

		$users=user_info($data['uid']);
		$data['uid_cn'] = $users['username'];
		$data['nickname'] = $users['nickname'];
		if($data['status']==1){
			$data['status_cn'] = '成功';
		}else if($data['status']==2){
			$data['status_cn'] = '失败';
		}else{
			$data['status_cn'] = '攻打中';
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
		
		$created_time_begin = post('created_time_begin');
		$created_time_end = post('created_time_end');
		$created_time_end = intval($created_time_end);
		
		if($created_time_begin){
  			$created_time_end = $created_time_end + 3600*24;
			$where['rd_time[<>]'] = [$created_time_begin,$created_time_end];
		}
  	
		$page=post("page",1);
		$page_size = post("page_size",10);
		$data=$this->gdxq_order_M->lists_sort($page,$page_size,$where,$order);

		foreach($data as &$one){
			$users=user_info($one['uid']);
			$one['uid_cn'] = $users['username'];  
			$one['nickname'] =$users['nickname'];   
			$one['avatar'] =$users['avatar'];   
			
		
		if($one['status']==1){
				$one['status_cn'] = '成功';
			}
			else if($one['status']==2){
				$one['status_cn'] = '失败';
			}
			else{
				$one['status_cn'] = '攻打中';
			}
		}
		$count = $this->gdxq_order_M->new_count($where);
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