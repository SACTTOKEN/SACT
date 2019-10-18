<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-04-04 16:16:00
 * Desc: 插件-猜猜乐
 */

namespace app\ctrl\admin;

use app\model\plugin_guess_lord as PluginGuessLordModel;
use app\model\plugin_guess_slave as PluginGuessSlaveModel;

use app\validate\IDMustBeRequire;
use app\validate\DelValidate;
use app\validate\PluginguesslordValidate;


class plugin_guess_lord extends BaseController{
	
	public $guess_lord_M;
	public $guess_slave_M;
	public function __initialize(){
		$this->guess_lord_M = new PluginGuessLordModel();
		$this->guess_slave_M = new PluginGuessSlaveModel();
	}

	/*生成下一期号*/
	public function next_stage(){
		$max = $this->guess_lord_M->max('plugin_guess_lord','stage');
		$new_stage = $max+1;
		return $new_stage;
	}


	/*按id查找*/
    public function find(){
    	$id = post('id');
    	(new IDMustBeRequire())->goCheck();
    	$data = $this->guess_lord_M->find($id);
    	empty($data) && error('数据不存在',404);    	
        return $data;      
    }	

    /*保存*/
	public function saveadd(){
		(new PluginguesslordValidate())->goCheck('scene_add');
		$data = post(['stage','begin_time','end_time']);
		$res=$this->guess_lord_M->save_plus($data);
		// var_dump($this->guess_lord_M->log());
		// exit();
		empty($res) && error('添加失败',400);	
		admin_log('添加猜猜乐',$res);    
		return $res;
	}


	/*按id删除*/
	public function del(){
		$id_str = post('id_str');
		(new DelValidate())->goCheck();
		$id_ar = explode('@',$id_str);

		foreach($id_ar as $one){
			$stage = $this->guess_lord_M->find($one,'stage');
			$where['stage'] = $stage;
			$is_have = $this->guess_slave_M->is_have($where);
			empty($is_have) && error('已有人竟猜,不能删除');
		}

		$res=$this->guess_lord_M->del($id_ar);
		empty($res) && error('删除失败',400);
		admin_log('删除猜猜乐',$id_str);    
		return $res;
	}

	/*按id修改*/
	public function saveedit()
	{	
		$id = post('id');
		$stage = $this->guess_lord_M->find($id,'stage');		
    	$data = post(['point','rf']);
		$res=$this->guess_lord_M->up($id,$data);
		$this->guess_slave_M->add_rf($stage,$data['rf']);
		empty($res) && error('修改失败',404);
		admin_log('修改猜猜乐',$id);    
 		return $res; 
	}

	/*结算*/
	public function balance()
	{	
		$id = post('id');
		$stage = post('stage');

		$is_have = $this->guess_lord_M->is_find($id,$stage);
		empty($is_have) && error('期号与ID不匹配',400);
       
		$ar = $this->guess_lord_M->find($id);

		if($ar['point'] == ''){
			error('请录入大盘点数',400);
		}
		if($ar['rf'] == 0){
			error('请录入涨跌',400);
		}
		if($ar['is_end'] == 1){
			error('本期已结算',400);
		}

		if($ar['end_time'] > time()){
			error('未到结算时间',400);
		}

		$rf = $ar['rf'];

		$guess_rate = C('guess_rate');
		//$guess_rate = 100; //千分比

		$sum_up = $this->guess_slave_M->sum_up($stage); //买涨总数
		$sum_down = $this->guess_slave_M->sum_down($stage); //买跌总数

		$data['rate'] = $guess_rate;
		$data['up_all'] = $sum_up ? $sum_up : 0;
		$data['down_all'] = $sum_down ? $sum_down : 0;

		//算服务费  输家总数*平台利率
		if($rf==2){			
			if(floatval($sum_up)==0){ //玩家全赔
				$charge = $sum_down;
				$avg = 0;
			}else{
				$charge = floatval($sum_down) * (floatval($guess_rate)/1000);
				$avg = (floatval($sum_down) - floatval($charge))/floatval($sum_up);
				$avg = round($avg,6); //$avg = sprintf("%.2f",$avg);
			}			
		}

		if($rf==1){ 				
			if(floatval($sum_down)==0){ //玩家全赔
				$charge = $sum_up;
				$avg = 0;
			}else{
				$charge = floatval($sum_up) * (floatval($guess_rate)/1000);
				$avg = (floatval($sum_up) - floatval($charge))/floatval($sum_down);
				$avg = round($avg,6); 
			}
		}

		$data['rf'] = $rf;
		$data['charge'] = $charge;
		$data['avg'] = $avg;
		$data['update_time'] = time();
		$data['is_end'] = 1; //结算状态

		$balance_type = c('guess_balance_type'); //猜猜乐结算类型

        //回滚BEGIN
        $model = new \core\lib\Model();
        $redis = new \core\lib\redis();  

        $model->action();
        $redis->multi();


		$res=$this->guess_lord_M->up($id,$data);//结算END
		empty($res) && error('结算失败',400);

		$this->guess_slave_M->change_by_stage($stage);

		$guess_S = new \app\service\guess();

		if($avg!=0 && $rf==2){
			$guess_S->guess_reward_up($stage,$avg,$balance_type); //奖励发放
		}

		if($avg!=0 && $rf==1){
			$guess_S->guess_reward_down($stage,$avg,$balance_type);//奖励发放
		}

		if($avg==0){   //进slave表判断猜中没，猜中全退
			$guess_S->guess_reward_back($stage,$balance_type);
		}


		$model->run();
        $redis->exec();
        //回滚END

	 	empty($res) && error('修改失败',404);
		 admin_log('结算猜猜乐',$id);    
 		return $res; 
	}


