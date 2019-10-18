<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-04-10 14:20:16
 * Desc: 早起签到前台
 */
namespace app\ctrl\mobile;

use app\model\plugin_early_lord as PluginEarlyLordModel;
use app\model\plugin_early_slave as PluginEarlySlaveModel;
use app\model\packet as PacketModel;
use app\model\plugin as PluginModel;
use app\validate\PluginEarlyValidate;
use app\ctrl\mobile\BaseController;

class plugin_early extends BaseController
{
	
    public $early_lord_M;
    public $early_slave_M;
    public $packet_M;
    public $plugin_M;


    public function __initialize(){
        $this->early_lord_M = new PluginEarlyLordModel();
        $this->early_slave_M = new PluginEarlySlaveModel();
        $this->packet_M = new PacketModel();
        $this->plugin_M = new PluginModel();

        $is_open = $this->plugin_M->find_open('zqqd');
        ($is_open == 0) &&  error('未开通使用权限');
    }

    /*最新一期期号（未结算的）*/
    public function stage_one(){
        $data = $this->early_lord_M->stage_now();
        return $data;
    }

    /*当前会员是否有买过某期*/
    public function is_buy(){
        $stage = post('stage');
        $uid = $GLOBALS['user']['uid'];
        $data = $this->early_slave_M->buy_info($uid,$stage);
        $data = $data ? $data : '';
        return $data;
    }


    /*当前状态汇总 1:未开放本期 2: 已开放未参与  3已开放已参与(时间未到)   4已开放已参与(时间到了)未签到(点击签到) 5签到成功  6:已开放未参与(时间到了)
    */
    public function status_now(){
        $uid = $GLOBALS['user']['uid'];
        $flag = 0;
        $stage  = $this->early_lord_M->stage_now();

        $is_have_stage = $this->early_lord_M->find_by_stage($stage);
        
        if(empty($is_have_stage)){
            $flag = 1;
        }else{
            $this_ar = $this->early_slave_M->buy_info($uid,$stage);
           
            if(empty($this_ar)){
                $flag =2;
                    $begin = $is_have_stage['begin_time'];  //1555966800
                    $end = $is_have_stage['end_time'];
                    $now_time = time();
                    if($now_time>=$begin && $now_time<= $end ){
                        $flag = 6;
                    }
            }else{
                $flag = 3;

                if($this_ar['sign_ok']==1){
                    $flag = 5;
                }else{



                    $begin = $is_have_stage['begin_time'];  //1555966800
                    $end = $is_have_stage['end_time'];
                    $now_time = time();
                    if($now_time>=$begin && $now_time<= $end ){
                        $flag = 4;
                    }
                }
            }
        }
        return $flag;
    }



