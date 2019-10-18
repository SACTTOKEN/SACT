<?php
/**
 * Created by yaaaaa__god 
 * User: yaaaaa
 * Date: 2018-12-18 13:50:53
 * Desc: 订单控制器
 */

namespace app\ctrl\supplier;

use app\model\order as OrderModel;
use app\validate\IDMustBeRequire;
use app\validate\OrderValidate;


use app\validate\AllsearchValidate;

class order extends BaseController{
	
	public $orderM;
	public function __initialize(){
		$this->orderM = new OrderModel();
	}

	/*订单列表*/
	public function lists()
	{
		(new AllsearchValidate())->goCheck();	
		(new \app\validate\PageValidate())->goCheck();
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
		$complete_time_begin= post('complete_time_begin'); 
		$complete_time_end  = post('complete_time_end'); 
		$status          	= post('status');   
		$title              = post('title');

		$username 			= post('username');
		$nickname			= post('nickname');
        if($username){
        	$user_M = new \app\model\user();
        	$where['uid'] = $user_M->find_mf_uid($username);
        }
        if($nickname){
        	$user_M = new \app\model\user();
        	$where['uid'] = $user_M->find_mf_uid_plus($nickname);
        }

	
		if($oid){
			$where['oid[~]'] = $oid;
		}
		if(is_numeric($uid)){
			$where['uid'] = $uid;
		}	
		if(is_numeric($sid)){
			$where['sid'] = $sid;
		}
		if($mail_name){
			$where['mail_name[~]'] = $mail_name;
		}
		if($mail_address){
			$where['mail_address[~]'] = $mail_address;
		}
		if($mail_tel){
			$where['mail_tel[~]'] = $mail_tel;
		}		
		if($pay){
			$where['pay'] = $pay;
		}
		if($mail_oid){
			$where['mail_oid[~]'] = $mail_oid;
		}
		if($mail_province){
			$where['mail_province[~]'] = $mail_province;
		}
		if($status){
			$where['status'] = $status;
		}
		if($created_time_begin>0){
			$created_time_end = $created_time_end ? $created_time_end : time();
			$created_time_end = $created_time_end + 3600*24;
			$where['created_time[<>]'] = [$created_time_begin,$created_time_end];
		}
		if($pay_time_begin>0){
			$pay_time_end = $pay_time_end ? $pay_time_end : time();
			$pay_time_end = $pay_time_end + 3600*24;
			$where['pay_time[<>]'] = [$pay_time_begin,$pay_time_end];
		}
		if($complete_time_begin>0){
			$complete_time_end = $complete_time_end ? $complete_time_end : time();
			$complete_time_end = $complete_time_end + 3600*24;
			$where['complete_time[<>]'] = [$complete_time_begin,$complete_time_end];
		}

		if($title){

			$order_product_M = new \app\model\order_product();
			$oid_ar = $order_product_M ->find_mf_oid($title); //根据商品名称模糊查找订单号
			//$oid_ar = ['201902180335051966790','201902180348169339391'];
			$where['oid'] = $oid_ar;
		}
		$where['sid'] = $GLOBALS['user']['id'];

		$page=post("page",1);
		$page_size = post("page_size",10);		
		$data=$this->orderM->lists($page,$page_size,$where);
		$count = $this->orderM->new_count($where);
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data; 

		//var_dump($this->orderM->log());	

       	return $res; 
	}

	/*订单详情*/
	public function order_info(){
		$id = post('id');
		(new IDMustBeRequire())->goCheck();
		$order_pro_M = new \app\model\order_product();
		$base=$this->orderM->have(['id'=>$id,'sid'=>$GLOBALS['user']['id']]);
		empty($base) && error('订单不存在',404);
		//拼团
		if($base['types']==4){
			$base['groups']=(new \app\model\groups())->have(['oid'=>$id]);
			$base['groups']['lists']=(new \app\model\groups())->lists_all(['head_oid'=>$base['groups']['head_oid'],'is_pay'=>1]);
			foreach($base['groups']['lists'] as &$vo){
				$users=user_info($vo['uid']);
				$vo['nickname']=$users['nickname']?$users['nickname']:$users['username'];
				$vo['avatar']=$users['avatar'];
				$vo['end_time_second']=$vo['end_time']-time();
				$vo['difference']=$vo['group_people']-$vo['now_people'];
			}
		}
		$users=user_info($base['uid']);
 		$base['uid_cn'] = $users['username'];
        $product =  $order_pro_M->find_by_oid($base['oid']);   
        $product_M = new \app\model\product();



        if(!empty($product)){
            foreach($product as $k=>$v){
               $product[$k]['sku_cn']= explode('@',$v['sku_cn']);
               $product[$k]['num_money'] = $v['money'] * $v['number'];
               $product[$k]['hid'] = $product_M->findme($v['pid'],'hid');
            }
        }



        $other = [
        	'operator' => $GLOBALS['admin']['username'],
        	'print_time' => date('Y-m-d H:i:s'),
			//'com_add'   => c('add'),
			//'com_tel'	=> c('tel'),
        ]; 

  
		$info = [
			'base' => $base,
			'product_ar' => $product,
			'other' => $other,
			
		];


		return $info;
	}



