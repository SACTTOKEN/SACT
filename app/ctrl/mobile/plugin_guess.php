<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-04-06 17:01:29
 * Desc: 猜猜乐前台
 */
namespace app\ctrl\mobile;

use app\model\plugin_guess_lord as PluginGuessLordModel;
use app\model\plugin_guess_slave as PluginGuessSlaveModel;
use app\validate\PluginguesslordValidate;

class plugin_guess extends BaseController
{
	
    public $guess_lord_M;
    public $guess_slave_M;
	public function __initialize(){
        if(!plugin_is_open('ccn')){
            error('暂未开放',10007);
        }
		$this->guess_lord_M = new PluginGuessLordModel();
        $this->guess_slave_M = new PluginGuessSlaveModel();

        if(isset($GLOBALS['user']['uid'])){
            $uid = $GLOBALS['user']['uid']; 
        }
        empty($uid) && error('请登录',606);
	}

    public function stage_one(){
        $data = $this->guess_lord_M->stage_now();
        return $data;
    }

    /*当前会员是否有买过某期*/
    public function is_buy(){
        $stage = post('stage');
        $uid = $GLOBALS['user']['uid'];
        $data = $this->guess_slave_M->buy_info($uid,$stage);
        $data = $data ? $data : '';
        return $data;
    }



    public function buy(){
        
        (new PluginguesslordValidate())->goCheck('scene_buy');
        
        //判断时间 和 是否结算
        $stage = post('stage'); 
        $stage_ar = $this->guess_lord_M->find_by_stage($stage);
        if($stage_ar['begin_time']<=time() && $stage_ar['end_time']>=time()){    
        }else{
            error('已过购买期',400);
        }
        if($stage_ar['is_end'] == 1){
            error('该期已结算,请买下一期',400);
        }

        $uid = $GLOBALS['user']['uid'];

        $is_buy = $this->guess_slave_M->buy_info($uid,$stage);
        if($is_buy){
            error('已经购买过了',400);
        }

        $guess_balance_type = C('guess_balance_type'); //money/amount/
        $balance_type_cn = find_reward_redis($guess_balance_type);
        $user_M = new \app\model\user();

        $ar = $user_M->find($uid);
        
        $buy_up = post('buy_up');
        $buy_down = post('buy_down');

        if($buy_down>0){$money = $buy_down;}    
        if($buy_up>0){$money = $buy_up;}  

        if(($ar[$guess_balance_type]-$money)<0){
            error($balance_type_cn.'不足',10003);
        }

        $data = post(['stage','buy_type','buy_up','buy_down']);
        $data['uid'] = $uid;
        (new PluginguesslordValidate())->goCheck('scene_buy');
 
        flash_god($uid);
        //回滚BEGIN
        $model = new \core\lib\Model();
        $redis = new \core\lib\redis();  

        $model->action();
        $redis->multi();

        $ar=$this->guess_slave_M->save_by_oid($data); 
        $oid = $ar['oid'];
        $remark = $data['stage']."期投注猜猜乐";
        $money_S = new \app\service\money();

        $res = $money_S->minus($uid,$money,$guess_balance_type,'ccl',$oid,$uid,$remark); //记录资金流水
        empty($res) && error('添加失败1',400);  

        if($buy_up>0){   
            $up_all =  $stage_ar['up_all'] + $buy_up;
            $res = $this->guess_lord_M->up($stage_ar['id'], ['up_all'=>$up_all]);
            empty($res) && error('添加失败2',400);  
        }

        if($buy_down>0){   
            $down_all =  $stage_ar['down_all'] + $buy_down;
            $res = $this->guess_lord_M->up($stage_ar['id'], ['down_all'=>$down_all]);
            empty($res) && error('添加失败3',400);  
        }

        $model->run();
        $redis->exec();
        //回滚END

        return $res;
    }


    /*排行榜*/
    public function ranking_list(){
        $ar = $this->guess_slave_M->ranking();
        $new_ar = [];
        if(!empty($ar)){
            foreach($ar as $one){
                $users=user_info($one['uid']);
                $new_one = [];
                $new_one['username'] = $users['username'];
                $new_one['uid'] = $one['uid'];
                $new_one['win'] = $one['win'];
                $new_one['win_num'] = $one['win_num'];
                $new_one['avatar'] = $users['avatar'];
                $new_one['rating_cn'] = $users['rating_cn'];
                $new_ar[] =  $new_one;
            }
        }      
        return $new_ar;
    }


