<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-04-12 16:57:20
 * Desc: 猜拳前端
 */
namespace app\ctrl\mobile;

use app\validate\IDMustBeRequire;

use app\model\plugin_finger_lord as PluginFingerLordModel;
use app\validate\PluginFingerValidate;



class plugin_finger extends BaseController
{

    public $finger_M;
    public function __initialize(){
        $this->finger_M = new PluginFingerLordModel();
    }

    public function publish(){

        (new PluginFingerValidate())->goCheck('scene_publish');
        $stake = post('stake');
        $choose_1 = post('choose_1');
        $rate = c('finger_rate');
        $balance_type = renew_c('finger_balance_type'); //money/amount/integral

        //判断是否足够金额发起
        $uid = $GLOBALS['user']['uid'];
        $balance_type_cn = find_reward_redis($balance_type);

        $user_M = new \app\model\user();
        $ar = $user_M->find_me($uid);  

        if(($ar[$balance_type]-$stake)<0){
            error($balance_type_cn.'不足');
        }

        flash_god($uid);
        //事务回滚BEGIN
        $model = new \core\lib\Model();
        $redis = new \core\lib\redis();  
        $model->action();
        $redis->multi();

        //生成记录
        $data['stake'] = $stake;
        $data['user_1'] = $uid;
        $data['choose_1'] = $choose_1;
        $data['rate'] = $rate;
        $data['balance_type'] = $balance_type;  
        $back =$this->finger_M->save_by_oid($data);
        $oid = $back['oid'];
 
        //资金变动
        $money_S = new \app\service\money();
        $remark = "发起猜拳";
        $res = $money_S->minus($uid,$stake,$balance_type,'cuaiquan',$oid,$uid,$remark); 
        empty($res) && error('操作失败',400);

        $model->run();
        $redis->exec();
        //事务回滚END

        return $res;       
    }

    /*请求应战资格*/
    public function glory(){
        (new IDMustBeRequire())->goCheck();
        $uid = $GLOBALS['user']['uid'];
        $finger_id = post('id');
        $user_2 = $uid;
        $ar = $this->finger_M->find($finger_id);

        if(!$ar){
            error('非法请求',404);
        }

        if($ar['user_1']==$uid){
            error('不能挑战自已',400);
        }

        if($ar['user_2']!=$uid){
        if($ar['war']!=0){
             error('已有人抢先一步进入猜拳游戏',400);
         }
        }

        $balance_type = c('finger_balance_type');

        $user_M = new \app\model\user();
        $uid_ar = $user_M->find($uid);  
        $stake = $ar['stake'];

        if(($uid_ar[$balance_type]-$stake)<0){
            error('金额不足',400);
        }

        $oid = $ar['oid'];

        $data['war'] = 1; //应战中。。。
        $data['user_2'] = $uid;
        $res = $this->finger_M->up($finger_id,$data);


        $master['stake'] = $ar['stake'];
        $users=user_info($ar['user_1']);
        $master['avatar'] = $users['avatar'];
        $master['nickname'] = $users['nickname'];
        return $master;
    }

    /*未战退出, 一分钟没有下注应战,则T出挑战资格*/
    public function outgame(){
        //$uid = $GLOBALS['user']['uid'];  
        $finger_id = post('id');
        $ar = $this->finger_M->find($finger_id);

        if($ar['winner']==0){
            $data['war'] = 0; //待战中。。
            $data['user_2'] = 0;
        }
       
        $res = $this->finger_M->up($finger_id,$data);
        return $res;
    }