	/*修改收货信息*/
	public function edit_mail()
	{
		$id = post('id');
		(new IDMustBeRequire())->goCheck();		
		$base=$this->orderM->have(['id'=>$id,'sid'=>$GLOBALS['user']['id']]);
		empty($base) && error('订单不存在',404);

    	$data = post(['mail_name','mail_tel','mail_province','mail_city','mail_area','mail_town','mail_address','admin_remark']);
    	$res=$this->orderM->up($id,$data);
		empty($res) && error('修改失败',404);	
		admin_log('修改收货信息',$id);   
 		return $res; 
	}


	/*修改支付状态*/
	public function edit_pay()
	{
		$id = post('id');
    	(new IDMustBeRequire())->goCheck();
    	$data = post(['pay','is_pay','status','pay_time']);
    	$res=$this->orderM->up($id,$data);
		empty($res) && error('修改失败',404);	
		admin_log('修改订单支付状态',$id);   
 		return $res;
	}


	/*修改发货信息*/
	public function edit_send()
	{	
		(new IDMustBeRequire())->goCheck();
		$id = post('id');
		
		$base=$this->orderM->have(['id'=>$id,'sid'=>$GLOBALS['user']['id']]);
		empty($base) && error('订单不存在',404);

    	$mail_type = post('mail_type'); //发货方式  0：常规  1：电子面单

    	if ($mail_type == 0) {
			(new OrderValidate())->goCheck('scene_edit_send');
			$data = post(['mail_oid', 'remark']);
			$order_ar = $this->orderM->find($id);
			if ($order_ar['status'] == '未支付' || $order_ar['status'] == '已关闭') {
				error('订单未支付', 400);
			}
			if ($data['mail_oid'] != '' && $order_ar['status'] == '已支付') {
				$data['status'] = '已发货';
				$data['mail_time'] = time();
				$data['mail_courier'] = (new \app\model\mail())->have(['sid' => $order_ar['sid']], 'title');
			}
			$res = $this->orderM->up($id, $data);
			mb_sms('ship',$id);
			empty($res) && error('修改失败', 400);
			admin_log('修改收货信息', $id);
			return $data;
		} else {
			$kdn_S = new \app\service\kdn();
			$data_up = $kdn_S->ship($id);
			if($data_up!==true){
            	error($data_up, 400);
			}
			mb_sms('ship',$id);
			admin_log('生成订单面单', $id);
			return "发货成功";
		}
 		
	}


	/*退换货列表,参数$status  0正常，1申请退货，2允许退货，3已退货待退款 4退货成功  5 驳回  @404代表非0 */
	public function reback_lists(){
		$where = [];

 		$username = post('username');
        $uid   = post('uid');
		$pid_cn   = post('pid_cn');
		$pid   = post('pid');
		$oid   = post('oid');
		$status = post('status',404);

  		if(is_numeric($uid)){
            $where['uid'] = $uid;
        }
        if($username){
            $user_M = new \app\model\user();
            $uid = $user_M->find_mf_uid($username);
            $where['uid'] = $uid;
        }

        if($pid){
        	$where['pid'] = $pid;
        }


        if($pid_cn){
            $product_M = new \app\model\product();
            $pid = $product_M->find_mf_pid($pid_cn);
            $where['pid'] = $pid;
        }

        if($oid){
        	$where['oid[~]'] = $oid;
        }

        if($status!=404){
            $where['status'] = $status;
        }else{
            $where['status'] = [1,2,3,4];
        }
        $where['sid'] = $GLOBALS['user']['id'];

		$page=post("page",1);
		$page_size = post("page_size",10);	
		$goods_M = new \app\model\order_product();	
		$data=$goods_M->return_goods($page,$page_size,$where);

		//cs($goods_M->log(),1);

		$count = $goods_M->new_count($where);
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data;
        return $res;
	}


