<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-05 09:38:34
 * Desc: 导出
 */

namespace app\ctrl\admin;
use app\validate\AllsearchValidate;

class export extends PublicController{
	public function __initialize(){
		set_time_limit(0);
	}

	/*查某一类*/
	public function order()
	{
        (new AllsearchValidate())->goCheck();
		$where = [];
		$oid    			= post('oid');
		$uid    			= post('uid');
		$sid    			= post('sid');
		$mail_name        	= post('mail_name');
		$mail_address      	= post('mail_address');
		$mail_tel          	= post('mail_tel');
		$pay             	= post('pay');
		$mail_oid          	= post('mail_oid');
		$mail_province    	= post('mail_province');
		$created_time_begin = post('created_time_begin');
		$created_time_end   = post('created_time_end');
		$pay_time_begin    	= post('pay_time_begin');
		$pay_time_end      	= post('pay_time_end');
		$complete_time_begin = post('complete_time_begin');
		$complete_time_end  = post('complete_time_end');
		$status          	= post('status');
		$title              = post('title');

		$username 			= post('username');
		$nickname			= post('nickname');
		$is_supplier = post('is_supplier');
		if ($username) {
			$user_M = new \app\model\user();
			$where['uid'] = $user_M->find_mf_uid($username);
		}
		if ($nickname) {
			$user_M = new \app\model\user();
			$where['uid'] = $user_M->find_mf_uid_plus($nickname);
		}


		if ($is_supplier == 1) {
			$where['sid[!]'] = 0;
		} else {
			$where['sid'] = 0;
		}
		if ($oid) {
			$where['oid[~]'] = $oid;
		}
		if (is_numeric($uid)) {
			$where['uid'] = $uid;
		}
		if (is_numeric($sid)) {
			$where['sid'] = $sid;
		}
		if ($mail_name) {
			$where['mail_name[~]'] = $mail_name;
		}
		if ($mail_address) {
			$where['mail_address[~]'] = $mail_address;
		}
		if ($mail_tel) {
			$where['mail_tel[~]'] = $mail_tel;
		}
		if ($pay) {
			$where['pay'] = $pay;
		}
		if ($mail_oid) {
			$where['mail_oid[~]'] = $mail_oid;
		}
		if ($mail_province) {
			$where['mail_province[~]'] = $mail_province;
		}
		if ($status) {
			if ($status == '配货中') {
				$where['status'] = ['配货中', '已支付'];
			} else {
				$where['status'] = $status;
			}
		}
		if ($created_time_begin > 0) {
			$created_time_end = $created_time_end ? $created_time_end : time();
			$created_time_end = $created_time_end + 3600 * 24;
			$where['created_time[<>]'] = [$created_time_begin, $created_time_end];
		}
		if ($pay_time_begin > 0) {
			$pay_time_end = $pay_time_end ? $pay_time_end : time();
			$pay_time_end = $pay_time_end + 3600 * 24;
			$where['pay_time[<>]'] = [$pay_time_begin, $pay_time_end];
		}
		if ($complete_time_begin > 0) {
			$complete_time_end = $complete_time_end ? $complete_time_end : time();
			$complete_time_end = $complete_time_end + 3600 * 24;
			$where['complete_time[<>]'] = [$complete_time_begin, $complete_time_end];
		}

        $order_product_M = new \app\model\order_product();
		if ($title) {
			$oid_ar = $order_product_M->find_mf_oid($title);
			$where['oid'] = $oid_ar;
		}
        $where['AND']['status[!]'] = '已关闭';
        $order_ar = (new \app\model\order())->lists_all($where);
 
        foreach($order_ar as $key=>$vo){
            $data[$key]['id']=$vo['id'];
            $data[$key]['oid']=$vo['oid'];
            $users=user_info($vo['uid']);
            $data[$key]['username']=$users['username'];
            $data[$key]['nickname']=$users['nickname'];
            $data[$key]['money']=$vo['money'];
            $data[$key]['is_pay']=$vo['is_pay']?'已支付':'未支付';
            $data[$key]['pay']=$vo['pay'];
            $data[$key]['status']=$vo['status'];
            $data[$key]['product']='';
            $product_ar=$order_product_M->lists_all(['oid'=>$vo['oid']],['title','number','sku_cn']);
            if($product_ar){
                foreach($product_ar as $vos){
                    $data[$key]['product']=$data[$key]['product'].$vos['title'].' '.$vos['sku_cn'].'X'.$vos['number'].'/';
                }
            }
            $data[$key]['product']=rtrim($data[$key]['product'],"/");
            $data[$key]['mail_name']=$vo['mail_name'];
            $data[$key]['mail_tel']=$vo['mail_tel'];
            $data[$key]['mail_address']=$vo['mail_province'].$vo['mail_city'].$vo['mail_area'].$vo['mail_town'].$vo['mail_address'];
            $data[$key]['mail_courier']=$vo['mail_courier'];
            $data[$key]['mail_oid']=$vo['mail_oid'];
            $data[$key]['created_time']=date("Y-m-d H:i:s",$vo['created_time']);
            $data[$key]['pay_time']=date("Y-m-d H:i:s",$vo['pay_time']);
            $data[$key]['mail_time']=date("Y-m-d H:i:s",$vo['mail_time']);
            $data[$key]['settle_time']=date("Y-m-d H:i:s",$vo['settle_time']);
            $data[$key]['complete_time']=date("Y-m-d H:i:s",$vo['complete_time']);
        }
        $title = ['ID','订单号','用户名','用户昵称','总金额','是否支付','支付方式','状态','商品','收货人','收货电话','收货地址','发货公司','发货单号','下单时间','支付时间','发货时间','结算时间','完成时间'];
        $phpexcel = new \core\lib\phpexcel();      
        $url=$phpexcel->wlw_excel_out($data,$title,'order');
        return $url;
    }

