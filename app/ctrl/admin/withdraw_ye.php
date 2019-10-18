<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-04 14:08:12
 * Desc: 提现余额控制器
 */

namespace app\ctrl\admin;

use app\model\withdraw_ye as WithdrawModel;
use app\ctrl\admin\BaseController;
use app\validate\IDMustBeRequire;
use app\validate\WithdrawYeValidate;
use app\validate\AllsearchValidate;

class withdraw_ye extends BaseController{
	
	public $withdraw_M;
	public function __initialize(){
		$this->withdraw_M = new WithdrawModel();
	}

	/*按id查找*/
    public function find(){
    	$id = post('id');
    	(new IDMustBeRequire())->goCheck();

    	$data = $this->withdraw_M->find($id);
		$admin_M = new \app\model\admin();
		$admin_name = '';
		$data['cate_cn'] = find_reward_redis($data['cate']);


    	if(isset($data['admin_id'])){
       			$admin_name = $admin_M->find($data['admin_id']);	
       		}			
       	$data['admin_name'] = isset($admin_name['nick_name']) ? $admin_name['nick_name'] : $GLOBALS['admin']['username'];
    	empty($data) && error('数据不存在',404);    	
        return $data; 
    }	

	/*查某人提现记录*/
	public function lists()
	{
		(new AllsearchValidate())->goCheck();
		(new \app\validate\PageValidate())->goCheck();
		$where = [];
		$username = post('username');	
		$nickname = post('nickname');
		$uid = post('uid');
		$oid = post('oid');
		$status = post('status');
		$pay = post('pay');
		$cate = post('cate');

		$created_time_begin = post('created_time_begin');
		$created_time_end = post('created_time_end');

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


        
        if(is_numeric($uid)){
			$where['uid'] = $uid;
		}
		if($oid){
			$where['oid[~]'] = $oid;
		}
        if(is_numeric($status)){
			$where['status'] = $status;
		}
		if($pay){
			$where['pay'] = $pay;
		}
        if(is_numeric($created_time_begin)){
			$created_time_end = $created_time_end ? $created_time_end : time();
			$created_time_end = $created_time_end + 3600*24;
            $where['created_time[<>]'] = [$created_time_begin,$created_time_end];   
        }

        if($cate){
        	$where['cate'] = $cate;
        }



		$page=post("page",1);
		$page_size = post("page_size",10);
		$data=$this->withdraw_M->lists($page,$page_size,$where);
		$admin_M = new \app\model\admin();
		foreach($data as $key=>$rs){
			$users=user_info($rs['uid']);
			$data[$key]['username']  = $users['username']; //会员账号
			$data[$key]['nickname']  = $users['nickname']; //会员账号
			$data[$key]['avatar']  =  $users['avatar']; 
			$data[$key]['rating_cn'] = $users['rating_cn'];

			switch ($rs['status']) {
				case '1':
					$data[$key]['status_cn'] = '审核通过';
					break;
				case '2':
					$data[$key]['status_cn'] = '申请驳回';
					break;
				case '3':
					$data[$key]['status_cn'] = '审核中';
					break;			
				default:
					$data[$key]['status_cn'] = '申请中';
					break;
			}   	
       		$data[$key]['style_cn'] = find_reward_redis($rs['cate']);  //奖励类型
       		$data[$key]['created_time_cn'] = date('Y-m-d h:i:s',$rs['created_time']); 
       		$data[$key]['finish_time'] = $rs['finish_time']; 
       		if($rs['admin_id']){
       			$admin_name = $admin_M->find($rs['admin_id'],'username');
       		}			
       		$data[$key]['admin_name'] = $admin_name ? $admin_name : $GLOBALS['admin']['username'];
		}

		$count = $this->withdraw_M->new_count($where);
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data; 
       
        return $res; 
	}

