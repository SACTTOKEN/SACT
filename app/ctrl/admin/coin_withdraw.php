<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-04-30 09:37:32
 * Desc: 提币记录
 */

namespace app\ctrl\admin;

use app\model\coin_withdraw as CoinWithdrawModel;
use app\ctrl\admin\BaseController;
use app\validate\IDMustBeRequire;
use app\validate\CoinWithdrawValidate;


class coin_withdraw extends BaseController{
	
	public $coin_wi_M;
	public function __initialize(){
		$this->coin_wi_M = new CoinWithdrawModel();
	}

	/*按id查找*/
    public function edit(){
    	$id = post('id');
    	(new IDMustBeRequire())->goCheck();
		$data = $this->coin_wi_M->find($id);    	
		$users=user_info($data['uid']);
		$data['uid_cn'] = $users['username'];
		$data['nickname'] = $users['nickname'];
		$data['admin_cn'] = admin_info($data['admin_id'],'username');	
    	empty($data) && error('数据不存在',404);    	
        return $data;      
    }	


	/*按id修改*/
	public function saveedit()
	{	
		(new CoinWithdrawValidate())->goCheck('scene_saveedit');
		$id = post('id');	
    	$data = post(['uid','money','withdraw_time','status']);
		$res=$this->coin_wi_M->up($id,$data);
		empty($res) && error('修改失败',404);
		admin_log('修改虚拟币提币',$id);  
 		return $res; 
	}

	/*查某一类*/
	public function lists()
	{
		(new \app\validate\AllsearchValidate())->goCheck();
		(new \app\validate\PageValidate())->goCheck();
		$where = [];
		$uid = post('uid');
		$username = post('username');
		$nickname = post('nickname');
		$oid = post('oid');
		$arrival=post('arrival');
		$created_time_begin = post('created_time_begin');
		$created_time_end = post('created_time_end');
		$created_time_end = intval($created_time_end);

   		if($username){
   				$user_M = new \app\model\user();
  				$where['uid'] = $user_M->find_mf_uid($username);
  		}

  		if($nickname){
   				$user_M = new \app\model\user();
  				$where['uid'] = $user_M->find_mf_uid_plus($nickname);
  		}
  		if($arrival){
  			$where['arrival']=$arrival;
  		}

  		if($created_time_begin){
  			$created_time_end = $created_time_end + 3600*24;
			$where['recharge_time[<>]'] = [$created_time_begin,$created_time_end];
		}

		if($oid){
			$where['oid[~]'] = $oid;
		}

		$page=post("page",1);
		$page_size = post("page_size",10);
		$data=$this->coin_wi_M->lists($page,$page_size,$where);
		
		//cs($this->coin_wi_M->log(),1);

		foreach($data as &$one){
			$users=user_info($one['uid']);
			$one['uid_cn'] = $users['username'];  
			$one['nickname'] =$users['nickname'];   
			$one['avatar'] =$users['avatar'];   
			$one['coin_rating_cn'] =$users['coin_rating_cn'];
			$one['admin_cn'] = admin_info($one['admin_id'],'username');
			if($one['status']==1){
				$one['status_cn'] = '提币成功';
			}elseif($one['status']==2){
				$one['status_cn'] = '提币失败';
			}else{
				$one['status_cn'] = '申请中';
			}
		}

		$count = $this->coin_wi_M->new_count($where);
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data; 
        // var_dump($this->coin_wi_M->log());
        // exit();
        return $res; 
	}


	/*改变提币申请状态*/
	public function change_status(){
		(new IDMustBeRequire())->goCheck();
		$id = post('id');	
    	$up['status'] = post('status');

    	$data = $this->coin_wi_M->find($id);
    	if($data['status']>0){
    		error('非申请中状态不能改变',400);
    	}
    	if($up['status'] == 2){
    		$uid = $data['uid'];
    		$money = $data['recharge_money'];
    		$cate = $data['cate'];
    		$oid = date('Ymd').rand(100,999).$id; //生成订单号
    		$remark = '申请提币'.$data['oid'].'回退';

    		$money_S = new \app\service\money();
    		$money_S->plus($uid,$money,$cate,"coin_withdraw_turn",$oid,$uid,$remark);
    	}
    	if($up['status'] == 1){
    		$up['recharge_time'] = time();
    		$money_S = new \app\service\money();
            $money_S->plus($data['uid'], $data['fee'], 'LMJJ', 'coin_withdraw', $data['oid'], $data['uid'], '提币手续费到联盟基金'); //记录资金流水
    	}

		$res=$this->coin_wi_M->up($id,$up);
		empty($res) && error('修改失败',404);
		admin_log('修改虚拟币提币状态',$id);  
 		return $res; 
	}



	

}