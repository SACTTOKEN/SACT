<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-04-04 16:16:00
 * Desc: 插件-早起签到主表
 */

namespace app\ctrl\admin;

use app\model\plugin_early_lord as PluginEarlyLordModel;
use app\model\plugin_early_slave as PluginEarlySlaveModel;

use app\ctrl\admin\BaseController;
use app\validate\IDMustBeRequire;
use app\validate\DelValidate;
use app\validate\PluginEarlyValidate;


class plugin_early_lord extends BaseController{
	
	public $early_lord_M;
	public $early_slave_M;
	public function __initialize(){
		$this->early_lord_M = new PluginEarlyLordModel();
		$this->early_slave_M = new PluginEarlySlaveModel();
	}

	/*生成下一期号*/
	public function next_stage(){
		$max = $this->early_lord_M->max('plugin_early_lord','stage');
		$new_stage = $max+1;
		return $new_stage;
	}

	/*按id查找*/
    public function find(){
    	$id = post('id');
    	(new IDMustBeRequire())->goCheck();
    	$data = $this->early_lord_M->find($id);
    	empty($data) && error('数据不存在',404);    	
        return $data;      
    }	

    /*保存*/
	public function saveadd(){	
		(new PluginEarlyValidate())->goCheck('scene_add');
		$end_time = post('end_time');

		$stage = date('Ymd',$end_time);
		$day = date('Y-m-d',$end_time);

		$is_have = $this->early_lord_M->find_by_stage($stage);
		!empty($is_have) && error('该天签到期数已添加',400);

		$data['stage_title'] = post('stage_title');
		$data['stage'] = $stage;

		$early_time = c('early_time');
		$early_time_ar =  explode('|',$early_time);
		$data['sign_begin_time'] = $early_time_ar[0];
		$data['sign_end_time'] =   $early_time_ar[1]; //08:00 

		$data['begin_time'] = strtotime($day." ".$early_time_ar[0]);
		$data['end_time'] = strtotime($day." ".$early_time_ar[1]); //时间戳格式 的 签到结束时间
		
		$res=$this->early_lord_M->save($data);
		empty($res) && error('添加失败',400);	 
		admin_log('添加早起签到',$res);   
		return $res;
	}


	/*按id删除*/
	public function del(){
		$id_str = post('id_str');
		$id_ar = explode('@',$id_str);

		(new DelValidate())->goCheck();
		foreach($id_ar as $id){
			$stage = $this->early_lord_M->find($id,'stage');
			$this->early_slave_M->del_by_stage($stage);
		}

		$res=$this->early_lord_M->del($id_ar);
		// var_dump($this->early_slave_M->log());
		// eixt();
		empty($res) && error('删除失败',400);
		admin_log('删除早起签到',$id_str);   
		return $res;
	}

	/*按id修改*/
	public function saveedit()
	{	
		$id = post('id');
    	$data = post(['point','rf']);
		$res=$this->early_lord_M->up($id,$data);
		empty($res) && error('修改失败',404);
		admin_log('修改早起签到',$id);   
 		return $res; 
	}

	/*结算当期*/
	public function balance()
	{	

		$stage = post('stage');
		$data['rate'] = c('early_rate');
		$ar = $this->early_lord_M->find_by_stage($stage);
		
		if(time()<$ar['end_time']){
			error('签到还未结束',400);
		}
		

		if($ar['is_end'] == 1){
			error('本期已结算',400);
		}
	
		$join_all = $this->early_slave_M->join_all_m($stage); //参与总额
		$sign_all = $this->early_slave_M->sign_all_m($stage); //签到总额
        $join_man = $this->early_slave_M->join_man_m($stage); //参与人数
        $sign_man = $this->early_slave_M->sign_man_m($stage); //签到人数

		$data['join_all'] = $join_all ? $join_all : 0;
		$data['sign_all'] = $sign_all ? $sign_all : 0;
		$data['join_man'] = $join_man ? $join_man : 0;
		$data['sign_man'] = $sign_man ? $sign_man : 0;

		if($join_all==0){
			error('本期无人参与',400);
		}

		//算服务费 =（参与总额 - 签到总额）* 平台利率 千分比
		if($sign_all==0){
			$avg = 0;
			$charge = $join_all;
		}else{
			$charge = ($join_all - $sign_all) * ($data['rate']/1000);
			$avg = ($join_all - $sign_all - $charge)/$sign_all;
			$avg = round($avg,8); //$avg = sprintf("%.2f",$avg);
		}
			
		$data['charge'] = $charge;
		$data['avg'] = $avg;
		$data['is_end'] = 1; //结算状态
		$balance_type = c('early_balance_type'); //结算类型

        $Model = new \core\lib\Model();
        $redis = new \core\lib\redis();
        $Model->action();
        $redis->multi();

		$res=$this->early_lord_M->up($ar['id'],$data);//结算END
		empty($res) && error('结算失败',400);
	

		$early_S = new \app\service\early();

		if($avg!=0){
			$early_S->early_reward($stage,$avg,$balance_type); //奖励发放
		}

		admin_log('结算早起签到',$stage);
	 	$Model->run();
        $redis->exec();

 		return $res; 
	}