	/*退换货*/
	public function reback_goods(){
		$goods_id = post('goods_id',0);
		$status = post('status',0);
		(new \app\validate\OrderProductValidate())->goCheck('scene_edit');
		$goods_M = new \app\model\order_product();

        $where['id']=$goods_id;
        $where['sid']=$GLOBALS['user']['id'];
		$where['status[!]']=0;
		$order_pro_ar=$goods_M->have($where);
        empty($order_pro_ar) && error('不是退货状态',404);
        $where_ar['oid']=$order_pro_ar['oid'];
        $where_ar['sid']=$GLOBALS['user']['id'];
        $order_ar=$this->orderM->have($where_ar);
        empty($order_ar) && error('订单不存在',404);
		
		$order_S=new \app\service\order();
		if($order_ar['status']!='已发货' && $order_ar['status']!='已支付' && $order_ar['status']!='配货中'){
			$order_S->order_is_raturn($order_ar);
			error('订单'.$order_ar['status'].'已取消退货',404);
        }
        if(($order_ar['types']==1 && c('fksfjfl')==1) || $order_ar['is_settle']>0){
			$order_S->order_is_raturn($order_ar);
            error('订单已结算，已取消退货',404);
		}
		
		switch($status){
			case "0":
				if($order_pro_ar['status']!=1 && $order_pro_ar['status']!=3){
					error('用户退货中',404);
				}
				$order_S->order_is_raturn($order_ar);
				break;
			case "2":
				if($order_pro_ar['status']!=1){
					error('用户退货中',404);
				}
				(new \app\validate\OrderProductValidate())->goCheck('reback_goods');
				$parameter=post(['return_name','return_tel','return_address']);
				$data['return_name']=$parameter['return_name'];
				$data['return_tel']=$parameter['return_tel'];
				$data['return_address']=$parameter['return_address'];
				break;
			case "4":
				if($order_pro_ar['status']!=3){
					error('用户未提交退货单号',404);
				}
				//退货退款
				$order_S=new \app\service\order();
				$return_res=$order_S->return_order($order_ar,$order_pro_ar);
				if($return_res!==true){
					error($return_res,404);
				}
				//退货退款end
				$order_S->order_is_raturn($order_ar);
				break;
			default:
				error('用户退货中',404);
		}
		$data['status']	=$status;
        $data['return_time']=time();
		$data['admin_remark'] = post('admin_remark','');
		$res=$goods_M->up($goods_id,$data);
		empty($res) && error('修改失败',400);	
		admin_log('修改退换货状态',$goods_id);   
		return $res;
	}


	/*订单奖金审核*/
	public function order_money_list(){
		$money_M = new \app\model\money();
		$oid = post('oid');
		$data = $money_M->lists_by_oid($oid);
		foreach($data as $key=>$rs){
			//会员账号 与 昵称
			$users=user_info($rs['uid']);
			$data[$key]['username']  = $users['username'];
			$data[$key]['nickname']  = $users['nickname'];

       		//加减符号 1加2减   
       		if($rs['types'] == 2){
       			$data[$key]['money'] = "-".$data[$key]['money'];
       		}else{
       			$data[$key]['money'] = "+".$data[$key]['money'];
       		}

       		//奖励类型 到reward中去查
       		$data[$key]['style_cn'] = find_reward_redis($rs['cate']); 
       		//来源 
			$users=user_info($rs['ly_id']);
       		$data[$key]['ly_name'] =  $users['username']; 
       					
		}
		return $data; 
	}

	/*发货时提示如果选择快递鸟电子面单，需配置快递鸟接口，否则不可选择*/
	public function check_kdn(){

		$mail_M = new \app\model\mail();
		$ar = $mail_M ->have(['sid'=>$GLOBALS['user']['id']]);
		if($ar['is_kdl']!=1){
			error('未开通快递鸟',400);
		}
		return true;
	}




}