	//批量通过
	public function allow(){
		(new IDMustBeRequire())->goCheck();
		$id = post('id');
		$old_status = $this->withdraw_M->find($id,'status'); 
		$oid = $this->withdraw_M->find($id,'oid'); 

		$res = false;
		if($old_status != 0){
			error($oid.' 已审核',400);
		}

		if($old_status['status'] == 0){	
			$auto_withdraw = c('auto_admin_wx_withdraw');
			$wx_withdraw_type = c('wx_withdraw_type');
			if($auto_withdraw==1 && $old_status['pay']=='微信'){
				if($wx_withdraw_type == '红包发送'){
				$user_M = new \app\model\user();
            	$openid = $user_M->find($old_status['uid'],'openid');
           	 	$wechat_redpack_S = new \app\service\wechat_redpack();
            	$redpack_res = $wechat_redpack_S->wx_redpack($openid,$old_status['money'],$old_status['real_money']*100,$old_status['oid'],$old_status['uid'],$old_status['cate']); 
				}
				if($wx_withdraw_type == '企业付款'){
				$user_M = new \app\model\user();
            	$openid = $user_M->find($old_status['uid'],'openid');
           	 	$wechat_redpack_S = new \app\service\wechat_redpack();
            	$redpack_res = $wechat_redpack_S->qy_redpack($openid,$old_status['money'],$old_status['real_money']*100,$old_status['oid'],$old_status['uid'],$old_status['cate']); 
				}
			}
		}	

		$data['status']	= 1;
		$data['admin_id'] = $GLOBALS['admin']['id'];
		$data['finish_time'] = time();
		$res=$this->withdraw_M->up($id,$data);
		empty($res) && error('操作失败',400);
		admin_log('批量通过提现',$id);
		return $oid." 审核成功";		
	}


	//批量驳回
	public function reject(){
		(new IDMustBeRequire())->goCheck();
		$id = post('id');
		$old_status = $this->withdraw_M->find($id); 
	
		$res = false;
		if($old_status['status'] != 0){
			error($old_status['oid'].' 已审核',400);
		}
		(new \app\service\money())->plus($old_status['uid'],$old_status['money'],$old_status['cate'],'withdraw_turn',$old_status['oid'],$old_status['uid'],'提现驳回');
		$data['status']	= 2;
		$data['admin_id'] = $GLOBALS['admin']['id'];
		$data['finish_time'] = time();
		$res=$this->withdraw_M->up($id,$data);
		empty($res) && error('操作失败',400);
		admin_log('批量驳回提现',$id);
		return $old_status['oid']." 驳回成功";
	}


	/*按id修改，只在status为0时进行修改 0：申请中 1：申请成功 2：申请失败*/
	public function saveedit()
	{	
		$id = post('id');
    	(new WithdrawYeValidate())->goCheck('scene_find');
		$old_status = $this->withdraw_M->find($id);

		if($old_status['status'] == 0){	
			$data['status'] =  post('status');
			if($data['status']==2){
				(new \app\service\money())->plus($old_status['uid'],$old_status['money'],$old_status['cate'],'withdraw_turn',$old_status['oid'],$old_status['uid'],'提现驳回');
			}

			$auto_withdraw = c('auto_admin_wx_withdraw');  
			$wx_withdraw_type = c('wx_withdraw_type');
			if($data['status']==1 && $auto_withdraw==1 && $old_status['pay']=='微信'){
				if($wx_withdraw_type == '红包发送'){
				$user_M = new \app\model\user();
            	$openid = $user_M->find($old_status['uid'],'openid');
           	 	$wechat_redpack_S = new \app\service\wechat_redpack();
            	$redpack_res = $wechat_redpack_S->wx_redpack($openid,$old_status['money'],$old_status['real_money']*100,$old_status['oid'],$old_status['uid'],$old_status['cate']); 
				}
				if($wx_withdraw_type == '企业付款'){
				$user_M = new \app\model\user();
            	$openid = $user_M->find($old_status['uid'],'openid');
           	 	$wechat_redpack_S = new \app\service\wechat_redpack();
            	$redpack_res = $wechat_redpack_S->qy_redpack($openid,$old_status['money'],$old_status['real_money']*100,$old_status['oid'],$old_status['uid'],$old_status['cate']); 
				}
			}
		}	
		$data['remark'] = post('remark');
		$data['admin_id'] = $GLOBALS['admin']['id'];
		$data['update_time'] = time();
		$data['finish_time'] = time();
		$res=$this->withdraw_M->up($id,$data);
		empty($res) && error('修改失败',404);
		admin_log('提现审核',$id);
		return $res;	 
	}


}