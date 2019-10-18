<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-05 09:38:34
 * Desc: 流水控制
 */

namespace app\ctrl\admin;

use app\model\money as MoneyModel;
use app\ctrl\admin\BaseController;
use core\lib\redis;
use app\validate\AllsearchValidate;

class money extends BaseController{
	
	public $money_M;
	public function __initialize(){
		$this->money_M = new MoneyModel();
	}


	/*查某一类*/
	public function lists()
	{
		(new AllsearchValidate())->goCheck();
		(new \app\validate\PageValidate())->goCheck();

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


		$page=post("page",1);
		$page_size = post("page_size",10);
		
		$data=$this->money_M->lists($page,$page_size,$where);


		$reward_M = new \app\model\reward();
		$style_cn = $reward_M->lists();
		$style_cn = array_column($style_cn,NULL,'iden');
		$coin=['coin','coin_storage','integrity','USDT','BTC','ETH','LTC','BCH'];
		foreach($data as $key=>$rs){
			$users=user_info($rs['uid']);
			if(in_array($rs['cate'],$coin)){
				$data[$key]['rating_cn']  = $users['coin_rating_cn'];
			}else{
				$data[$key]['rating_cn']  = $users['rating_cn'];
			}
			//会员账号 与 昵称 图像 等级
			$data[$key]['username']  = $users['username'];
			$data[$key]['nickname']  = $users['nickname'];
			$data[$key]['avatar']  =  $users['avatar'];
			$data[$key]['admin_remark']  =  $users['admin_remark'];
			$data[$key]['cate_cn']  =  find_reward_redis($rs['cate']);
			
			
       		//加减符号 1加2减   
       		if($rs['types'] == 2){
       			$data[$key]['money'] = "-".$data[$key]['money'];
       		}else{
       			$data[$key]['money'] = "+".$data[$key]['money'];
       		}

       		//奖励类型 到reward中去查
			$data[$key]['style_cn'] = $style_cn[$rs['cate']]['title']; 
			if($data[$key]['remark']==''){
				$data[$key]['remark']=$data[$key]['style'];
			}
       		//来源 
			$users=user_info($rs['ly_id']);
       		$data[$key]['ly_name'] =  $users['username']; 
			$data[$key]['ly_nickname']  = $users['nickname'];
			$data[$key]['ly_admin_remark']  = $users['admin_remark'];
			if(in_array($rs['cate'],$coin)){
				$data[$key]['ly_rating_cn']  = $users['coin_rating_cn'];
			}else{
				$data[$key]['ly_rating_cn']  = $users['rating_cn'];
			}
       					
		}

		$count = $this->money_M->new_count($where);
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data; 
        //cs($this->money_M->log(),1);
        return $res; 
	}

//================= 以上是基础方法 ==================

	//reward表里types=2的 金额类型
	public function search_money_option(){
		$reward_M = new \app\model\reward();
		$data = $reward_M->title_by_types(2);
		return $data;
	}

	//reward表里types=0的 奖励类型
	public function search_reward_option(){
		$reward_M = new \app\model\reward();
		$data = $reward_M->title_by_types(0);
		return $data;
	}



	/*导出EXCEL表格*/
	public function excel_out(){
        $data = $this->money_M->list_excel(['id','oid','uid','types','money','balance','ly_id','remark']);
        $title = ['ID','订单号','用户ID','加减','金额','余额','来源ID','备注'];
        $phpexcel = new \core\lib\phpexcel();      
		echo $phpexcel->wlw_excel_out($data,$title);
        exit();
	}

}