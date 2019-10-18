<?php

/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-04 14:08:12
 * Desc: 充值控制器
 */

namespace app\ctrl\admin;

use app\model\recharge as RechargeModel;
use app\ctrl\admin\BaseController;
use app\validate\IDMustBeRequire;
use app\validate\RechargeValidate;
use app\validate\AllsearchValidate;

class recharge extends BaseController
{

	public $recharge_M;
	public function __initialize()
	{
		$this->recharge_M = new RechargeModel();
	}

	/*按id查找*/
	public function edit()
	{
		$id = post('id');
		(new IDMustBeRequire())->goCheck();
		$data = $this->recharge_M->find($id);
		empty($data) && error('数据不存在', 404);
		return $data;
	}

	/*按id修改*/
	public function saveedit()
	{
		$id = post('id');
		(new RechargeValidate())->goCheck('scene_find');
		$recharge_ar=$this->recharge_M->find($id);
		if($recharge_ar['status']!=0){
			$data = post(['admin_id', 'remark']);
		}else{
			$data = post(['status', 'admin_id', 'remark']);
			if($data['status']==1){
				$data['pay_time']=time();
				$data['pay']='管理员确认支付';
				admin_log('管理员充值确认支付', $id);

				$money_S = new \app\service\money();
				$money_S->plus($recharge_ar['uid'], $recharge_ar['money'], $recharge_ar['cate'], "online_recharge", $recharge_ar['oid'], $recharge_ar['uid'], '管理员确认支付','sum_money');
				
			}
		}
		$res = $this->recharge_M->up($id, $data);
		empty($res) && error('修改失败', 404);
		admin_log('修改充值状态', $id);
		return $res;
	}

	/*充值记录*/
	public function lists()
	{
		(new AllsearchValidate())->goCheck();
		(new \app\validate\PageValidate())->goCheck();
		$where = [];
		$uid = post('uid');
		$oid = post('oid');
		$status = post('status');
		$username = post('username');
		$nickname = post('nickname');

		$created_time_begin = post('created_time_begin');
		$created_time_end = post('created_time_end');

		if (is_numeric($uid)) {
			$where['uid'] = $uid;
		}
		if ($oid) {
			$where['oid[~]'] = $oid;
		}
		if (is_numeric($status)) {
			$where['status'] = $status;
		}
		if (is_numeric($created_time_begin)) {
			$created_time_end = $created_time_end ? $created_time_end : time();
			$created_time_end = $created_time_end + 3600 * 24;
			$where['created_time[<>]'] = [$created_time_begin, $created_time_end];
		}
		if ($username) {
			$user_M = new \app\model\user();
			$uid_ar = $user_M->find_mf_uid($username);
			$where['uid'] = $uid_ar;
		}
		if ($nickname) {
			$user_M = new \app\model\user();
			$where['uid'] = $user_M->find_mf_uid_plus($nickname);
		}

		$page = post("page", 1);
		$page_size = post("page_size", 10);
		$data = $this->recharge_M->lists($page, $page_size, $where);
		foreach ($data as $key => $rs) {
			$user = user_info($rs['uid']);
			$data[$key]['imtoken']  = $user['imtoken']; //会员账号
			$data[$key]['username']  = $user['username']; //会员账号
			$data[$key]['nickname']  = $user['nickname']; //会员账号
			$data[$key]['avatar']  =  $user['avatar'];
			$data[$key]['rating_cn'] = $user['rating_cn'];
			if($rs['status'] == 1){
				$data[$key]['status']='支付成功';
			}elseif($rs['status'] == 2){
				$data[$key]['status']='支付中';
			}elseif($rs['status'] == 3){
				$data[$key]['status']='支付失败';
			}else{
				$data[$key]['status']='未支付';
			}
			if ($rs['types'] == 2) {
				$data[$key]['money'] = "-" . $data[$key]['money'];  //加减符号 1加2减   
			} else {
				$data[$key]['money'] = "+" . $data[$key]['money'];
			}
			$data[$key]['style_cn'] = find_reward_redis($rs['cate']);  //奖励类型
		}

		$count = $this->recharge_M->new_count($where);
		$res['all_num'] = $count;
		$res['all_page'] = ceil($count / $page_size);
		$res['page'] = $page;
		$res['data'] = $data;
		//var_dump($this->product_review_M->log());
		//exit();
		return $res;
	}


	/*充值选项*/
	public function recharge_option()
	{
		$reward_M = new \app\model\reward();
		$data = $reward_M->title_by_types(2);
		$new_ar = [];
		$j = 0;
		if ($data) {
			foreach ($data as $key => $rs) {
				for ($i = 0; $i < 2; $i++) {
					if ($i == 0) {
						$new_ar[$i + $key + $j]['value']   = '加' . $rs['title'];
						$new_ar[$i + $key + $j]['label']   = '加' . $rs['title'];
					} else {
						$new_ar[$i + $key + $j]['value']   = '减' . $rs['title'];
						$new_ar[$i + $key + $j]['label']   = '减' . $rs['title'];
					}
				}
				$j++;
			}
		}
		return $new_ar;
	}

	/*管理员充值，先去充值表充值，再生成流水并修改用户表，有出错则回滚*/
	public function recharge_saveedit()
	{
		$pa = post(['id', 'recharge_type', 'num', 'remark']);

		$reward_M = new \app\model\reward();
		$len = mb_strlen($pa['recharge_type']);
		$cate_cn = mb_substr($pa['recharge_type'], 1, $len, 'utf-8');
		$cate = $reward_M->find_iden($cate_cn);
		$types_cn = mb_substr($pa['recharge_type'], 0, 1, 'utf-8');
		if ($types_cn == '加') {
			$types = 1;
		}
		if ($types_cn == '减') {
			$types = 2;
			$user_M = new \app\model\user();
			$old_num = $user_M->find($pa['id'], $cate);
			if ($pa['num'] > $old_num) {
				error($cate_cn . '不足！', 400);
				exit();
			}
		}

		$Model = new \core\lib\model;
		$redis = new \core\lib\redis;
		$Model->action();
		$redis->multi();


		$data['uid'] = $pa['id'];
		$data['money'] = $pa['num'];
		$data['cate'] = $cate;
		$data['types'] = $types;
		$data['admin_id'] = $GLOBALS['admin']['id'];
		$data['remark'] = $pa['remark'];
		$data['pay'] = '管理员充值';
		$data['status'] = 1;

		$res = $this->recharge_M->save($data);
		$oid = date('Ymd') . rand(100, 999) . $res; //生成订单号
		$up['oid'] = $oid;
		$res2 = $this->recharge_M->up($res, $up);
		empty($res2) && error('充值失败', 400);

		$uid = $pa['id'];
		$money = $pa['num'];
		$style = $pa['recharge_type'];
		$ly_id = $GLOBALS['admin']['id'];
		$remark = $pa['remark'];

		$money_S = new \app\service\money();
		if ($types_cn == '加') {
			$money_S->plus($uid, $money, $cate, "htjejj", $oid, $uid, $remark);
		} else {
			$money_S->minus($uid, $money, $cate, "htjejj", $oid, $uid, $remark);
		}

		$Model->run();
		$redis->exec();
		admin_log('管理员充减账户', $res);
		return true;
	}
}
