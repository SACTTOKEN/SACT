<?php

/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-07-27 14:51:11
 * Desc: 大盘交易
 */

namespace app\ctrl\admin;

class transaction_order extends PublicController
{
    public $transaction_M;
	public function __initialize()
	{
        $this->transaction_order_M=new \app\model\transaction_order();
	}

	/*查某一类*/
	public function lists()
	{
		(new \app\validate\AllsearchValidate())->goCheck();
		(new \app\validate\PageValidate())->goCheck();
		
		$buy_oid = post('buy_oid');
        $buy_username = post('buy_username');	
        $buy_nickname = post('buy_nickname');

        if($buy_oid){
            $where['buy_oid[~]']=$buy_oid;
        }
        if($buy_username){
            $user_M = new \app\model\user();
            $uid = $user_M->find_mf_uid($buy_username);
            $where['buy_uid'] = $uid;
        }
        if($buy_nickname){
            $user_M = new \app\model\user();
            $uid = $user_M->find_mf_uid_plus($buy_nickname);
            $where['buy_uid'] = $uid;
        }
    
		$sell_oid = post('sell_oid');
        $sell_username = post('sell_username');	
        $sell_nickname = post('sell_nickname');

        if($sell_oid){
            $where['sell_oid[~]']=$sell_oid;
        }
        if($sell_username){
            $user_M = new \app\model\user();
            $uid = $user_M->find_mf_uid($sell_username);
            $where['sell_uid'] = $uid;
        }
        if($sell_nickname){
            $user_M = new \app\model\user();
            $uid = $user_M->find_mf_uid_plus($sell_nickname);
            $where['sell_uid'] = $uid;
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
        $data=$this->transaction_order_M->lists_sort($page,$page_size,$where,$order);
        foreach($data as &$vo){
            if($vo['iden']=='PTB'){
                $vo['iden']=c('coin_title');
            }
            $buy_users=user_info($vo['buy_uid']);
			$vo['buy_username']  = $buy_users['username']; //会员账号
			$vo['buy_nickname']  = $buy_users['nickname']; //会员账号
			$vo['buy_avatar']  =  $buy_users['avatar']; 
            $sell_users=user_info($vo['sell_uid']);
			$vo['sell_username']  = $sell_users['username']; //会员账号
			$vo['sell_nickname']  = $sell_users['nickname']; //会员账号
			$vo['sell_avatar']  =  $sell_users['avatar']; 
        }
		$count = $this->transaction_order_M->new_count($where);
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data; 
        return $res; 
    }
    


}