	//流水导出
	public function money()
	{
		(new AllsearchValidate())->goCheck();
		$where = [];
		$user_M = new \app\model\user();	
		$style = post('style');//奖励类型  sjfx/zqqd/ccl
		$cate  = post('cate'); //金额类型  amount/money/
		$types = post('types'); //加减
		$created_time_begin = post('created_time_begin');
		$created_time_end = post('created_time_end');

		$username = post('username');
		$nickname = post('nickname');
		$ly_id = post('ly_id');
		$ly_name = post('ly_name');
		$oid = post('oid');
		$balance = post('balance');

		if($username){
			$where['AND']['uid'] = $user_M->find_mf_uid($username);
		}
		if($nickname){
			$where['AND']['uid'] = $user_M->find_mf_uid_plus($nickname);
		}
		if($ly_id){
			$where['AND']['ly_id'] = $user_M->find_mf_uid($ly_id);
		}
		if($ly_name){
			$where['AND']['ly_id'] = $user_M->find_mf_uid($ly_name);
		}
		if($oid){
			$where['AND']['oid[~]'] = $oid;
		}
		if($balance){
			$where['AND']['balance'] = $balance;
		}
		if($types){
		$where['AND']['types'] = $types;
		}
		if($cate){
		$where['AND']['cate'] = $cate;
		}
		if($style){
		$where['AND']['iden'] = $style;
		}
		
		if(is_numeric($created_time_begin)){
			$created_time_end = $created_time_end ? $created_time_end : time();
			$created_time_end = $created_time_end + 3600*24;
        	$where['AND']['created_time[<>]'] = [$created_time_begin,$created_time_end]; 	
        }     
		$money_ar=(new \app\model\money())->lists_all($where);


		foreach($money_ar as $key=>$vo){
            $data[$key]['id']=$vo['id'];
            $data[$key]['oid']=$vo['oid'];
            $users=user_info($vo['uid']);
            $data[$key]['username']=$users['username'];
			$data[$key]['nickname']=$users['nickname'];
			$users=user_info($vo['ly_id']);
       		$data[$key]['ly_name'] =  $users['username']; 
			$data[$key]['ly_nickname']  = $users['nickname'];
            $data[$key]['types']=$vo['types']==1?'加':'减';
            $data[$key]['money']=($vo['types']==1?'+':'-').$vo['money'];
            $data[$key]['balance']=$vo['balance'];
            $data[$key]['cate']=find_reward_redis($vo['cate']);
            $data[$key]['style']=$vo['style'];
			$data[$key]['remark']=$vo['remark'];
            $data[$key]['created_time']=date("Y-m-d H:i:s",$vo['created_time']);
        }
        $title = ['ID','订单号','用户名','用户昵称','来源用户名','来源用户昵称','类型','总金额','剩余金额','资金类型','名称','备注','发放时间'];
        $phpexcel = new \core\lib\phpexcel();      
        $url=$phpexcel->wlw_excel_out($data,$title,'money');
        return $url;
	}