    /*应战者*/
    public function challenge(){
        (new IDMustBeRequire())->goCheck();
        (new PluginFingerValidate())->goCheck('scene_challenge');
        $uid = $GLOBALS['user']['uid'];
        $finger_id = post('id');
        $user_2 = $uid;
        $choose_2 = post('choose_2');

        $ar = $this->finger_M->find($finger_id);
        $choose_1 = $ar['choose_1'];
        if($ar['user_1']==$uid){
            error('不能挑战自已',400);
        }

        $stake = $ar['stake'];
        $balance_type = $ar['balance_type'];

        //判断是否足够金额  
        $balance_type_cn = find_reward_redis($balance_type);
        $user_M = new \app\model\user();
        $my_ar = $user_M->find($uid);        
        if((floatval($my_ar[$balance_type])-floatval($stake))<0){
            error($my_ar[$balance_type].$balance_type_cn.'不足'.$stake,400);
        }
        
        flash_god($uid);
        //生成记录   
        $data['user_2'] = $user_2;
        $data['choose_2'] = $choose_2;        
        $this->finger_M->up($finger_id,$data);

        //资金变动
        $money_S = new \app\service\money();
        $remark = "挑战猜拳";
        $oid = $ar['oid'];
        $res = $money_S->minus($uid,$stake,$balance_type,'cuaiquan',$oid,$uid,$remark); 
        empty($res) && error('操作失败',400);  

        //挑战结果
        $finger_S = new \app\service\finger();  
        $back = $finger_S->finger_reward($finger_id); 
        return $back;    
    }

    /*配置信息*/
    public function info(){
        $yk_is_open = plugin_is_open('cq');
        $client_is_open = c('finger_open');

        if(empty($yk_is_open) || empty($client_is_open)){
            error('猜拳未开放',10007);   
        }

        $uid = $GLOBALS['user']['uid'];
        $ar['finger_choose_1'] = c('finger_choose_1');
        $ar['finger_choose_2'] = c('finger_choose_2');
        $ar['finger_choose_3'] = c('finger_choose_3');    
 
        $balance_type = c('finger_balance_type');
        $balance_type_cn = find_reward_redis($balance_type);  

        $money = 0;
        if($uid){
            $money = user_info($uid,$balance_type);
        }

        $data['balance_type'] = $balance_type;
        $data['balance_type_cn'] =  $balance_type_cn;
        $data['early_choose'] = $ar;
        $data['money'] = $money;
        $data['finger_rate'] = c('finger_rate');      
        $data['finger_open'] = renew_c('finger_open'); 
        return $data;
    }


    /*猜拳等待挑战列表*/
    public function finger_lists(){

        $yk_is_open = plugin_is_open('cq');
        $client_is_open = c('finger_open');

        if(empty($yk_is_open) || empty($client_is_open)){
            error('猜拳未开放',10009);   
        }

        $ar = $this->finger_M->lists_all(['AND'=>['is_end'=>0,'user_2'=>0]]);
        $new_ar = [];
        foreach($ar as $key=>$one){
            $new_ar[$key]['id'] = $one['id']; 
            $new_ar[$key]['stake'] = $one['stake']; 
            $new_ar[$key]['uid']   = $one['user_1']; 
            $users=user_info($one['user_1']);
            $new_ar[$key]['username'] = $users['username'];
            $new_ar[$key]['avatar']   =  $users['avatar'];
        }
        return $new_ar;
    }



