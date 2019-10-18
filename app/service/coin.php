<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-03-01 17:18:54
 * Desc: 用户相关
 */
namespace app\service;
use app\model\coin_order as coin_order_Model;
use app\model\coin_machine as coin_machine_Model;
use app\model\user_attach as user_attach_Model;
use core\lib\redis;
use core\lib\Model;

class coin{
    public $coin_order_M;
    public function __construct()
    {
        $this->coin_order_M=new coin_order_Model();
    }

    /*买矿机*/
    public function buy($id)
    { 
       $user = $GLOBALS['user'];
       $coin_machine_M=new coin_machine_Model();
	   $machine=$coin_machine_M->find($id);
       empty($machine) && error('矿机已下架',404); 
       //判断金额
       $user_M = new \app\model\user();
       $ar = $user_M->find($user['uid']);
   
       if($machine['m_money']-$ar['coin']-$ar['coin_storage']>0){
           error('金额不足',10003);
       }
       
        $where['uid']=$user['id'];
        $where['status']=1;
        $where['cid']=$id;
        $number=$this->coin_order_M->new_count($where);
        if($number>=$machine['purchase_limit']){
            error('达到限购条件',404);
        }

       flash_god($user['id']);
       //开始
       $redis = new redis();
       $Model = new Model();
       $Model->action();
       $redis->multi();
       //添加订单
       $data['uid']=$user['uid'];
       $data['cid']=$machine['id'];
       $data['m_pic']=$machine['m_pic'];
       $data['m_money']=$machine['m_money'];
       $data['m_day_production']=$machine['m_day_production'];
       $data['m_life']=$machine['m_life'];
       $data['m_title']=$machine['m_title'];
       $data['z_money']=$machine['z_money'];
       $data['y_time'] = time();
       $res=$this->coin_order_M->save_by_oid($data);
       empty($res) && error('添加失败',10006);	
       //扣金额        
       $money_S = new \app\service\money();
       if($ar['coin_storage']-$machine['m_money']>0){
           $money1=$machine['m_money'];
           $money2=0;
       }else{
           $money1=$ar['coin_storage'];
           $money2=$machine['m_money']-$ar['coin_storage'];
       }
       $oid = $res['oid'];
       $remark = "购买矿机";
       if($money1>0){
       $money_res = $money_S->minus($user['uid'],$money1,'coin_storage','coin_buy',$oid,$user['uid'],$remark); //记录资金流水
       empty($money_res) && error('添加失败1',10005);  
       }
       if($money2>0){
       $money_res = $money_S->minus($user['uid'],$money2,'coin','coin_buy',$oid,$user['uid'],$remark); //记录资金流水
       empty($money_res) && error('添加失败2',10005);  
       }
       //加业绩
       $yj['coin_buy[+]']=$machine['m_money'];
       $user_attach_M=new user_attach_Model();
       $user_attach_M->up($user['uid'],$yj);
      
       //加团队业绩
       $user_s = new \app\service\user();
       $user_s -> coin_sales($user['uid'],$machine['m_money']);
       //判断等级
       $rating = new \app\service\rating();
       $rating -> coin($user['uid']);
       //直推奖
       $this->direct_award($res);
       //战队奖励
       $this->team_im($res);
       //结束
       $Model->run();
       $redis->exec();
    }

    
    /*战队奖励 */
    public function team_im($order_ar)
    {
        $user_M = new \app\model\user();
        $tid=$user_M->find($order_ar['uid'],'im_team');
        if(empty($tid)){
            $user_gx_M = new \app\model\user_gx();
            $where['uid']=$order_ar['uid'];
            $where['level[<=]']=3;
            $where['ORDER']=['level'=>'ASC'];
            $gx_ar=$user_gx_M->lists_tid($where);
            if(!empty($gx_ar)){
                foreach($gx_ar as $vo){
                    $tid=$user_M->find($vo,'im_team');
                    if(!empty($tid)){
                        break;
                    }
                }
            }
        }
        $bid=(new \app\model\im_team())->find($tid,['boss_uid','cate']);
        if(empty($bid)){
            return;
        }
        if($order_ar['uid']==$bid['boss_uid']){
            return;
        }
        $coin_rating=$user_M->find($bid['boss_uid'],'coin_rating');
        if($coin_rating>1){
            $where=array();
            $where['uid']=$bid['boss_uid'];
            $where['status']=1;
            $where['remark[!]']='升级赠送';
            $where['ORDER']=['m_money'=>'DESC'];
            $money=$this->coin_order_M->have($where);
            if(empty($money)){
                return;
            }

            if($bid['cate']==0){
                $charge=(new \app\model\config())->find('bteam_charge','value');
            }else{
                $charge=(new \app\model\config())->find('hteam_charge','value');
            }
            $money=$order_ar['m_money']*$charge/1000;
            if($money>0){
            $money_S = new \app\service\money();
            $money_S->plus($bid['boss_uid'],$money,'coin','team_im',$order_ar['oid'],$order_ar['uid'],'战队奖励'); //记录资金流水
            }
        }
       
    }