	//充值导出
	public function recharge()
	{
		(new AllsearchValidate())->goCheck();
		$where = [];	
		$uid = post('uid');
		$oid = post('oid');
		$status = post('status');
		$username = post('username');
		$nickname = post('nickname');

		$created_time_begin = post('created_time_begin');
		$created_time_end = post('created_time_end');

		if(is_numeric($uid)){
			$where['uid'] = $uid;
		}
		if($oid){
			$where['oid[~]'] = $oid;
		}
        if(is_numeric($status)){
			$where['status'] = $status;
		}
		if(is_numeric($created_time_begin)){
			$created_time_end = $created_time_end ? $created_time_end : time();
			$created_time_end = $created_time_end + 3600*24;
        	$where['created_time[<>]'] = [$created_time_begin,$created_time_end]; 	
        }
        if($username){
        	$user_M = new \app\model\user();
        	$uid_ar = $user_M->find_mf_uid($username);
        	$where['uid'] = $uid_ar;
        }
        if($nickname){
        	$user_M = new \app\model\user();
        	$where['uid'] = $user_M->find_mf_uid_plus($nickname);
        }
        
		$recharge_ar=(new \app\model\recharge())->lists_all($where);
		foreach($recharge_ar as $key=>$vo){
            $data[$key]['id']=$vo['id'];
            $data[$key]['oid']=$vo['oid'];
			$user=user_info($vo['uid']);
			$data[$key]['username']  = $user['username']; //会员账号
			$data[$key]['nickname']  = $user['nickname']; //会员账号
            $data[$key]['types']=$vo['types']==1?'加':'减';
			$data[$key]['money']=($vo['types']==1?'+':'-').$vo['money'];
			$data[$key]['cate'] = find_reward_redis($vo['cate']);  //奖励类型
			if($data[$key]['status'] == 1){
				$data[$key]['status']='支付成功';
			}elseif($data[$key]['status'] == 2){
				$data[$key]['status']='支付中';
			}elseif($data[$key]['status'] == 3){
				$data[$key]['status']='支付失败';
			}else{
				$data[$key]['status']='未支付';
			}

            $data[$key]['pay']=$vo['pay'];
            $data[$key]['remark']=$vo['remark'];
            $data[$key]['created_time']=date("Y-m-d H:i:s",$vo['created_time']);
            $data[$key]['pay_time']=date("Y-m-d H:i:s",$vo['pay_time']);
			
		}

        $title = ['ID','订单号','用户名','用户昵称','类型','金额','资金类型','充值状态','充值方式','备注','申请时间','支付时间'];
        $phpexcel = new \core\lib\phpexcel();      
        $url=$phpexcel->wlw_excel_out($data,$title,'recharge');
		return $url;		
	}


	






