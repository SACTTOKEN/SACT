<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-07-25 17:00:14
 * Desc: 大转盘前端
 */
namespace app\ctrl\mobile;

use app\model\big_wheel as BigWheelModel;
use app\model\big_wheel_config as BigWheelConfigModel;
use app\model\big_wheel_win as BigWheelWinModel;
use app\validate\BigWheelValidate;
use app\ctrl\mobile\BaseController;
use app\validate\IDMustBeRequire;

class plugin_big extends BaseController
{
    public $big_wheel_M;
    public $pbw_win_M;
    public $pbw_config_M;

    //分享后，支付后，评价后 （场景）
    public function __initialize(){
     
        $this->big_wheel_M = new BigWheelModel();
        $this->pbw_win_M = new BigWheelWinModel();
        $this->pbw_config_M  = new BigWheelConfigModel();
    }

    //时间段内，活动场景 找到合适的活动ID
    public function ask(){
       if(!plugin_is_open('dzp')){
            return false;
        }
        (new BigWheelValidate())->goCheck();
        $uid = $GLOBALS['user']['id'];
        $con = post('con');
        $order_id  = post('oid','');

        if($order_id){
            $where_order['order_id'] = $order_id;
            $is_have = $this->big_wheel_M->is_have($where_order);
            if($is_have){
                return false;
            }
        }

        $where['con'] = $con;
        $where['begin_time[<]'] = time();
        $where['end_time[>]'] = time();
        $ar = $this->pbw_config_M->have($where);

        if(!$ar){
            return false;
        }
        $hd_id = $ar['id'];
        $is_allow = $this -> is_allow($uid,$hd_id);

        if($is_allow){
                $res['hd_id'] = $hd_id;
                $res['hd_readme'] = $ar['readme'];
            return $res;
        }else{
            return false;
        }
    }

    //等级限制,次数限制 来判断是否有资格参加
    public function is_allow($uid,$hd_id){
       if(!plugin_is_open('dzp')){
            return false;
        }
        $ar = $this->pbw_config_M->find($hd_id);
        $where['uid'] = $uid;
        $join_num = $this->big_wheel_M->new_count($where);
        $where2['uid'] = $uid;
        $where2['created_time[>]'] = strtotime(date('Y-m-d 00:00:00'));
        $today_num = $this->big_wheel_M->new_count($where2);
        $flag_1 = true;
        if($ar['join_limit'] == '一天一次'){
            if($today_num>=1){
                $flag_1 = false;
            }
        }
        if($ar['join_limit'] == '一人一次'){
            if($join_num>=1){
                $flag_1 = false;
            }
        }
        if($ar['join_limit'] == '一天两次'){
            if($today_num>=2){
                $flag_1 = false;
            }
        }
        if($ar['join_limit'] == '不限参与次数'){
            $flag_1 = true;
        }

        $flag_2 = true;
        $rating = user_info($uid,'rating');

        if($ar['is_only_rating']==1){
            if($ar['rating'] != $rating){
                $flag_2 = false;
            }
        }elseif($ar['rating'] > $rating){
                $flag_2 = false;
        }

        if($flag_1 && $flag_2){
            return true;
        }else{
            return false;
        }
    }

    public function play(){
      	if(!plugin_is_open('dzp')){
            return false;
        }
        (new IDMustBeRequire())->goCheck();
        $money_S = new \app\service\money();
        $uid = $GLOBALS['user']['id'];
        $id  = post('id'); //活动ID plugin_big_config表ID
        
        
        $order_id  = post('oid','');
        if($order_id){
            $where_order['order_id'] = $order_id;
            $is_have = $this->big_wheel_M->is_have($where_order);
            if($is_have){
                return false;
            }
        }
        flash_god($uid);
        $ar = $this->pbw_config_M->find($id);

        $where['bid'] = $id;
        $reward = $this->pbw_win_M->lists_all($where);

        empty($reward) && error('活动不存在',400);

        foreach($reward as $one){
            if($one['lv']==1){
                $percent_1 = $one['win_percent'];    
            }
            if($one['lv']==2){
                $percent_2 = $one['win_percent'];        
            }
            if($one['lv']==3){
                $percent_3 = $one['win_percent'];
            }
        }
      
        $my_rating = user_info($uid,'rating');
        $lv = $this->win_rating($id,$my_rating,$percent_1,$percent_2,$percent_3); //获得中奖等级

        $model = new \core\lib\Model();
        $redis = new \core\lib\redis();  
        $model->action();
        $redis->multi();

            $data_add['join_num[+]'] = 1;
            $this->pbw_config_M->up($id,$data_add);//参与人数加一
            
            if($lv>0){
            $where2['lv'] = $lv;
            $where2['bid'] = $id;    
            $this->pbw_config_M->up($id,['win_num[+]'=>1]);//领奖人数加一
            $earn = $this->pbw_win_M->have($where2); 
            $money = $earn['score'];
            $balance_type = $earn['balance_type'];
            $oid = $ar['oid'];
            $remark = '大转盘中奖';
            $data['uid'] = $uid;
            $data['join_time'] = time();
            $data['win_title'] = $earn['win_title'];
            $data['score'] = $money;
            $data['score_balance_type'] = $balance_type;
            $data['is_win'] = 1;
            if($order_id!=''){
                  $data['order_id'] = $order_id; //支付后的订单号,存储以解决
            }
            $this->big_wheel_M->save($data);
            $money_S->plus($uid,$money,$balance_type,'dazhuanpan',$oid,$uid,$remark);
            }

        $model->run();
        $redis->exec();
    
        if($lv==0){
            $res['win_say'] = $ar['not_win_say'];
            $res['money'] = 0;
            $res['balance_type'] = '';
        }else{
            $res['win_say'] = $earn['win_say'];
            $res['money'] = $money;
            $balance_type_cn = find_reward_redis($balance_type);
            $res['balance_type'] = $balance_type_cn;
        }
        $res['lv'] = $lv;      
        return $res;
    }


    //反复循环到满足条件为止  什么等级以上才能中这个奖
    public function win_rating($hd_id,$uid_lv,$percent_1,$percent_2,$percent_3){
        $num = mt_rand(1,100);

        $a1 = 1;
        $b1 = $percent_1;
        $a2 = $b1;
        $b2 = $percent_1 + $percent_2;
        $a3 = $b2;
        $b3 = $b2 + $percent_3;
        $lv = 0;

        if($num<= $b1 && $num>$a1){$lv = 1;}
        if($num<= $b2 && $num>$a2){$lv = 2;}
        if($num<= $b3 && $num>$a3){$lv = 3;}

        $where['bid']= $hd_id;
        $where['lv'] = $lv;
        $ar = $this->pbw_win_M->have($where);

        if($ar['rating'] > $uid_lv){
            return $this->win_rating($hd_id,$uid_lv,$percent_1,$percent_2,$percent_3);
        }else{
            return $lv;
        }

    }
 




}