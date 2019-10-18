<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-04-04 16:16:00
 * Desc: 插件-猜拳
 */

namespace app\ctrl\admin;

use app\model\plugin_finger_lord as PluginFingerModel;
use app\validate\IDMustBeRequire;
use app\validate\DelValidate;

class plugin_finger_lord extends BaseController{
	
	public $finger_M;
	public function __initialize(){
		$this->finger_M = new PluginFingerModel();
	}

	/*按id查找*/
    public function find(){
    	$id = post('id');
    	(new IDMustBeRequire())->goCheck();
    	$data = $this->finger_M->find($id);
    	empty($data) && error('数据不存在',404);    	
        return $data;      
    }	

	/*按id删除*/
	public function del(){
		$id_str = post('id_str');
		(new DelValidate())->goCheck();
		$id_ar = explode('@',$id_str);
		$res=$this->finger_M->del($id_ar);
		empty($res) && error('删除失败',400);
		admin_log('删除猜拳',$id_str);   
		return $res;
	}

	/*按id修改*/
	public function saveedit()
	{	
		$id = post('id');
    	$data = post(['point','rf']);
		$res=$this->finger_M->up($id,$data);
		empty($res) && error('修改失败',404);
		admin_log('修改猜拳',$id);   
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

		$oid = post('oid');
		$username = post('username');
		$nickname = post('nickname');

		if($oid){
            $where['oid[~]'] = $oid;
        }

		if($username){
            $user_M = new \app\model\user();
            $uid = $user_M->find_mf_uid($username);
            $where['OR']['user_1'] = $uid;
            $where['OR']['user_2'] = $uid;
        }
        if($nickname){
            $user_M = new \app\model\user();
            $uid = $user_M->find_mf_uid_plus($nickname);
            $where['OR']['user_1'] = $uid;
            $where['OR']['user_2'] = $uid;
        }

		$data=$this->finger_M->lists($page,$page_size,$where);



		foreach($data as &$one){
			$users=user_info($one['user_1']);
			$one['username_1']  = $users['username'];
			$one['nickname_1']  = $users['nickname'];


			switch ($one['choose_1']) {
				case '0':
					$one['choose_1_cn'] = '石头';
					break;
				case '1':
					$one['choose_1_cn'] = '剪刀';
					break;
				case '2':
					$one['choose_1_cn'] = '布';
					break;				
			}		


			if($one['user_2']!=0 && $one['winner']!=0){
				$users=user_info($one['user_2']);
				$one['username_2']  = $users['username'];
				$one['nickname_2']  = $users['nickname'];
				if($one['winner']==2){
					$one['winner_cn']   = '胜';
				}elseif ($one['winner']==1) {
					$one['winner_cn']   = '负';
				}else{
					$one['winner_cn']  	= '平';
				}
				
				switch ($one['choose_2']) {
				case '0':
					$one['choose_2_cn'] = '石头';
					break;
				case '1':
					$one['choose_2_cn'] = '剪刀';
					break;
				case '2':
					$one['choose_2_cn'] = '布';
					break;				
				}			
			}
			
		}
		unset($one);

		$count = $this->finger_M->new_count($where);
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data;  
        return $res; 
	}



//====================以下是赛季排行==================================	
/*生成上赛季信息,赛季结算  猜拳上月排行胜次数前五*/
	public function my_war()
	{	
		$finger_open_war = c('finger_open_war');
		empty($finger_open_war) && error('请先开启赛季功能',400);


		//1:生成赛季编号和起止时间 OK
		$cycle = renew_c('finger_war_cycle');	
		if($cycle=='自然月'){
   			$begin_time = date('Y-m-01 00:00:00',strtotime('-1 month'));
			$end_time = date("Y-m-d 23:59:59", strtotime(-date('d').'day'));
			$begin_time = strtotime($begin_time);
			$end_time = strtotime($end_time);
			$war_ex = date('Ymd',$begin_time);
        	$war = $war_ex."_m"; 	

        	$money_reward = "finger_month"; //资金奖励  流水类型
        	$remark = "猜拳月排行奖励";
		}
		if($cycle == '自然周'){
			$money_reward = 'early_week'; //资金奖励  流水类型
			$begin_time = strtotime('monday last week');
        	$end_time = strtotime('monday this week')-1; //周日最后一秒  
        	$war_ex = date('Ymd',$begin_time);
        	$war = $war_ex."_w";   

        	$money_reward = "finger_week"; //资金奖励  流水类型
        	$remark = "猜拳周排行奖励";
		}

		//2：判断是否结算
		$begin_day = date('Ymd',$begin_time);
        $end_day = date('Ymd',$end_time);


        	$finger_war_M = new \app\model\plugin_finger_war();
			$is['war']	= $war;
			$is_have = $finger_war_M->is_have($is);		
			$is_have && error("猜拳".$begin_day."-".$end_day.'赛季已结算',400);

		//3:有一个函数 
		$where['is_end'] = 1;
		$where['update_time[<>]'] = [$begin_time,$end_time]; 
		$all = $this->finger_M->lists_all($where);
		if($all==false){error("猜拳".$begin_day."-".$end_day.'赛季无人参与',400);}

		$charge_all = 0;      
        foreach($all as $one){
            $user_ar[] = $one['user_1'];
            $user_ar[] = $one['user_2'];
        
            if($one['winner']==1){
                $win_ar[] = $one['user_1'];
            }
            if($one['winner']==2){
                $win_ar[] = $one['user_2'];
            }
            $charge_all += floatval($one['charge']);                 
        }
        $count_1 = array_count_values($user_ar);  //求出相同元素出现次数 参与次数  array([19]=>4,[80]=>5,[81]=>5);
        $count_2 = array_count_values($win_ar);  // 用户胜的次数  array([19]=>2,[80]=>1);

        arsort($count_2);

		$j=1;$pre = '';
        foreach($count_2 as $key=>$val){
            $pre = $pre ? $pre : $val;
            if($val!=$pre){
                $pre = $val;
                $j ++;           
            }
            if($j==6){ break;}else{
                $new_ar[$key] = $j;
            }
        }

        $p_1 = c('finger_month_num_1');
        $p_2 = c('finger_month_num_2');
        $p_3 = c('finger_month_num_3');
        $p_4 = c('finger_month_num_4');
        $p_5 = c('finger_month_num_5');

        $num_ar = array_count_values($new_ar); //每个名次分别有多少人 Array ( [1] => 1 [2] => 2 [3] => 1 [4] => 2 [5] => 1 )

        $i=1;
        foreach($num_ar as $key=>$val){
            $name = 'p_'.$key;
            $$name = $$name / (100*$val);
            $i++;          
        }

        $money_ar = [];
        foreach($new_ar as $key=>$val){
            $name = 'p_'.$val;
            $money_ar[$key] = $$name*$charge_all;//前五名次 各分多少钱 array([80] => 40 [81] => 15 [82] => 15 [19] => 20 [18] => 4 [17] => 4 [5] => 2 )
        }

        $balance_type = c('finger_balance_type');        
        $money_S = new \app\service\money();//分奖
        $month_ranking_M = new \app\model\month_ranking();

   		//事务回滚BEGIN
   		$finger_war_M = new \app\model\plugin_finger_war();
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
            $data['red_all'] = $charge_all;

            $back = $finger_war_M->save_by_oid($data);
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
 	public function finger_war(){
		(new \app\validate\PageValidate())->goCheck();
		$where = [];
		$page=post("page",1);
		$page_size = post("page_size",10);
		$finger_war_M = new \app\model\plugin_finger_war();
		$where['GROUP'] = 'war'; 
		$data = $finger_war_M->lists($page,$page_size,$where);
		$count = $finger_war_M->new_count($where);	
       	$res['all_num'] = intval($count);
       	$res['all_page'] = ceil($count/$page_size);
       	$res['page'] = $page;
       	$res['data'] = $data;  
       	return $res; 
 	}

 	
 	/*赛季查看排名*/
	public function finger_war_info(){
	$id = post('id');
	$finger_war_M = new \app\model\plugin_finger_war();

	$early_balance_type = c('early_balance_type');  //find_reward_redis($iden)
    $early_balance_type_cn = find_reward_redis($early_balance_type);

	$ar = $finger_war_M->find($id);
	$where['war'] = $ar['war'];
	$res = $finger_war_M -> lists_all($where);

		$new_ar = [];
		$one = [];	
		if($res){
		foreach($res as &$sub){
					$man = user_info($sub['uid']);		
					$sub['username'] = $man['username'];
					$sub['nickname'] = $man['nickname'];
					$sub['avatar'] = $man['avatar'];
					$sub['unit'] = $early_balance_type_cn;
					$sub['red'] = sprintf("%.2f",$sub['red']);			 
		}		
		}

	return $res;
	}







}