    /*签到页信息*/
    public function info(){
        $uid = $GLOBALS['user']['uid'];
        $stage_now = $this->early_lord_M->stage_now();
        $stage_now_id = $this->early_lord_M->have(['stage'=>$stage_now],'id');

   
        $stage_pre = $this->early_lord_M->stage_pre($stage_now_id);

        $join_money = $this->early_slave_M->join_all_m($stage_now); //参与总额 瓜分奖金
        $join_man = $this->early_slave_M->join_man_m($stage_now); //参与人数

        $early_war_M = new \app\model\plugin_early_war();

        $cycle = renew_c('early_war_cycle');

        if($cycle=='自然月'){
            $begin_time = date('Y-m-01 00:00:00',strtotime('-1 month'));
            $end_time = date("Y-m-d 23:59:59", strtotime(-date('d').'day'));
            $begin_time = strtotime($begin_time);
            $end_time = strtotime($end_time);
            $war_ex = date('Ymd',$begin_time);
            $war = $war_ex."_m";
        }else{
            $begin_time = strtotime('monday last week');
            $end_time = strtotime('monday this week')-1; //周日最后一秒  
            $war_ex = date('Ymd',$begin_time);
            $war = $war_ex."_w";
        }        



        $ranking = $early_war_M->ranking($war); //赛季排行


        $uid_info = $this->early_slave_M->buy_info($uid,$stage_pre);

        $continu_num = $uid_info['continu_num'];
        $early_continu = C('early_continu');

        $early_coupon = C('early_coupon');
        $coupon_ar = [];
        if($early_coupon){
            $coupon_ar = $this->packet_M->find($early_coupon);
            //cs($this->packet_M->log(),1);
        }

        $stage_pre_ar = $this->early_lord_M->find_by_stage($stage_pre); //上期信息
        $slave_ar = $this->early_slave_M->buy_info($uid,$stage_now); //本期个人信息
               
        $data['stage_now'] = $stage_now;
        $data['join_money'] = $join_money ? $join_money : 0; 
        $data['join_man'] = $join_man ? $join_man : 0;
        $data['early_continu'] =  $early_continu ?  $early_continu : 0; 
        $data['continu_num'] = $continu_num ? $continu_num : 0; //连续打卡次数
        $data['coupon_ar'] = $coupon_ar;
        $data['ranking'] = $ranking;
        $data['stage_pre_ar'] = $stage_pre_ar ? $stage_pre_ar : ['join_man'=>0,'sign_man'=>0];

        $data['early_balance_type'] = c('early_balance_type');  //find_reward_redis($iden)
        $data['early_balance_type_cn'] = find_reward_redis($data['early_balance_type']);  

        $early_time = c('early_time');
        $early_time_ar =  explode('|',$early_time);
        $data['early_begin_time'] = $early_time_ar[0];
        $data['early_end_time'] =   $early_time_ar[1]; //08:00 
        $ar['early_choose_1'] = c('early_choose_1');
        $ar['early_choose_2'] = c('early_choose_2');
        $ar['early_choose_3'] = c('early_choose_3');
        $ar['early_choose_4'] = c('early_choose_4'); //可买值
        $data['early_choose'] = $ar;
        $flag = $this->status_now();

         switch ($flag) {
            case '1':
                $status_now = "未开放";
                break;
            case '2':
                $status_now = "支付金额参与挑战";
                break;  
            case '3':
                $status_now = "已发起挑战，等待开始签到";
                break;
            case '4':
                $status_now = "点击签到";
                break;
            case '5':
                $status_now = "签到成功";
                break;  
            case '6':
                $status_now = "未发起挑战，等待下期发起";    
                break;                
            default:
                $status_now = "未开放";
                break;
        }
        $data['status_now'] = $status_now;
        $data['flag'] = $flag;

        if(empty($slave_ar)){$slave_ar=[];}
        $data['slave_ar']  = $slave_ar;
        return $data;
    }


