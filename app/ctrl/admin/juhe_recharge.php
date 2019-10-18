<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-08-09 22:56:58
 * Desc: 话费/流量/油卡
 */
namespace app\ctrl\admin;

use app\ctrl\admin\BaseController;
use app\validate\IDMustBeRequire;
use app\model\juhe_recharge as JuheRechargeModel;
use app\validate\JuHeRechargeValidate;


class juhe_recharge extends BaseController{

	public $juhe_M;
	public function __initialize(){
		$this->juhe_M = new JuheRechargeModel();
	}

	/*列表*/
	public function lists()
	{
		(new \app\validate\PageValidate())->goCheck();
		$page=post("page",1);
		$page_size = post('page_size',10);

		$oid = post('oid');
		$username = post('username');
		$nickname = post('nickname');
		$types = post('types','');
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

		if($begin_time>0){
            $end_time = $end_time ? $end_time : time();
            $end_time = $end_time + 3600*24;
            $where['created_time[<>]'] = [$begin_time,$end_time];
        }

		
		$data  = $this->juhe_M->lists($page,$page_size,$where);
		$count = $this->juhe_M->new_count($where);
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


	public function del(){
		(new \app\validate\DelValidate())->goCheck();
		$id_str = post('id_str');
        $id_ar = explode('@',$id_str);
		$res = $this->juhe_M -> del($id_ar);
		empty($res) && error('删除失败',400);
		return $res;
	}




}