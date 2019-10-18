<?php

/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-07-27 14:51:11
 * Desc: dapp订单
 */

namespace app\ctrl\admin;
use \app\validate\IDMustBeRequire;

class dapp_order extends BaseController
{
    public $dapp_order_M;
	public function __initialize()
	{
        $this->dapp_order_M=new \app\model\dapp_order();
	}

	/*查某一类*/
	public function lists()
	{
		(new \app\validate\AllsearchValidate())->goCheck();
		(new \app\validate\PageValidate())->goCheck();
        $username 			= post('username');
		$nickname			= post('nickname');
		$oid			= post('oid');
		$status			= post('status');
		$created_time_begin = post('created_time_begin');
        $created_time_end = post('created_time_end');
        $where=[];
		if ($username) {
			$user_M = new \app\model\user();
			$where['uid'] = $user_M->find_mf_uid($username);
		}
		if ($nickname) {
			$user_M = new \app\model\user();
			$where['uid'] = $user_M->find_mf_uid_plus($nickname);
		}
		if($oid){
			$where['oid[~]']=$oid;
		}
		if (is_numeric($status)) {
			$where['status'] = $status;
		}
        if(is_numeric($created_time_begin)){
			$created_time_end = $created_time_end ? $created_time_end : time();
			$created_time_end = $created_time_end + 3600*24;
        	$where['created_time[<>]'] = [$created_time_begin,$created_time_end]; 	
        }

		$page=post("page",1);
		$page_size = post("page_size",10);		
		$order=['id'=>'DESC'];
        $data=$this->dapp_order_M->lists_sort($page,$page_size,$where,$order);
        foreach($data as &$vo){
            $users=user_info($vo['uid']);
            $vo['nickname']=$users['nickname'];
            $vo['username']=$users['username'];
            $vo['avatar']=$users['avatar'];
        }
        $count = $this->dapp_order_M->new_count($where);
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data; 
        return $res; 
    }
  

	/*详情*/
	public function edit()
	{
		$id = post('id');
    	(new IDMustBeRequire())->goCheck();
    	$data = $this->dapp_order_M->find($id);
    	empty($data) && error('数据不存在',404);    	
        return $data;    
	}

   
    

}