	/*查某一类*/
	public function lists()
	{
		(new \app\validate\AllsearchValidate())->goCheck();
		(new \app\validate\PageValidate())->goCheck();
		$where = [];
		$page=post("page",1);
		$page_size = post("page_size",10);
		$data=$this->guess_lord_M->lists($page,$page_size,$where);
		$count = $this->guess_lord_M->new_count($where);
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data;  
        return $res; 
	}


//================= 以上是基础方法 ==================
//====================以下是赛季排行==================================
	
/*生成上赛季信息,赛季结算 猜猜乐 月猜中次数最多前五名次，可并列*/
	public function my_war()
	{	
		$guess_open_war = c('guess_open_war');
		empty($guess_open_war) && error('请先开启赛季功能',400);

		//1:生成赛季编号和起止时间 OK
		$cycle = renew_c('guess_war_cycle');	
		if($cycle=='自然月'){
   			$begin_time = date('Y-m-01 00:00:00',strtotime('-1 month'));
			$end_time = date("Y-m-d 23:59:59", strtotime(-date('d').'day'));
			$begin_time = strtotime($begin_time);
			$end_time = strtotime($end_time);
			$war_ex = date('Ymd',$begin_time);
        	$war = $war_ex."_m"; 	
        	$money_reward = "guess_month"; //资金奖励 流水类型
        	$remark = "猜猜乐月排行奖励";

        	$where_month['end_time[<>]'] = [$begin_time,$end_time];
        	
			$red_all = $this->guess_lord_M->find_sum('charge',$where_month); //总服务费
		}

		if($cycle == '自然周'){
			$begin_time = strtotime('monday last week');
        	$end_time = strtotime('monday this week')-1; //周日最后一秒  
        	$war_ex = date('Ymd',$begin_time);
        	$war = $war_ex."_w";   
        	$money_reward = "guess_week"; //资金奖励  流水类型
        	$remark = "猜猜乐周排行奖励";

        	$where_week['end_time[<>]'] = [$begin_time,$end_time];
        	$red_all = $this->guess_lord_M->find_sum('charge',$where_week); //总服务费
		}


		//2：判断是否结算
		$begin_day = date('Ymd',$begin_time);
        $end_day = date('Ymd',$end_time);

        	$guess_war_M = new \app\model\plugin_guess_war();
			$is['war']	= $war;
			$is_have = $guess_war_M->is_have($is);		
			$is_have && error("猜猜乐".$begin_day."-".$end_day.'赛季已结算',400);

		//3:有一个函数 
		$where['is_end'] = 1;
		$where['end_time[<>]'] = [$begin_time,$end_time]; 
		$all = $this->guess_lord_M->lists_all($where);
		//cs($this->guess_lord_M->log(),1);
		if($all==false){error("猜猜乐".$begin_day."-".$end_day.'上赛季无人参与',400);}

        $p_1 = c('guess_month_num_1');
        $p_2 = c('guess_month_num_2');
        $p_3 = c('guess_month_num_3');
        $p_4 = c('guess_month_num_4');
        $p_5 = c('guess_month_num_5');

        $guess_slave_M = new \app\model\plugin_guess_slave();  

        $num_top = $guess_slave_M->join_top($begin_time,$end_time); //月猜中次数最多前五名次

        foreach($num_top as $one){
            $count_1[$one['uid']] = $one['join_num']; //参与次数排名
            $win_num = $guess_slave_M->win_num($one['uid'],$begin_time,$end_time);
            $win_num = $win_num ? $win_num : 0;
            $count_2[$one['uid']] = $win_num; //胜局排名
        }

        arsort($count_2);

        $j=1;$pre = '';
        foreach($count_2 as $key=>$val){
            $pre = $pre ? $pre : $val;
            if($val!=$pre){
                $pre = $val;
                $j ++;           
            }
            if($j==6){ break;}else{
                $new_ar[$key] = $j; //名次排名
            }
        }

        $num_ar = array_count_values($new_ar); //每个名次分别有多少人 Array ( [1] => 1 [2] => 2 [3] => 1 [4] => 2 [5] => 1 )
        //cs($num_ar,1);

        $i=1;
        foreach($num_ar as $key=>$val){
            $name = 'p_'.$key;
            $$name = $$name / (100*$val);
            $i++;          
        }

        $money_ar = []; //某个UID分多少钱
        foreach($new_ar as $key=>$val){
            $name = 'p_'.$val;
            $money_ar[$key] = $$name*$red_all;
        }
     	

		//事务回滚BEGIN
		$balance_type = c('guess_balance_type'); //猜猜乐结算类型
   		$guess_war_M = new \app\model\plugin_guess_war();
   		$money_S = new \app\service\money(); //分奖

        $model = new \core\lib\Model();
        $redis = new \core\lib\redis();  
        $model->action();
        $redis->multi();

        foreach($money_ar as $my_uid=>$val){
            //生成记录
            
            $data['war'] = $war;
            $data['begin_time'] = $begin_time;
            $data['end_time'] = $end_time;
            $data['uid'] = $my_uid;
            $data['ranking'] = $new_ar[$my_uid];
            $data['balance_type'] = $balance_type;  
            $data['join_num'] = $count_1[$my_uid];
            $data['win_num']  = $count_2[$my_uid];
            $data['win_percent'] = ($count_2[$my_uid]*100) / $count_1[$my_uid];
            $data['red'] = $val; //分红
            $data['red_all'] = $red_all;

            $back = $guess_war_M->save_by_oid($data);
            //cs($guess_war_M->log(),1);
            $oid = $back['oid'];

            //资金变动       
            $res = $money_S->plus($my_uid,$val,$balance_type,$money_reward,$oid,$my_uid,$remark); 
            empty($res) && error('操作失败',400);
        }

        $model->run();
        $redis->exec();
        //事务回滚END  

        return true;
	}	

