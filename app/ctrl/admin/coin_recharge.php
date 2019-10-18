<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-04-30 09:37:32
 * Desc: 充币记录
 */

namespace app\ctrl\admin;

use app\model\coin_recharge as CoinRechargeModel;
use app\ctrl\admin\BaseController;
use app\validate\IDMustBeRequire;
use app\validate\CoinRechargeValidate;


class coin_recharge extends BaseController{
	
	public $coin_re_M;
	public function __initialize(){
		$this->coin_re_M = new CoinRechargeModel();
	}

	/*按id查找*/
    public function edit(){
    	$id = post('id');
    	(new IDMustBeRequire())->goCheck();
    	$data = $this->coin_re_M->find($id);

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
		(new CoinRechargeValidate())->goCheck('scene_saveedit');
		$id = post('id');	
    	$data = post(['uid','money','recharge_time','status']);
		$res=$this->coin_re_M->up($id,$data);
		empty($res) && error('修改失败',404);
		admin_log('修改充值虚拟币',$id);  
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
		$created_time_begin = post('created_time_begin');
		$created_time_end = post('created_time_end');
		$created_time_end = intval($created_time_end) + 3600*24;


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
  		if($created_time_begin){
			$where['created_time[<>]'] = [$created_time_begin,$created_time_end];
		}

		$page=post("page",1);
		$page_size = post("page_size",10);
		$data=$this->coin_re_M->lists($page,$page_size,$where);

		foreach($data as &$one){
			$users=user_info($one['uid']);
			$one['uid_cn'] = $users['username'];  
			$one['nickname'] =$users['nickname'];   
			$one['avatar'] =$users['avatar'];   
			$one['coin_rating_cn'] =$users['coin_rating_cn'];

			if($one['status']==1){
				$one['status_cn'] = '支付成功';
			}else{
				$one['status_cn'] = '未支付';
			}

		}
		unset($one);


		$count = $this->coin_re_M->new_count($where);
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data; 
        // var_dump($this->coin_re_M->log());
        // exit();
        return $res; 
	}

//================= 以上是基础方法 ==================


	

}