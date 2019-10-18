<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-08-02 14:32:05
 * Desc: 生活缴费控制
 */
namespace app\ctrl\admin;

use app\ctrl\admin\BaseController;
use app\validate\IDMustBeRequire;
use app\model\living_pay as LivingPayModel;
use app\validate\LivingPayValidate;


class living_pay extends BaseController{

	public $lv_pay_M;
	public function __initialize(){
		$this->lv_pay_M = new LivingPayModel();
	}

	/*列表*/
	public function lists()
	{
		(new \app\validate\PageValidate())->goCheck();
		$page=post("page",1);
		$page_size = post('page_size',10);
		//订单号，用户名,类型，状态，时间区间
		$oid = post('oid');
		$username = post('username');
		$nickname = post('nickname');
		$status  = post('status');
		$types = post('types');
		$begin_time  = post('begin_time');
		$end_time = post('end_time');

		$where=[];
		if($oid){
			$where['oid[~]'] = $oid;
		}
		if($username){
			$user_M = new \app\model\user();
			$uid = $user_M->find_mf_uid($username);
			$where['uid'] = $uid;
		}
		if($nickname){
			$user_M = new \app\model\user();
        	$where['uid'] = $user_M->find_mf_uid_plus($nickname);
		}
		if($types){
			$where['types'] = $types;
		}
		if($status){
			$where['status'] = $status;
		}
		if($begin_time>0){
            $end_time = $end_time ? $end_time : time();
            $end_time = $end_time + 3600*24;
            $where['created_time[<>]'] = [$begin_time,$end_time];
        }

		
		$data  = $this->lv_pay_M->lists($page,$page_size,$where);
		$count = $this->lv_pay_M->new_count($where);
		foreach($data as &$one){
			$user = user_info($one['uid']);
			$one['avatar'] = $user['avatar'];
			$one['username'] = $user['username'];
			$one['nickname'] = $user['nickname'];
		}
		$res['data'] = $data;
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page_size'] = $page_size;
        $res['page'] = $page;
        return $res; 
	}

	/*审核失败,退钱*/
	public function is_check(){
		(new IDMustBeRequire())->goCheck();
		(new LivingPayValidate()) ->goCheck('scene_check');
		$id = post('id');
		$status = post('status');
		$readme = post('readme');
		$res = $this->lv_pay_M->up($id,['readme'=>$readme]);
		$old_status = $this->lv_pay_M->find($id,'status');
		if($old_status==0){
			
			if($status==2){
	     	$model = new \core\lib\Model();
	        $redis = new \core\lib\redis();  
	        $model->action();
	        $redis->multi();

			$res = $this->lv_pay_M->up($id,['status'=>2]);
			empty($res) && error('操作失败',400);
			$ar = $this->lv_pay_M->find($id);
			$money = $ar['pay'];
			$uid = $ar['uid'];
			$cate = 'coin';
			$oid = $ar['oid'];
			$ly_id = $ar['uid'];
			$remark = '生活缴费审核失败退款';
			$money_S = new \app\service\money();
			$money_S->plus($uid,$money,$cate,"living_payback",$oid,$ly_id,$remark);
	    	$model->run();
	    	$redis->exec();
			}
			if($status==1){
				$res = $this->lv_pay_M->up($id,['status'=>1]);
				empty($res) && error('操作失败',400);
			}
		}
		
		return $res;
	}

}