    /*早起签到投注*/
    public function buy(){
        (new PluginEarlyValidate())->goCheck('scene_buy');
        $uid = $GLOBALS['user']['uid'];
        //判断是否可以投注
        $early_open = c('early_open');
        if($early_open!=1){error('活动暂未开放',400);}

        $is_buy = $this->early_lord_M->stage_is_buy();
        if(!$is_buy){
            error('签到时间不可投注',400);
        }

        //是否有这一期签到
        $stage  = $this->early_lord_M->stage_now();
        $is_have_stage = $this->early_lord_M->find_by_stage($stage);
        empty($is_have_stage) && error('未开放签到活动',400);

        $this_ar = $this->early_slave_M->buy_info($uid,$stage);
        if($this_ar){error('不可重复投注',400);}
        

        //判断是否足够金额下注  
        $early_balance_type = c('early_balance_type'); //money/amount/integral
        $balance_type_cn = find_reward_redis($early_balance_type);
        $user_M = new \app\model\user();
        $ar = $user_M->find_me($uid);        
        $stake = post('stake'); //购买值,下注
        if(($ar[$early_balance_type]-$stake)<0){
            error($balance_type_cn.'不足');
        }

        /*写入主表数据*/
        $sign_time_ar = $this->early_lord_M->sign_time();
        $data['uid'] = $uid;
        $data['stake'] = $stake;
        $data['stage'] = $stage;
        $data['sign_begin_time'] = $sign_time_ar['begin'];
        $data['sign_end_time'] = $sign_time_ar['end']; //签到时间戳


        $config_continu = c('early_continu');
        $early_coupon_id= c('early_coupon');   
        flash_god($uid);
        //事务BEGIN
        $model = new \core\lib\Model();
        $redis = new \core\lib\redis();  
        $model->action();
        $redis->multi();

        /*判断是否连续签到，上期期号*/    
        $last_sign_stage = $this->early_lord_M->stage_pre($stage);
        $data['last_sign_stage'] = $last_sign_stage; //上期期号

        $data['continu_num'] = 0;

        if($last_sign_stage){
            $last_ar = $this->early_slave_M->buy_info($uid,$last_sign_stage);//上期是否投注
            if($last_ar){
                $data['continu_num'] = $last_ar['continu_num'] + 1;
            }        
        }    

        $ar=$this->early_slave_M->save_by_oid($data);
        empty($ar) && error('添加下注失败',400);

        /*修改主表里的总下注额，和总人数*/
        $data_lord['join_all'] = $is_have_stage['join_all'] + $stake;
        $data_lord['join_man'] = $is_have_stage['join_man'] + 1;
        $lord_res = $this->early_lord_M->up($is_have_stage['id'],$data_lord);
        empty($lord_res) && error('添加下注总额失败',400);


        /*满足连续签到多少次送红包*/     
        if($data['continu_num']==$config_continu && $config_continu!=0){        
            $config_early_coupon  = $this->packet_M->find($early_coupon_id,'title');
            $coupon_S = new \app\service\coupon();
            $res = $coupon_S->get_coupon($uid,$config_early_coupon); //发放红包
            empty($res) && error('添加失败',400);
            $change['continu_num'] = 0;
            $this->early_slave_M->up($ar['id'],$change); //发完红包连续次数归0
        }

        /*记录流水*/
        $oid = $ar['oid'];
        $remark = $data['stage']."期投注早起签到";
        $money_S = new \app\service\money();
        $res = $money_S->minus($uid,$stake,$early_balance_type,'zqqd',$oid,$uid,$remark); 
        empty($res) && error('记录流水失败',400); 

        $model->run();
        $redis->exec();
        //事务 回滚END     
        return $res;
    }


    //签到
    public function sign_in(){
        //是否签到时间范围
        $is_sign_time = $this->early_lord_M->stage_is_sign();
        if(!$is_sign_time){error('亲,不在签到时间内',400);}
        $stage =  $this->early_lord_M->stage_now();
        $uid = $GLOBALS['user']['uid'];
        $res = $this->early_slave_M->is_sign($uid,$stage);
        if($res){
            error('请勿重复签到',400);
        }

        //回滚BEGIN    
        $model = new \core\lib\Model();
        $redis = new \core\lib\redis();  
        $model->action();
        $redis->multi();

        $bool = $this->early_slave_M->sign_me($uid,$stage);
        empty($bool) && error('签到失败',400);
        $ar = $this->early_lord_M->find_by_stage($stage);
        $sign_man = $ar['sign_man'] +1;
        $sign_all = $ar['sign_all'] + $res['stake'];
        $up['sign_man'] = $sign_man;
        $up['sign_all'] = $sign_all;
        $bool2 = $this->early_lord_M->up($ar['id'],$up);
        empty($bool2) && error('更新签到数失败',400);

        $model->run();
        $redis->exec();
        //回滚END

        return $bool;
    }    


