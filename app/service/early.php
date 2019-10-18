<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-04-11 13:40:31
 * Desc: 早起签到服务
 */
namespace app\service;

class early{
    public $early_lord_M;
    public $early_slave_M;
    public function __construct()
    {
        $this->early_lord_M = new \app\model\plugin_early_lord(); 
        $this->early_slave_M = new \app\model\plugin_early_slave();
    }
    
    /*早起签到奖励发放*/
    public function early_reward($stage,$avg,$balance_type){
        $money_S = new \app\service\money();
        $where['stage']=$stage;
        $ar = $this->early_slave_M->lists_all($where);
        if(!empty($ar)){
            foreach($ar as $one){
                if($one['is_end']==0 && $one['sign_ok']==1){
                    $uid = $one['uid'];
                    $reward = $one['stake'] * $avg; //奖励                       
                    $slave_data['earn'] = $reward;
                    $slave_data['is_end'] = 1;
                    $slave_data['update_time'] = time();
                    $this->early_slave_M->up($one['id'],$slave_data); //改变结算状态
                    $money = $reward + $one['stake'];   
                    $remark  = '期号：'.$stage;        
                    $money_S->plus($uid,$money,$balance_type,'zqqd',$stage,$uid,$remark); //记录资金流水
                }else{
                    $slave_data2['is_end'] = 1;
                    $slave_data2['update_time'] = time();
                    $this->early_slave_M->up($one['id'],$slave_data2); //改变结算状态
                }

            }
        }
    }
    
}