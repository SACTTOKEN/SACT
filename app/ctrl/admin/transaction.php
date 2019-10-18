<?php

/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-07-27 14:51:11
 * Desc: 大盘交易
 */

namespace app\ctrl\admin;

class transaction extends PublicController
{
    public $transaction_M;
	public function __initialize()
	{
        $this->transaction_M=new \app\model\transaction();
	}

	/*查某一类*/
	public function lists()
	{
		(new \app\validate\AllsearchValidate())->goCheck();
		(new \app\validate\PageValidate())->goCheck();
		
		$oid = post('oid');
		$types = post('types');
        $status = post('status');
        $username = post('username');	
        $nickname = post('nickname');
        
        if($username){
            $user_M = new \app\model\user();
            $uid = $user_M->find_mf_uid($username);
            $where['uid'] = $uid;
        }
        if($nickname){
            $user_M = new \app\model\user();
            $uid = $user_M->find_mf_uid_plus($nickname);
            $where['uid'] = $uid;
        }
    
        if($oid){
            $where['oid[~]']=$oid;
        }
		if(is_numeric($types)){
			$where['types'] = $types;
		}
		if(is_numeric($status)){
			$where['status'] = $status;
		}
        
        $created_time_begin=post('created_time_begin');
        $created_time_end=post('created_time_end');
		if(is_numeric($created_time_begin)){
			$created_time_end = $created_time_end ? $created_time_end : time();
			$created_time_end = $created_time_end + 3600*24;
        	$where['created_time[<>]'] = [$created_time_begin,$created_time_end]; 	
        }

		$page=post("page",1);
		$page_size = post("page_size",10);		
		$order=['id'=>'DESC'];
        $data=$this->transaction_M->lists_sort($page,$page_size,$where,$order);
        foreach($data as &$vo){
            if($vo['iden']=='PTB'){
                $vo['iden']=c('coin_title');
            }
            $users=user_info($vo['uid']);
			$vo['username']  = $users['username']; //会员账号
			$vo['nickname']  = $users['nickname']; //会员账号
			$vo['avatar']  =  $users['avatar']; 
        }
		$count = $this->transaction_M->new_count($where);
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data; 
        return $res; 
    }
    


}