	/*查某一类*/
	public function lists()
	{
		(new \app\validate\PageValidate())->goCheck();
		$where = [];
		$page=post("page",1);
		$page_size = post("page_size",10);
		$data=$this->early_lord_M->lists($page,$page_size,$where);
		$count = $this->early_lord_M->new_count($where);
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data;  
        return $res; 
	}



//====================以下是赛季排行==================================


	/*生成上赛季信息,赛季结算*/
	public function my_war()
	{
		$early_open_war = c('early_open_war');
		empty($early_open_war) && error('请先开启赛季功能',400);


		//1:生成赛季编号和起止时间 OK
		$cycle = renew_c('early_war_cycle');	
		if($cycle=='自然月'){
			$money_reward = 'early_month'; //资金奖励  流水类型

   			$begin_time = date('Y-m-01 00:00:00',strtotime('-1 month'));
			$end_time = date("Y-m-d 23:59:59", strtotime(-date('d').'day'));
			$begin_time = strtotime($begin_time);
			$end_time = strtotime($end_time);
			$war_ex = date('Ymd',$begin_time);
        	$war = $war_ex."_m";

        	$begin_day = date('Ymd',$begin_time);
        	$end_day = date('Ymd',$end_time);
        	$month_up = date('Ym',$begin_time);
        	$where_month['stage[~]'] = $month_up;
        	$red_all = $this->early_lord_M->find_sum('charge',$where_month); //总服务费
		}
		if($cycle == '自然周'){
			$money_reward = 'early_week'; //资金奖励  流水类型
			$begin_time = strtotime('monday last week');
        	$end_time = strtotime('monday this week')-1; //周日最后一秒  
        	$war_ex = date('Ymd',$begin_time);
        	$war = $war_ex."_w";

        	$begin_day = date('Ymd',$begin_time);
        	$end_day = date('Ymd',$end_time);
        	$where_week['stage[<>]'] = [$begin_day,$end_day];
        	$red_all = $this->early_lord_M->find_sum('charge',$where_week); //总服务费 OK
		}

		//2：判断是否结算
			$early_war_M = new \app\model\plugin_early_war();
			$is['war']	= $war;
			$is_have = $early_war_M->is_have($is);		
			$is_have && error($begin_day."-".$end_day.'赛季已结算',400);


		//3:有一个函数 时间起止 去算三种情况的 1:签到最多 累计签到次数, 2:偷懒最多 累计亏损,3:瓜分最多 累计赚到',
		    $ar = $this->go_war($begin_time,$end_time); //ok,测试OK

		    if($ar==false){error($begin_day."-".$end_day.'上个赛季未开放',400);} 
		    // $ar = [
		    // 	['uid_ar'=>'19@80','uid_num'=>1,'champion'=>1,'recode'=>3],
		    // 	['uid_ar'=>'80','uid_num'=>1,'champion'=>2,'recode'=>90],
		    // 	['uid_ar'=>'19','uid_num'=>1,'champion'=>3,'recode'=>81],
		    // ];  //勿删,数据格式示例
 

		//4:排行榜奖励 OK
			$early_war_M = new \app\model\plugin_early_war();	
			$money_S = new \app\service\money();//分奖
			$p1 = c('early_month_num_1'); //百分比
			$p2 = c('early_month_num_2');
			$p3 = c('early_month_num_3');
			$balance_type = c('early_balance_type'); 

	        $model = new \core\lib\Model();
	        $redis = new \core\lib\redis();  
	        $model->action();
	        $redis->multi();


			foreach($ar as $key=>$sub){
				if(empty($sub['uid_ar'])){break;}
				if($sub['uid_ar']){
					$u_ar = explode('@',$sub['uid_ar']);

					if($sub['champion']==1){
							$earn = ($red_all * $p1)/(100*$sub['uid_num']);
							$remark = "签到次数最多的";
						}
					if($sub['champion']==2){
							$earn = ($red_all * $p2)/(100*$sub['uid_num']);
							$remark = "签到亏损最多的";
						}
					if($sub['champion']==3){
							$earn = ($red_all * $p3)/(100*$sub['uid_num']);
							$remark = "签到瓜分最多的";
						}

					// $ar[$key]['earn'] = $earn;
					// $ar[$key]['war'] = $war;
					// $ar[$key]['begin_time']	 = $begin_time;
					// $ar[$key]['end_time']	 = $end_time;
					// $back = $early_war_M->save_by_oid($ar[$key]);
					// $oid = $back['oid'];
					
					foreach($u_ar as $one_uid){

						$ar[$key]['earn'] = $earn;
						$ar[$key]['war'] = $war;
						$ar[$key]['begin_time']	 = $begin_time;
						$ar[$key]['end_time']	 = $end_time;
						$ar[$key]['uid_ar'] = $one_uid; //改成单个UID

						$back = $early_war_M->save_by_oid($ar[$key]);
						$oid = $back['oid'];

						$money_S->plus($one_uid,$earn,$balance_type,$money_reward,$oid,$one_uid,$remark);
					}
				}
			}
        $model->run();
        $redis->exec();

        return true;
	}