    /*我的猜拳记录*/
    public function finger_history(){
        (new \app\validate\AllsearchValidate())->goCheck();
        $uid = $GLOBALS['user']['uid'];


        $where = ['OR'=>['user_2'=>$uid,'user_1'=>$uid],'AND'=>['is_end'=>1]];
        $page=post("page",1);
        $page_size = post("page_size",10);
        $data=$this->finger_M->lists($page,$page_size,$where);

        foreach($data as &$one){
            $one['username_1']  = user_info($one['user_1'],'username');
            $one['username_2']  = user_info($one['user_2'],'username');
            if($one['winner']==2){
                $one['winner_cn']   = '胜';
            }elseif ($one['winner']==1) {
                $one['winner_cn']   = '负';
            }else{
                $one['winner_cn']   = '平';
            }
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
        unset($one);


        //合二为一
        $vue = [];
        foreach($data as $key=>$rs){
            $balance_type = $rs['balance_type'];
            $balance_type_cn = find_reward_redis($balance_type);
            $vue[$key]['balance_type_cn'] = $balance_type_cn;

            if($uid == $rs['user_1']){
                $vue[$key]['vue_choose_m'] = $rs['choose_1'];
                $vue[$key]['vue_choose_c'] = $rs['choose_2'];

                $vue[$key]['vue_stake'] = $rs['stake'];
                $vue[$key]['vue_time'] = $rs['update_time'];

                $vue[$key]['vue_user_c'] = user_info($rs['user_2'],'username');
              
                if($rs['winner']==1){
                    $vue[$key]['vue_money']  = $rs['earn_1'] + $rs['stake'];
                    $vue[$key]['vue_win']  = '胜';
                }

                if($rs['winner']==3){
                    $vue[$key]['vue_money']  = $rs['stake'];
                    $vue[$key]['vue_win']  = '平';
                }

                if($rs['winner']==2){
                    $vue[$key]['vue_money']  = $rs['stake'];
                    $vue[$key]['vue_win']  = '负';
                }

            }

            if($uid == $rs['user_2']){
                $vue[$key]['vue_choose_m'] = $rs['choose_2'];
                $vue[$key]['vue_choose_c'] = $rs['choose_1'];
                $vue[$key]['vue_stake'] = $rs['stake'];
                $vue[$key]['vue_time'] = $rs['update_time'];

                $vue[$key]['vue_user_c'] = user_info($rs['user_1'],'username');
                 if($rs['winner']==1){
                    $vue[$key]['vue_money']  = $rs['stake'];
                    $vue[$key]['vue_win']  = '负';
                }

                if($rs['winner']==3){
                    $vue[$key]['vue_money']  = $rs['stake'];
                    $vue[$key]['vue_win']  = '平';
                }

                if($rs['winner']==2){
                    $vue[$key]['vue_money']  = $rs['earn_2']+$rs['stake'];
                    $vue[$key]['vue_win']  = '胜';
                }
            }
        }



        $res['data'] = $vue;  
        return $res; 
    }


    /*排行榜*/
    public function month_red(){     

        $finger_open_war = c('finger_open_war');  
        $cycle = c('finger_war_cycle'); 

        $finger_lord_M = new \app\model\plugin_finger_lord(); 

        if($finger_open_war!=1){
            error('赛季奖励未开放',400);
        }     

        if($cycle=='自然月'){
            $begin_time = date('Y-m-01 00:00:00',strtotime('-1 month'));
            $end_time = date("Y-m-d 23:59:59", strtotime(-date('d').'day'));
            $begin_time = strtotime($begin_time);
            $end_time = strtotime($end_time);
            $war_ex = date('Ymd',$begin_time);
            $war = $war_ex."_m";

            $begin_day = date('Ymd',$begin_time);
            $end_day = date('Ymd',$end_time);
         
            $where_month['update_time[<>]'] = [$begin_time,$end_time];
            $charge_up = $finger_lord_M->find_sum('charge',$where_month); //总服务费
            $now = time();
            $where_now['update_time[<>]'] = [$end_time+1,$now];

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

            $where_week['update_time[<>]'] = [$begin_time,$end_time];
            $charge_up =$finger_lord_M->find_sum('charge',$where_week); //总服务费 OK

            
            $now_begin_time = strtotime('monday this week');
            $now = time();
            $where_now['update_time[<>]'] = [$now_begin_time,$now];

            //本期日期
            $ben_begin = date('Ymd',strtotime('monday this week'));
            $ben_end = date('Ymd',strtotime('sunday this week'));
        }

        $where_now['is_end'] = 1;

        $charge_now = $finger_lord_M->find_sum('charge',$where_now); //本赛季累积

        $finger_war_M = new \app\model\plugin_finger_war();

        $where['war'] = $war;
        $where['ORDER'] = ["ranking"=>"ASC"];
        $ar = $finger_war_M ->lists_all($where);

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

        $finger_balance_type = c('finger_balance_type');
        $finger_balance_type_cn = find_reward_redis($finger_balance_type);

        $new_ar['begin_day'] = $ben_begin;
        $new_ar['end_day'] = $ben_end;
        $new_ar['charge_now'] = point($charge_now,$finger_balance_type);
        $new_ar['charge_up'] = point($charge_up,$finger_balance_type) ;
        $new_ar['ar'] = $ar;
        $new_ar['balance_type_cn'] = $finger_balance_type_cn;
        return $new_ar;
    }

}






