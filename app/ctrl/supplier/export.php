<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-05 09:38:34
 * Desc: 导出
 */

namespace app\ctrl\supplier;
use app\validate\AllsearchValidate;

class export extends PublicController{
	public function __initialize(){
	}

	/*查某一类*/
	public function order()
	{
        (new AllsearchValidate())->goCheck();
		$where = [];
		$oid    			= post('oid');
		$uid    			= post('uid');
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

		if ($oid) {
			$where['oid[~]'] = $oid;
		}
		if (is_numeric($uid)) {
			$where['uid'] = $uid;
		}
        $where['sid'] = $GLOBALS['user']['id'];
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
        $url=$phpexcel->wlw_excel_out($data,$title,'订单');
        return $url;
    }

	
}