    /*我的签到记录列表(我的战绩)*/
    public function history(){
        (new \app\validate\AllsearchValidate())->goCheck();
        $balance_type = c('early_balance_type'); //结算类型

        $uid = $GLOBALS['user']['uid'];
        $where['uid'] = $uid;
    
        $page=post("page",1);
        $page_size = post("page_size",10);  
        $data =$this->early_slave_M->lists_ranking($page,$page_size,$where);

        $reward_iden = C("early_balance_type");
        $reward_M = new \app\model\reward();
        $reward = $reward_M->find_redis($reward_iden); //结算类型，奖励方式

        foreach($data as &$one){ 
            $one['reward'] = $reward;
            if($one['sign_ok']==1){
                $one['final'] = $one['earn'] + $one['stake']; //连本带利
            }else{
                $one['final'] = '-'.$one['stake']; //输掉了
            }
            $one['final'] = point($one['final'],$balance_type);
            $one['earn'] = point($one['earn'],$balance_type);
        }
        unset($one);

        $all = $this->early_slave_M->lists_all($where);
            $all_in = 0;
            $all_earn = 0;
            $all_ok = 0;

        foreach($all as $one){
            $back = 0;
            $all_in += $one['stake'];
            $all_ok += $one['sign_ok'];       
            if($one['sign_ok']==1){
                $back = $one['earn']; //连本带利
                $all_earn += $back;
            }   
        }

        $res['all_in'] = point($all_in,$balance_type);     //累计投入
        $res['all_earn'] = point($all_earn,$balance_type);//累计赚取
        $res['all_ok'] = $all_ok;   //成功打卡次数
      
        $res['data'] = $data; 

        return $res;

    }



    /*签到分红排行,新增周排行*/
    public function month_red(){

        $cycle = renew_c('early_war_cycle');    
        if($cycle=='自然月'){
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
            $where_month['is_end'] = 1;
            $red_all = $this->early_lord_M->find_sum('charge',$where_month); //总服务费

            $where_now['stage[~]'] = date('Ym',time());
            //本期日期
               $ben_begin = date('Ym01');
               $BeginDate=date('Y-m-01', strtotime(date("Y-m-d")));
               $ben_end = date('Ymd', strtotime("$BeginDate +1 month -1 day"));       
        }
        if($cycle == '自然周'){
            $begin_time = strtotime('monday last week');
            $end_time = strtotime('monday this week')-1; //周日最后一秒  
            $war_ex = date('Ymd',$begin_time);
            $war = $war_ex."_w";

            $begin_day = date('Ymd',$begin_time);
            $end_day = date('Ymd',$end_time);
            $where_week['stage[<>]'] = [$begin_day,$end_day];
            $where_week['is_end'] = 1;
            $red_all = $this->early_lord_M->find_sum('charge',$where_week); //总服务费 OK

             $now_begin_time = strtotime('monday this week');
             $now_begin_day  = date('Ymd',$now_begin_time);
             $now_end_day = date('Ymd');
             $where_now['stage[<>]'] = [$now_begin_day,$now_end_day];

            //本期日期
            $ben_begin = date('Ymd',strtotime('monday this week'));
            $ben_end = date('Ymd',strtotime('sunday this week'));
        }

        $charge_up = $red_all; //上赛季总服务费 

        $where_now['is_end'] = 1;
        $charge_now = $this->early_lord_M->find_sum('charge',$where_now); //本赛季累积

        $early_war_M = new \app\model\plugin_early_war();
        $where['war'] = $war;
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
                    $one['rating_cn'] = $man['rating_cn'];
                    $one['ranking_info'] = $champion_cn;
                    $one['champion'] = $sub['champion'];
                    $one['recode'] = $sub['recode'];
                    $one['unit'] = $unit;
                    $one['earn'] = $sub['earn'];
                    $new_ar[] = $one;               
        }       
        }

        if(!empty($new_ar)){
            foreach($new_ar as $key=>$val){
                if($val['champion']==1){
                     $ar_1[] = $val;
                }
                if($val['champion']==2){
                     $ar_2[] = $val;
                }
                if($val['champion']==3){
                     $ar_3[] = $val;
                }
            }
        }

        $early_balance_type = c('early_balance_type');
        $early_balance_type_cn = find_reward_redis($early_balance_type);



        $array = [$ar_1,$ar_2,$ar_3];
        $new_ar['begin_day'] = $ben_begin;
        $new_ar['end_day'] = $ben_end;
        $new_ar['charge_now'] = point($charge_now,$early_balance_type);
        $new_ar['charge_up'] = point($charge_up,$early_balance_type);
        $new_ar['ar'] = $array;
        $new_ar['early_balance_type_cn'] = $early_balance_type_cn;

        return $new_ar;
    }




}