	/*赛季列表*/
 	public function guess_war(){
		(new \app\validate\PageValidate())->goCheck();
		$where = [];
		$page=post("page",1);
		$page_size = post("page_size",10);
		$guess_war_M = new \app\model\plugin_guess_war();
		$where['GROUP'] = 'war'; 
		$data = $guess_war_M->lists($page,$page_size,$where);
		$count = $guess_war_M->new_count($where);	
       	$res['all_num'] = intval($count);
       	$res['all_page'] = ceil($count/$page_size);
       	$res['page'] = $page;
       	$res['data'] = $data;  
       	return $res; 
 	}


 	/*赛季查看排名*/
	public function guess_war_info(){
	$id = post('id');
	$guess_war_M = new \app\model\plugin_guess_war();
	$guess_balance_type = c('guess_balance_type');  //find_reward_redis($iden)
    $guess_balance_type_cn = find_reward_redis($guess_balance_type);
	$ar = $guess_war_M->find($id);
	$where['war'] = $ar['war'];
	$res = $guess_war_M -> lists_all($where);
		$new_ar = [];
		$one = [];	
		if($res){
		foreach($res as &$sub){
					$man = user_info($sub['uid']);		
					$sub['username'] = $man['username'];
					$sub['nickname'] = $man['nickname'];
					$sub['avatar'] = $man['avatar'];
					$sub['unit'] = $guess_balance_type_cn;
					$sub['red'] = sprintf("%.2f",$sub['red']);			 
		}		
		}
	return $res;
	}







}