    /*我的购买记录列表*/
    public function history(){
        (new \app\validate\AllsearchValidate())->goCheck();
        $uid = $GLOBALS['user']['uid'];
        $where['uid'] = $uid;
        
        $page=post("page",1);
        $page_size = post("page_size",10);  
        $data =$this->guess_slave_M->lists($page,$page_size,$where);

        $reward_iden = C("guess_balance_type");

        $reward_M = new \app\model\reward();
        $reward = $reward_M->find_redis($reward_iden);

        foreach($data as &$one){
            $one['reward'] = $reward;

            if($one['rf']==0){
                $one['gameover'] = '等待结果';
            }else{
                if($one['rf'] == $one['buy_type']){
                    $one['gameover'] = '猜对了';
                }else{
                    $one['gameover'] = '猜错了';                            
                }
            }
        }

    
        $res['data'] = $data; 

        return $res;

    }

    /*我的购买胜率*/
    public function victor(){
        $uid = $GLOBALS['user']['uid'];
        $where['uid'] = $uid; 
        $num =$this->guess_slave_M->new_count($where);
        $where2['uid'] = $uid;
        $where2['earn[>]'] = 0; 
        $num2 = $this->guess_slave_M->new_count($where2);

        if($num != 0){
            $win_per = intval($num2*100/$num); //百分比的分子
        }else{
            $num = 0;
        }
        
        $data['num'] = $num;
        $data['win_per'] = $win_per;
        $data['rating_cn'] = user_info($uid,'rating_cn');
        return $data;
    }


    //猜猜乐信息
    public function guess_info(){


        $flag = 0;   //0:未开始 1：本期内未买   2:本期内已买   3:已买未揭晓 4:已买已揭晓   5:未买未揭晓 6:未买已揭晓

        $uid = $GLOBALS['user']['uid'];

        $guess_balance_type = C('guess_balance_type'); //money/amount/
        $balance_type_cn = find_reward_redis($guess_balance_type);
        $user_M = new \app\model\user();
        $ar = $user_M->find_me($uid);

        $back['money'] = $ar[$guess_balance_type];
        $back['money_cn'] = $balance_type_cn;

        $stage_now = $this->guess_lord_M->stage_now();//是否本期内

        //上期瓜分金额begin 已结算的最后一期
        $where['is_end'] =1;   
        $where['LIMIT'] =1;
        $where['ORDER'] = ["id"=>"DESC"]; 
        $ar_up = $this->guess_lord_M->have($where); //上期记录
        if($ar_up){
            if($ar_up['rf']==1){
                $guafen = $ar_up['up_all'];
            }
            if($ar_up['rf']==2){
                $guafen = $ar_up['down_all'];
            }
        }else{
            $guafen = 0;
        }   
        //上期瓜分金额END 
        if($stage_now){
            $stage = $stage_now['stage'];          
            $buy_info = $this->guess_slave_M->buy_info($uid,$stage);//是否有买  

            if(!$buy_info){
                $flag = 1;
                $back['info'] = '';
                $back['flag'] = $flag;
                $back['stage'] = $stage_now;  
                        
            }else{
                $flag = 2;
                $buy_type_cn = ($buy_info['buy_type']==1) ? '跌':'涨';
                $back['info'] = "你已预言".$buy_type_cn."@等待揭晓大盘结果！";
                $back['flag'] = $flag;
                $back['stage'] = $stage_now;
            } 

        }else{

            $stage_mid = $this->guess_lord_M->stage_mid(); //区间期
            $back['stage'] = $stage_mid;

            if(!$stage){
                $flag = 0;
                $back['info'] = "未开放";
                $back['flag'] = $flag;
            }else{        
                $stage = $stage_mid['stage'];
                $buy_info = $this->guess_slave_M->buy_info($uid,$stage);//是否有买

                if($stage_mid['rf']==0 && !empty($buy_info)){
                    $flag = 3;
                    $back['info'] = "竞猜时间已截止,你已预言".$buy_type_cn."@等待揭晓大盘结果！";
                    $back['flag'] = $flag;
                }

                if($stage_mid['rf']!=0 && !empty($buy_info) && $stage_mid['is_end']==1){
                    $flag = 4;
                    $rf = $stage_mid['rf']==1 ? '跌':'涨';           
                    if($buy_info['buy_type'] == $stage_mid['rf']){
                        $back['info'] = "大盘结果：".$rf.",恭喜你竟猜成功获得 ".$buy_info['earn']." ".$balance_type_cn;  
                    }else{
                        $back['info'] = "大盘结果：".$rf.",竟猜失败";
                    }               
                    $back['flag'] = $flag;
                }

                if($stage_mid['rf']==0 && empty($buy_info)){
                    $flag = 5;
                    $back['info'] = "竞猜时间已截止@等待揭晓大盘结果！";
                    $back['flag'] = $flag;
                }            

                if($stage_mid['rf']!=0 && empty($buy_info)){
                    $flag = 6;
                    $rf = $stage_mid['rf']==1 ? '跌':'涨';
                    $back['info'] = "大盘结果：".$rf.",等待下一期";
                    $back['flag'] = $flag;
                }
            }
        }

        //上证指数 
        $szzs = https_request('http://hq.sinajs.cn/list=s_sh000001');  //var hq_str_s_sh000001="上证指数,3270.7973,20.5961,0.63,3151980,31513192";
        $szzs = iconv("GB2312", "UTF-8", $szzs); 
        if($szzs){
             $szzs_ar = explode('"',$szzs);
             $szzs_1= $szzs_ar[1];
             if($szzs_1){
                $szzs_ar2 = explode(',',$szzs_1);
             }
             $szzs_2  = $szzs_ar2[1];
             $back['szzs'] = $szzs_2;
        }

        //我的胜率和战绩 所有的
        

        $where_1['uid'] = $GLOBALS['user']['uid'];
        $join_num = $this->guess_slave_M->new_count($where_1);

        $where_2['AND']['uid'] = $GLOBALS['user']['uid'];
        $where_2['AND'] = ['buy_type[=]rf'] ;
        $win_num = $this->guess_slave_M->new_count($where_2);

        if($join_num >0){
            $win_percent = ($win_num*100)/$join_num;
        }else{
            $join_num = 0;
            $win_num =0;
            $win_percent = 0;
        }

        $back['join_num'] = $join_num;
        $back['win_num'] = $win_num;
        $back['win_percent'] = $win_percent;
        $back['guafen'] = point($guafen,$guess_balance_type);
        return $back;
    }



