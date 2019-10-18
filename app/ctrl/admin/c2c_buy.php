<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-05-06 20:18:27
 * Desc: 交易订单 (有分页，无添加，无删除，只查看)
 */
namespace app\ctrl\admin;

use app\model\c2c_buy as C2cBuyModel;
use app\validate\IDMustBeRequire;

class c2c_buy extends BaseController{
	
	public $c2c_buy_M;
	public function __initialize(){
		$this->c2c_buy_M = new C2cBuyModel();
	}

	/*按id查找*/
    public function edit(){
    	$id = post('id');
    	(new IDMustBeRequire())->goCheck();
		$data = $this->c2c_buy_M->find($id);
		$users=user_info($data['uid']);
    	$data['uid_cn'] = $users['username'];
    	$data['nickname'] = $users['nickname'];

    	$data['fee'] = sprintf('%.2f',$data['fee']);
    	$data['price'] = sprintf('%.2f',$data['price']);
    	$data['money'] = sprintf('%.2f',$data['money']);

		if($data['status']==1){
			$data['status_cn'] = '已支付';
		}else{
			$data['status_cn'] = '已完成';
		}
		
		if($data['types']==1){
		$data['types_cn'] = '买';
		}
		if($data['types']==2){
		$data['types_cn'] = '卖';
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

		$oid = post('oid');
		$username = post('username');
		$nickname = post('nickname');
		$status = post('status');
		$types = post('types');
		$coin = post('coin');
		$created_time_begin = post('created_time_begin');
		$created_time_end = post('created_time_end');

		if($oid){
			$where['oid[~]'] = $oid;
		}
	
		if($username){
   				$user_M = new \app\model\user();
  				$where['uid'] = $user_M->find_mf_uid($username);
  		}

  		if($nickname){
   				$user_M = new \app\model\user();
  				$where['uid'] = $user_M->find_mf_uid_plus($nickname);
  		}
  		if($status){
  			$where['status'] = $status;
  		}
  		if($types){
  			$where['types'] = $types;
		}
		if($coin){
			$user_M = new \app\model\user();
		    $where['uid'] = $user_M->list_where(['coin_rating'=>$coin]);
		}

		if(is_numeric($created_time_begin)){
			$created_time_end = $created_time_end ? $created_time_end : time();
			$created_time_end = $created_time_end + 3600*24;
        	$where['AND']['created_time[<>]'] = [$created_time_begin,$created_time_end]; 	
        }     

		$page=post("page",1);
		$page_size = post("page_size",10);
		$data=$this->c2c_buy_M->lists($page,$page_size,$where);

		
		foreach($data as &$one){
			$one['money']	= sprintf('%.2f',$one['money']);
			$one['fee']	= sprintf('%.2f',$one['fee']);
			$one['price']	= sprintf('%.2f',$one['price']);

			
			$users=user_info($one['uid']);
		
			$one['uid_cn'] = $users['username'];  
			$one['nickname'] =$users['nickname'];   
			$one['avatar'] =$users['avatar'];   
			$one['coin_rating_cn'] = $users['coin_rating_cn'];
		
			if($one['status']==1){
				$one['status_cn'] = '发布中';
			}elseif($one['status']==2){
				$one['status_cn'] = '交易中';
			}elseif($one['status']==3){
				$one['status_cn'] = '已完成';
			}elseif($one['status']==4){
				$one['status_cn'] = '已撤销';
			}
			if($one['types']==1){
			$one['types_cn'] = '买';
			}
			if($one['types']==2){
			$one['types_cn'] = '卖';
			}
			$one['coin'] = $users['coin'];
			$one['coin_storage'] = $users['coin_storage'];

			$one['integrity'] = $users['integrity']; //诚信值
			$one['coin_buy'] =  $users['coin_buy']; //币累计消费
			$one['admin_remark'] =  $users['admin_remark']; 

		}
		unset($one);
		
		$count = $this->c2c_buy_M->new_count($where);
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data; 
       
        return $res; 
	}


	/* 撤销 */
	public function cancel()
	{
		$id = post('id');
		(new IDMustBeRequire())->goCheck();
		$where['id']=$id;
		$where['status']=1;
    	$res=$this->c2c_buy_M->have($where);
		empty($res) && error('交易中或已撤销',400); 
		$data['status']=4;
		$res=$this->c2c_buy_M->up($id,$data);
		empty($res) && error('撤销失败',404);
		admin_log('管理员撤销c2c求购',$id);  
 		return $res; 
	}
}