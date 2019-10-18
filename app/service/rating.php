<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-03-01 17:18:54
 * Desc: 用户相关
 */
namespace app\service;
use core\lib\redis;

class rating{
    public $user_M;
    public $redis;
    public $user_attach_M;
    public function __construct()
    {
        $this->user_M = new \app\model\user();
        $this->redis = new redis();
        $this->user_attach_M = new \app\model\user_attach();
    }

    

    /*商城等级*/
    public function mall($uid)
    {
        //定制结算
        $user=$this->user_M->find_me($uid);
        $made=(new \app\model\config())->find('made');
        $is_made=1;
        if($made){
			$ctrlfile=MADE.'/'.$made.'/service/rating.php';
            if(is_file($ctrlfile)){
                $cltrlClass='\made\\'.$made.'\\service\rating';
                $made_S=new $cltrlClass();
                $coin_rating=$made_S->mall($uid);
                if(!isset($coin_rating['id'])){
                    return;
                }
                $is_made=0;
            }
        }
        if($is_made){
            $coin_rating_M = new \app\model\rating();
            $where['id[>]']=$user['rating'];
            $where['zt_num[<=]']=$user['yvip'];
            $where['td_num[<=]']=$user['zvip'];
            $where['shop_buy[<=]']=$user['buy'];
            $where['assign_buy[<=]']=$user['buy_vip'];
            $where['recharge[<=]']=$user['sum_money'];
            $where['td_shop_buy[<=]']=$user['zsales'];
            $where['td_assign_buy[<=]']=$user['zsales_vip'];
            $coin_rating=$coin_rating_M->have($where);
            if(!$coin_rating){
                return;
            }
            //直推等级人数
            if($coin_rating['direct_rating']>0 && $coin_rating['direct_rating_number']>0){
                $dirwct_where['tid']=$uid;
                $dirwct_where['level']=1;
                $dirwct_where['rating[>=]']=$coin_rating['direct_rating'];
                $direct_rating_number=(new \app\model\user_gx())->new_count($dirwct_where);
                if($coin_rating['direct_rating_number']>$direct_rating_number){
                    return;
                }
            }
        }
        //升级
        $data['rating']=$coin_rating['id'];
        $this->user_M->up($user['id'],$data);
        $data2['upgrade_time']=time();
        if($user['rating']==1){
            $data2['vip_upgrade_time']=time();
        }
        $this->user_attach_M->up($user['id'],$data2);
        
        //统计分销人数
        if($user['rating']==1){
            $user_S = new \app\service\user();
            $user_S -> mall_rating_run($user['id'],$data['rating'],$user['rating']);
        }

        $user_gx_M = new \app\model\user_gx();
        $data_rating['rating']=$data['rating'];
        $user_gx_M->up($uid,$data_rating);

        $data_t_rating['t_rating']=$data['rating'];
        $user_gx_M->up_all(['tid'=>$uid],$data_t_rating);
        //判断上级等级
        if($user['rating']>1 && $coin_rating['direct_rating']>0 && $coin_rating['direct_rating_number']>0){
            if($user['tid']){
            $this->mall($user['tid']);
            }
        }
        //重新判断
        $this->mall($uid);
    }

    /*矿机等级*/
    public function coin($uid)
    {
        $user=$this->user_M->find_me($uid);
        $coin_rating_M = new \app\model\coin_rating();
        $where['id[>]']=$user['coin_rating'];
        $where['coin_buy[<=]']=$user['coin_buy'];
        $where['zt_num[<=]']=$user['coin_yvip'];
        $where['td_num[<=]']=$user['coin_zvip'];
        $coin_rating=$coin_rating_M->judge($where);
        if(!$coin_rating){
            return;
        }
        //直推等级人数
        if($coin_rating['direct_rating']>0 && $coin_rating['direct_rating_number']>0){
            $dirwct_where['tid']=$uid;
            $dirwct_where['level']=1;
            $dirwct_where['coin_rating[>=]']=$coin_rating['direct_rating'];
            $direct_rating_number=(new \app\model\user_gx())->new_count($dirwct_where);
            if($coin_rating['direct_rating_number']>$direct_rating_number){
                return;
            }
        }
        
        //升级
        $data['coin_rating']=$coin_rating['id'];
        $this->user_M->up($user['id'],$data);
        $data2['coin_upgrade_time']=time();
        $this->user_attach_M->up($user['id'],$data2);

        
        //升级奖励
        if($coin_rating['send_lv']>0){
            $coin_S = new \app\service\coin();
            $coin_S->gift($coin_rating['send_lv'],$user['id']);
        }

        //统计分销人数
        if($user['coin_rating']==1){
            $user_S = new \app\service\user();
            $user_S -> rating_run($user['id'],$data['coin_rating'],$user['rating']);
        }
        $user_gx_M = new \app\model\user_gx();
        $data_rating['coin_rating']=$data['coin_rating'];
        $user_gx_M->up($uid,$data_rating);

        $data_t_rating['t_coin_rating']=$data['coin_rating'];
        $user_gx_M->up_all(['tid'=>$uid],$data_t_rating);
        //判断上级等级
        if($user['coin_rating']>1 && $coin_rating['direct_rating']>0 && $coin_rating['direct_rating_number']>0){
            if($user['tid']){
            $this->coin($user['tid']);
            }
        }
        //重新判断
        $this->coin($uid);
     
    }
    
}