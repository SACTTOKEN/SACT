<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-03-01 17:18:54
 * Desc: 用户相关
 */
namespace app\service;

class guess{
   public $guess_lord_M;
    public $guess_slave_M;
    public function __construct()
    {
        $this->guess_lord_M = new \app\model\plugin_guess_lord();
        $this->guess_slave_M = new \app\model\plugin_guess_slave();
    }

    
    /*猜猜乐奖励发放*/
    public function guess_reward_up($stage,$avg,$balance_type){
            $money_S = new \app\service\money();
            $ar = $this->guess_slave_M->lists_all($stage);
            if(!empty($ar)){
                foreach($ar as $one){
                    if($one['buy_up']>0){
                    $uid = $one['uid'];
                    $reward = $one['buy_up'] * $avg; //奖励
                    
                    $slave_data['earn'] = $reward;
                    $slave_data['is_end'] = 1;
                    $slave_data['update_time'] = time();
                    $this->guess_slave_M->up($one['id'],$slave_data); //改变结算状态

                    //$balance_type = C('guess_balance_type'); //猜猜乐结算类型

                    $money = $reward+ $one['buy_up'];   
                    $remark  = '期号：'.$stage;        
                    $money_S->plus($uid,$money,$balance_type,'ccl',$stage,$uid,$remark); //记录资金流水
                    }else{

                    $slave_lose['is_end'] = 1;
                    $slave_lose['update_time'] = time();
                    $this->guess_slave_M->up($one['id'],$slave_lose); //改变结算状态

                    }
                }
            }
    }


    public function guess_reward_down($stage,$avg,$balance_type){
            $money_S = new \app\service\money();
            $ar = $this->guess_slave_M->lists_all($stage);
            if(!empty($ar)){
                foreach($ar as $one){
                    if($one['buy_down']>0){
                    $uid = $one['uid'];
                    $reward = $one['buy_down'] * $avg; //奖励
                    
                    $slave_data['earn'] = $reward;
                    $slave_data['is_end'] = 1;
                    $slave_data['update_time'] = time();
                    $this->guess_slave_M->up($one['id'],$slave_data); //改变结算状态

                    //$balance_type = C('guess_balance_type'); //结算类型

                    $money = $reward+ $one['buy_down'];   
                    $remark  = '期号：'.$stage;        
                    $money_S->plus($uid,$money,$balance_type,'ccl',$stage,$uid,$remark); //记录资金流水
                    }else{

                        $slave_lose['is_end'] = 1;
                        $slave_lose['update_time'] = time();
                        $this->guess_slave_M->up($one['id'],$slave_lose); //改变结算状态
                        
                    }
                }
            }
    }

    //退本金，如只有一个人买并买中的情况，所有人买并全买中的情况，无服务费，全退
    public function guess_reward_back($stage,$balance_type){
        $money_S = new \app\service\money();
        $ar = $this->guess_slave_M->lists_all($stage);

        if(!empty($ar)){
            foreach($ar as $one){
                if($one['rf']==$one['buy_type'] && $one['buy_type']!=0)
                {
                    if($one['buy_type']==1){
                        $money = $one['buy_down'];
                    }
                    if($one['buy_type']==2){
                        $money = $one['buy_up'];
                    }
                    $remark  = '期号：'.$stage; 
                    $uid = $one['uid'];
                    $money_S->plus($uid,$money,$balance_type,'ccl',$stage,$uid,$remark); //记录资金流水
                }
            }

        }
    }

    
}