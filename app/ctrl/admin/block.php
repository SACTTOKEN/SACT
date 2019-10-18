<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-04-27 15:21:54
 * Desc: 币价管理
 */
namespace app\ctrl\admin;
use \app\validate\AllsearchValidate;
use \app\validate\PageValidate;

class block extends PublicController{
	
	public function __initialize(){
	}

	/*主流币列表*/
    public function coin_currency_list(){
		$data=(new \app\model\coin_currency())->lists_all();
        return $data; 
    }	

    #钱包列表
    public function block_wallet_lists()
    {
        (new AllsearchValidate())->goCheck();
		(new PageValidate())->goCheck();
		$where = [];	
		$status = post('status');
		$username = post('username');
		$nickname = post('nickname');
		$cate = post('cate');

		if($username){
        	$user_M = new \app\model\user();
        	$uid_ar = $user_M->find_mf_uid($username);
        	$where['uid'] = $uid_ar;
        }
        if($nickname){
        	$user_M = new \app\model\user();
        	$where['uid'] = $user_M->find_mf_uid_plus($nickname);
        }
        if($cate){
        	$where['cate'] = $cate;
        }
        if(is_numeric($status)){
			$where['status'] = $status;
        }
        
		$distribution_time_begin = post('distribution_time_begin');
		$distribution_time_end = post('distribution_time_end');
		if(is_numeric($distribution_time_begin)){
			$distribution_time_end = $distribution_time_end ? $distribution_time_end : time();
			$distribution_time_end = $distribution_time_end + 3600*24;
        	$where['distribution_time[<>]'] = [$distribution_time_begin,$distribution_time_end]; 	
        }
        
		$effective_time_begin = post('effective_time_begin');
		$effective_time_end = post('effective_time_end');
		if(is_numeric($effective_time_begin)){
			$effective_time_end = $effective_time_end ? $effective_time_end : time();
			$effective_time_end = $effective_time_end + 3600*24;
        	$where['effective_time[<>]'] = [$effective_time_begin,$effective_time_end]; 	
        }
        
		$freed_time_begin = post('freed_time_begin');
		$freed_time_end = post('freed_time_end');
		if(is_numeric($freed_time_begin)){
			$freed_time_end = $freed_time_end ? $freed_time_end : time();
			$freed_time_end = $freed_time_end + 3600*24;
        	$where['freed_time[<>]'] = [$freed_time_begin,$freed_time_end]; 	
        }
        
		$carried_time_begin = post('carried_time_begin');
		$carried_time_end = post('carried_time_end');
		if(is_numeric($carried_time_begin)){
			$carried_time_end = $carried_time_end ? $carried_time_end : time();
			$carried_time_end = $carried_time_end + 3600*24;
        	$where['carried_time[<>]'] = [$carried_time_begin,$carried_time_end]; 	
        }
        
		$carried_time_begin = post('carried_time_begin');
		$carried_time_end = post('carried_time_end');
		if(is_numeric($carried_time_begin)){
			$carried_time_end = $carried_time_end ? $carried_time_end : time();
			$carried_time_end = $carried_time_end + 3600*24;
        	$where['carried_time[<>]'] = [$carried_time_begin,$carried_time_end]; 	
        }
        
        
		$page=post("page",1);
		$page_size = post("page_size",10);
		$data=(new \app\model\block_wallet())->lists($page,$page_size,$where);
		foreach($data as $key=>$rs){
			$user=user_info($rs['uid']);
			$data[$key]['username']  = $user['username']; //会员账号
			$data[$key]['nickname']  = $user['nickname']; //会员账号
			$data[$key]['avatar']  =  $user['avatar'];
			$data[$key]['status'] =  ($rs['status']==1) ? '已分配' : '空闲'; //交易状态 0未支付1支付成功 	
       		
		}
		$count = (new \app\model\block_wallet())->new_count($where);
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data;
        return $res;
    }