	public function go_war($begin_time,$end_time){

		$begin_day = date('Ymd',$begin_time);
		$end_day = date('Ymd',$end_time);
		$ar_1 = $this->early_slave_M->find_max_sign_week($begin_day,$end_day);

		//cs($ar_1,1); Array ( [0] => Array ( [uid] => 19 [win] => 3 ) )  //勿删，格式示例

		if($ar_1){
			$uid_ar_1 = '';
			$uid_num_1 = count($ar_1);
	
			foreach($ar_1 as $one_1){
				$uid_ar_1 .= "@".$one_1['uid'];				
				$recode_1 = $one_1['win'];
			}
			$uid_ar_1 = trim($uid_ar_1,'@');
			$ar[] = ['uid_ar'=>$uid_ar_1,'uid_num'=>$uid_num_1,'champion'=>1,'recode'=>$recode_1];
		}else{
			return false;
		}


		$ar_2 = $this->early_slave_M->find_max_stake_week($begin_day,$end_day);
		if($ar_2){
			$uid_ar_2 = '';
			$uid_num_2 = count($ar_2);	
			foreach($ar_2 as $one_2){
				$uid_ar_2 .= "@".$one_2['uid'];
				$recode_2 = $one_2['lost_money'];
			}
			$uid_ar_2 = trim($uid_ar_2,'@');
			$ar[] = ['uid_ar'=>$uid_ar_2,'uid_num'=>$uid_num_2,'champion'=>2,'recode'=>$recode_2];
		}else{
			return false;
		}


		$ar_3 = $this->early_slave_M->find_max_earn_week($begin_day,$end_day);
		if($ar_3){
			$uid_ar_3 = '';
			$uid_num_3 = count($ar_3);	
			foreach($ar_3 as $one_3){
				$uid_ar_3 .= "@".$one_3['uid'];
				$recode_3 = $one_3['win_money'];
			}
			$uid_ar_3 = trim($uid_ar_3,'@');
			$ar[] = ['uid_ar'=>$uid_ar_3,'uid_num'=>$uid_num_3,'champion'=>3,'recode'=>$recode_3];
		}else{
			return false;
		}

		return $ar;
	}


/*赛季列表*/
 	public function early_war(){
		(new \app\validate\PageValidate())->goCheck();
		$where = [];
		$page=post("page",1);
		$page_size = post("page_size",10);
 		$data = $this->early_lord_M->war($page,$page_size);
 		$count = $this->early_lord_M->war_count();
       	$res['all_num'] = intval($count);
       	$res['all_page'] = ceil($count/$page_size);
       	$res['page'] = $page;
       	$res['data'] = $data;  
       	return $res; 
 	}

/*赛季查看排名*/
	public function early_war_info(){
	$id = post('id');
	$week_ranking_M = new \app\model\week_ranking();
	$month_ranking_M = new \app\model\month_ranking();
	$early_war_M = new  \app\model\plugin_early_war();

	$early_balance_type = c('early_balance_type');  //find_reward_redis($iden)
    $early_balance_type_cn = find_reward_redis($early_balance_type);

	$ar = $early_war_M->find($id);
	$where['war'] = $ar['war'];
	$res = $early_war_M -> lists_all($where);

		$new_ar = [];
		$one = [];	
		if($res){
		foreach($res as $key=>$sub){
			if($sub['champion']==1){  $champion_cn = '签到最多'; $unit = '次';}
			if($sub['champion']==2){  $champion_cn = '亏损最多'; $unit = $early_balance_type_cn;}
			if($sub['champion']==3){  $champion_cn = '瓜分最多'; $unit = $early_balance_type_cn;}
					$man = user_info($sub['uid_ar']);		
					$one['username'] = $man['username'];
					$one['nickname'] = $man['nickname'];
					$one['avatar'] = $man['avatar'];
					$one['champion'] = $champion_cn;
					$one['recode'] = $sub['recode'];
					$one['unit'] = $unit;
					$one['earn'] = $sub['earn'];
					$new_ar[] = $one; 				
		}		
		}

	return $new_ar;
	}



}