     /*月分红*/
    public function month_red(){

        $guess_lord_M = new \app\model\plugin_guess_lord();
        $guess_open_war = c('guess_open_war');  
        $cycle = c('guess_war_cycle');          
  
        if($cycle=='自然月'){
            $begin_time = date('Y-m-01 00:00:00',strtotime('-1 month'));
            $end_time = date("Y-m-d 23:59:59", strtotime(-date('d').'day'));
            $begin_time = strtotime($begin_time);
            $end_time = strtotime($end_time);     
            $war_ex = date('Ymd',$begin_time);
            $war = $war_ex."_m";   
            $where_month['end_time[<>]'] = [$begin_time,$end_time];            
            $charge_up = $guess_lord_M->find_sum('charge',$where_month); //上期总服务费
            $now_begin_time = $end_time + 1;
            $now = time();
            $ben_begin = date('Ym01');
            $ben_end = date('Ymd', strtotime("$ben_begin +1 month -1 day")); 
        }

        if($cycle == '自然周'){
            $begin_time = strtotime('monday last week');
            $end_time = strtotime('monday this week')-1; //周日最后一秒  
            $war_ex = date('Ymd',$begin_time);
            $war = $war_ex."_w";   
            $where_week['end_time[<>]'] = [$begin_time,$end_time];
            $charge_up = $guess_lord_M->find_sum('charge',$where_week); //上期总服务费

            $now_begin_time = strtotime('monday this week');
            $now = time();

            $ben_begin = date('Ymd',strtotime('monday this week'));
            $ben_end = date('Ymd',strtotime('sunday this week'));       
        }

        $where_now['update_time[<>]'] = [$now_begin_time,$now];
        $where_now['is_end'] = 1;
        $charge_now = $guess_lord_M->find_sum('charge',$where_now); //本赛季累积已结算的服务费 
     

        $guess_war_M = new \app\model\plugin_guess_war();

        $where['war'] = $war;
        $where['ORDER'] = ["ranking"=>"ASC"];
        $ar = $guess_war_M ->lists_all($where);

        if($ar){
            foreach($ar as &$one){
                $u_ar = user_info($one['uid']);
                $one['username'] = $u_ar['username'];
                $one['nickname'] = $u_ar['nickname'];
                $one['avatar'] = $u_ar['avatar'];
                $one['rating_cn'] = $u_ar['rating_cn'];
            }
        }else{
            $charge_up = 0;
            $ar = [];
        }  
    
        $guess_balance_type = c('guess_balance_type');
        $guess_balance_type_cn = find_reward_redis($guess_balance_type); 

        $new_ar['begin_day'] = $ben_begin;
        $new_ar['end_day'] = $ben_end;
        $new_ar['charge_now'] = point($charge_now,$guess_balance_type);
        $new_ar['charge_up'] = point($charge_up,$guess_balance_type);
        $new_ar['ar'] = $ar;
        $new_ar['balance_type_cn'] = $finger_balance_type_cn;
        return $new_ar;
    }
    


}