    #充值记录
    public function block_recharge_lists()
    {
        (new AllsearchValidate())->goCheck();
		(new PageValidate())->goCheck();
		$where = [];	
		$username = post('username');
		$oid = post('oid');
		$cate = post('cate');
		$nickname = post('nickname');

		if($username){
        	$user_M = new \app\model\user();
        	$uid_ar = $user_M->find_mf_uid($username);
        	$where['uid'] = $uid_ar;
        }
        if($nickname){
        	$user_M = new \app\model\user();
        	$where['uid'] = $user_M->find_mf_uid_plus($nickname);
        }
        if($cate){
        	$where['cate'] = $cate;
        }
        if($oid){
        	$where['oid[~]'] = $oid;
        }
      
		$created_time_begin = post('created_time_begin');
		$created_time_end = post('created_time_end');
		if(is_numeric($created_time_begin)){
			$created_time_end = $created_time_end ? $created_time_end : time();
			$created_time_end = $created_time_end + 3600*24;
        	$where['created_time[<>]'] = [$created_time_begin,$created_time_end]; 	
        }
        
        
		$page=post("page",1);
		$page_size = post("page_size",10);
		$data=(new \app\model\block_recharge())->lists($page,$page_size,$where);
		foreach($data as $key=>$rs){
			$user=user_info($rs['uid']);
			$data[$key]['username']  = $user['username']; //会员账号
			$data[$key]['nickname']  = $user['nickname']; //会员账号
			$data[$key]['avatar']  =  $user['avatar'];
		}
		$count = (new \app\model\block_recharge())->new_count($where);
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data;
        return $res;
    }

    #提币记录
    public function block_withdraw_lists()
    {
        (new AllsearchValidate())->goCheck();
		(new PageValidate())->goCheck();
		$where = [];	
		$username = post('username');
		$oid = post('oid');
		$cate = post('cate');
		$nickname = post('nickname');
		$types = post('types');

		if($username){
        	$user_M = new \app\model\user();
        	$uid_ar = $user_M->find_mf_uid($username);
        	$where['uid'] = $uid_ar;
        }
        if($nickname){
        	$user_M = new \app\model\user();
        	$where['uid'] = $user_M->find_mf_uid_plus($nickname);
        }
        if($cate){
        	$where['cate'] = $cate;
        }
        if($oid){
        	$where['oid[~]'] = $oid;
		}
		if(is_numeric($types)){
			$where['types'] = $types;
		}
      
		$created_time_begin = post('created_time_begin');
		$created_time_end = post('created_time_end');
		if(is_numeric($created_time_begin)){
			$created_time_end = $created_time_end ? $created_time_end : time();
			$created_time_end = $created_time_end + 3600*24;
        	$where['created_time[<>]'] = [$created_time_begin,$created_time_end]; 	
        }
        
        
		$page=post("page",1);
		$page_size = post("page_size",10);
		$data=(new \app\model\block_withdraw())->lists($page,$page_size,$where);
		foreach($data as $key=>$rs){
			$user=user_info($rs['uid']);
			$data[$key]['username']  = $user['username']; //会员账号
			$data[$key]['nickname']  = $user['nickname']; //会员账号
            $data[$key]['avatar']  =  $user['avatar'];
            $data[$key]['types'] =  ($rs['types']==1) ? '用户转出' : '归集';
		}
		$count = (new \app\model\block_withdraw())->new_count($where);
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data;
        return $res;
    }

    #释放记录
    public function block_freed_lists()
    {
        (new AllsearchValidate())->goCheck();
		(new PageValidate())->goCheck();
		$where = [];	
		$username = post('username');
		$cate = post('cate');
		$nickname = post('nickname');

		if($username){
        	$user_M = new \app\model\user();
        	$uid_ar = $user_M->find_mf_uid($username);
        	$where['uid'] = $uid_ar;
        }
        if($nickname){
        	$user_M = new \app\model\user();
        	$where['uid'] = $user_M->find_mf_uid_plus($nickname);
        }
        if($cate){
        	$where['cate'] = $cate;
        }
        
		$created_time_begin = post('created_time_begin');
		$created_time_end = post('created_time_end');
		if(is_numeric($created_time_begin)){
			$created_time_end = $created_time_end ? $created_time_end : time();
			$created_time_end = $created_time_end + 3600*24;
        	$where['created_time[<>]'] = [$created_time_begin,$created_time_end]; 	
        }
        
        
		$page=post("page",1);
		$page_size = post("page_size",10);
		$data=(new \app\model\block_freed())->lists($page,$page_size,$where);
		foreach($data as $key=>$rs){
			$user=user_info($rs['uid']);
			$data[$key]['username']  = $user['username']; //会员账号
			$data[$key]['nickname']  = $user['nickname']; //会员账号
            $data[$key]['avatar']  =  $user['avatar'];
		}
		$count = (new \app\model\block_freed())->new_count($where);
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data;
        return $res;
    }

}