	//导出提现
	public function withdraw()
	{
		(new AllsearchValidate())->goCheck();
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


		$withdraw_ar=(new \app\model\withdraw_ye())->lists_all($where);
		$admin_M = new \app\model\admin();
		foreach($withdraw_ar as $key=>$vo){
            $data[$key]['id']=$vo['id'];
            $data[$key]['oid']=$vo['oid'];
			$users=user_info($vo['uid']);
			$data[$key]['username']  = $users['username']; //会员账号
			$data[$key]['nickname']  = $users['nickname']; //会员账号
            $data[$key]['money']=$vo['money'];
            $data[$key]['fee']=$vo['fee'];
            $data[$key]['integral']=$vo['integral'];
            $data[$key]['real_money']=$vo['real_money'];
            $data[$key]['cate']=find_reward_redis($vo['cate']);
            $data[$key]['pay']=$vo['pay'];

			switch ($vo['status']) {
				case '1':
					$data[$key]['status'] = '审核通过';
					break;
				case '2':
					$data[$key]['status'] = '申请驳回';
					break;
				case '3':
					$data[$key]['status'] = '审核中';
					break;			
				default:
					$data[$key]['status'] = '申请中';
					break;
			}   	

			$data[$key]['alipay']=$vo['alipay'];
			$data[$key]['alipay_name']=$vo['alipay_name'];
			$data[$key]['wechat']=$vo['wechat'];
			$data[$key]['bank']=$vo['bank'];
			$data[$key]['bank_name']=$vo['bank_name'];
			$data[$key]['bank_card']=$vo['bank_card'];
			$data[$key]['bank_network']=$vo['bank_network'];
			$data[$key]['bank_province']=$vo['bank_province'];
			$data[$key]['bank_city']=$vo['bank_city'];
       		if($vo['admin_id']){
       			$admin_name = $admin_M->find($vo['admin_id'],'username');
       		}			
			$data[$key]['admin_name'] = $admin_name ? $admin_name : '';
			$data[$key]['remark']=$vo['remark'];
			$data[$key]['created_time']=date("Y-m-d H:i:s",$vo['created_time']);
			$data[$key]['finish_time']=date("Y-m-d H:i:s",$vo['finish_time']);
		}

		$title = ['ID','订单号','用户名','用户昵称','申请金额','手续费','积分复购','实提金额','资金类型','提现方式','状态','支付宝账号','支付宝账号真实姓名','微信号','开户行','开户名','银行卡号','开户网点','开户省','开户市','管理员','备注','申请时间','提现时间'];
        $phpexcel = new \core\lib\phpexcel();      
        $url=$phpexcel->wlw_excel_out($data,$title,'提现');
		return $url;
	}



	//导出提币
	public function coin_withdraw()
	{
		(new AllsearchValidate())->goCheck();
		$where = [];
		$username = post('username');
		$nickname = post('nickname');
		$oid = post('oid');
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

  		if($created_time_begin){
  			$created_time_end = $created_time_end + 3600*24;
			$where['recharge_time[<>]'] = [$created_time_begin,$created_time_end];
		}

		if($oid){
			$where['oid[~]'] = $oid;
		}
		$where['ORDER']=['id'=>'DESC'];

		$withdraw_ar=(new \app\model\coin_withdraw())->lists_all($where);
		$admin_M = new \app\model\admin();
		foreach($withdraw_ar as $key=>$vo){
            $data[$key]['id']=$vo['id'];
            $data[$key]['oid']=$vo['oid'];
			$users=user_info($vo['uid']);
			$data[$key]['username']  = $users['username']; //会员账号
			$data[$key]['nickname']  = $users['nickname']; //会员账号
            $data[$key]['money']=$vo['money'];
            $data[$key]['fee']=$vo['fee'];
            $data[$key]['recharge_money']=$vo['recharge_money'];
            $data[$key]['cate']=$vo['cate'];
            $data[$key]['types']=$vo['types'];

			switch ($vo['status']) {
				case '1':
					$data[$key]['status'] = '审核通过';
					break;
				case '2':
					$data[$key]['status'] = '申请驳回';
					break;
				case '3':
					$data[$key]['status'] = '审核中';
					break;			
				default:
					$data[$key]['status'] = '申请中';
					break;
			}   	
            $data[$key]['add']=$vo['add'];

       		if($vo['admin_id']){
       			$admin_name = $admin_M->find($vo['admin_id'],'username');
       		}			
			$data[$key]['admin_name'] = $admin_name ? $admin_name : '';
			$data[$key]['remark']=$vo['remark'];
			$data[$key]['created_time']=date("Y-m-d H:i:s",$vo['created_time']);
			$data[$key]['recharge_time']=date("Y-m-d H:i:s",$vo['recharge_time']);
		}

		$title = ['ID','订单号','用户名','用户昵称','申请金额','手续费','实提金额','资金类型','提现方式','状态','地址','管理员','备注','申请时间','提现时间'];
        $phpexcel = new \core\lib\phpexcel();      
        $url=$phpexcel->wlw_excel_out($data,$title,'USDT提现');
		return $url;
	}
}