    public function direct_award($order_ar)
    {
        //直推人数
        $user = (new \app\model\user())->find($order_ar['uid'],['tid','coin_rating']);
        if(empty($user['tid'])){
            return;
        }
        $where['uid']=$user['tid'];
        $where['status']=1;
        $where['remark[!]']='升级赠送';
        $where['ORDER']=['m_money'=>'DESC'];
        $money=$this->coin_order_M->have($where);
        if(empty($money)){
            return;
        }
        if($order_ar['m_money']-$money['m_money']>0){
            $order_ar['m_money']=$money['m_money'];
        }
        if($order_ar['m_money']<=0){
            return;
        }
        $zt=(new \app\model\coin_rating())->find($user['coin_rating'],'direct_award');
        $zt_money=$order_ar['m_money']*$zt/1000;
        if($zt_money<=0){
            return;
        }
        $money_S = new \app\service\money();
        $money_S->plus($user['tid'],$zt_money,'coin','coin_direct',$order_ar['oid'],$order_ar['uid'],'矿机直推奖','sum_coin'); //记录资金流水
    }

    /*升级赠送*/
    public function gift($id,$uid)
    {
       //判断订单
       $where['remark']="升级赠送";
       $where['uid']=$uid;
       $where['cid']=$id;
       $res=$this->coin_order_M->is_have($where);
       if($res){
           return;
       }
       //判断矿机
       $coin_machine_M=new coin_machine_Model();
	   $machine=$coin_machine_M->find($id);
       if(empty($machine)){
            return;
       } 
       //添加订单
       $data['uid']=$uid;
       $data['cid']=$machine['id'];
       $data['m_pic']=$machine['m_pic'];
       $data['m_money']=$machine['m_money'];
       $data['m_day_production']=$machine['m_day_production'];
       $data['m_life']=$machine['m_life'];
       $data['m_title']=$machine['m_title'];
       $data['z_money']=$machine['z_money'];
       $data['y_time'] = time();
       $data['remark']="升级赠送";
       $res=$this->coin_order_M->save_by_oid($data);
       empty($res) && error('添加失败',10006);	
    }

    /* 发放每日奖励 */
    public function day_reward()
    {
        $user = $GLOBALS['user'];
        flash_god($user['id']);
        //开始
        $redis = new redis();
        $Model = new Model();
        $Model->action();
        $redis->multi();

        $coin_order_ar=$this->coin_order_M->reward_money($user['id']);
        $money=0;
        $money2=0;
        foreach($coin_order_ar as $vo){
            $y_time=intval((time()-$vo['y_time'])/3600);
            if($y_time>0){
                $data=[];
                $money_one=0;
                
                $z_time=intval((time()-$vo['created_time'])/3600);
                if(($z_time>$vo['m_life']) || ($vo['y_life']+$y_time>$vo['m_life'])){
                    $y_time=$vo['m_life']-$vo['y_life'];
                }
                if($vo['y_life']+$y_time>=$vo['m_life']){
                    $money_one=$vo['z_money']-$vo['y_money'];
                    if($money_one<0){
                        $money_one=0;
                    }
                    $data['status']=2;
                    $data['y_life']=$vo['m_life'];
                }else{
                    $money_one=$y_time*$vo['m_day_production'];
                    if($vo['y_money']+$money_one>=$vo['z_money']){
                        $money_one=$vo['z_money']-$vo['y_money'];
                        $data['status']=2;
                        $data['y_life']=$vo['m_life'];
                    }else{
                        $data['y_life[+]']=$y_time;
                    }
                }
                
                $money=$money+$money_one;
                if($vo['remark']!="升级赠送"){
                $money2=$money2+$money_one;
                }
                $data['y_time']=strtotime('+'.$y_time.'hour',$vo['y_time']);
                $data['y_money[+]']=$money_one;
                $rs=$this->coin_order_M->up($vo['id'],$data);
                if(empty($rs)){
                    return;
                }
                if($money_one>0){
                    $money_S = new \app\service\money();
                    $money_S->plus($user['uid'],$money_one,'coin','coin_kjjl',$vo['oid'],$user['uid'],'矿机收益','sum_coin'); //记录资金流水
                }
            }
        }
        //发放团队奖
        $this->team_award($user['id'],$money2);
        
        //结束
        $Model->run();
        $redis->exec();
        return $money;
    }


    /*团队奖 */
    public function team_award($uid,$money2)
    {
        $coin_team_M=new \app\model\coin_team();
        $user_M = new \app\model\user();
        $user_attach_M = new \app\model\user_attach();
        $money_S = new \app\service\money();
        $coin_team_ar=$coin_team_M->lists_all();
        $tid=$uid;
        foreach($coin_team_ar as $vo){
            $tid=$user_M->find($tid,'tid');
            if(!$tid){
                return;
            }
            $coin_rating=$user_M->find($tid,'coin_rating');
            if($coin_rating>1){
              $data=$user_attach_M->find($tid,'coin_yvip');
              if($data && $data>=$vo['zt_num']){
                  $money=$money2*$vo['team_award']/1000;
                  if($money>0){
                  $money_S->plus($tid,$money,'coin','coin_team','无',$uid,'矿机收益团队奖'); //记录资金流水
                  }
              }
            }
        }
    }
}