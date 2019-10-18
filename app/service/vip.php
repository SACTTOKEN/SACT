<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-03-01 17:18:54
 * Desc: 用户相关
 */
namespace app\service;
use app\model\vip_order as vip_order_Model;
use app\model\vip_rating as vip_rating_Model;
use app\model\user_attach as user_attach_Model;
use core\lib\redis;
use core\lib\Model;

class vip{
    public $vip_order_M;
    public function __construct()
    {
        $this->vip_order_M=new vip_order_Model();
    }

    /*买矿机*/
    public function buy($id,$xzlx,$price)
    { 
       $user = $GLOBALS['user'];
       $vip_rating_M=new vip_rating_Model();
	   $machine=$vip_rating_M->find($id);
       empty($machine) && error('VIP不存在已下架',404); 
       //判断金额
	   if($machine['ljrd']<=0){
		   error('VIP不存在已下架',10003);
	   }
       $user_M = new \app\model\user();
       $ar = $user_M->find($user['uid']);
	    
	   if($machine['id']<$ar['vip_rating']){
		    error('达到限购条件',404);
	   }  
	   if($ar['vip_rating']>1){
		   if($xzlx=1){
		   }
		   else
		   {
			  $xzlx=1; 
		   }
	   }
	   else{ //首次入单-只能默认
		   $xzlx=1;
	   }
	    $ljrd_usdt3=0;
	    if($xzlx==1){
		  $ljrd_ptb=$machine['ljrd']*C('dzrd_ptbqfb')/1000;
		  $ljrd_usdt=$machine['ljrd']-$ljrd_ptb;
		  if($ljrd_usdt-$ar['USDT']-$ar['viprd_usdt']>0){
			error('金额不足',10003);
		  }
		  else{
				if($ar['viprd_usdt']>0){
					if($ar['viprd_usdt']>=$ljrd_usdt){
						 $ljrd_usdt3=$ljrd_usdt;
						 $ljrd_usdt=0;
					}
					else{
						$ljrd_usdt3=$ar['viprd_usdt'];
						$ljrd_usdt=$ljrd_usdt-$ljrd_usdt3;
					}
				}
			}
		  $ljrd_ptb=$ljrd_ptb/$price;
		  if($ljrd_ptb-$ar['coin']>0){
			 error('金额不足',10003);
		  }
		  
		}
		else{
			$ljrd_ptb=0;
			$ljrd_usdt=$machine['ljrd'];
			if($ljrd_usdt-$ar['USDT']-$ar['viprd_usdt']>0){
			error('金额不足',10003);
		    }
			else{
				if($ar['viprd_usdt']>0){
					if($ar['viprd_usdt']>=$ljrd_usdt){
						 $ljrd_usdt3=$ljrd_usdt;
						 $ljrd_usdt=0;
					}
					else{
						$ljrd_usdt3=$ar['viprd_usdt'];
						$ljrd_usdt=$ljrd_usdt-$ljrd_usdt3;
					}
				}
			}
			
		}
	    
        /*
		$where['uid']=$user['id'];
        $where['status']=1;
        $where['cid']=$id;
        $number=$this->vip_order_M->new_count($where);
        if($number>=$machine['purchase_limit']){
            error('达到限购条件',404);
        }
		*/
	 
	   $cj_money=$machine['ljrd']*C('dzrd_cjqfb')/1000;
	   $rdjl_ptb=C('rdjl_ptb');
       flash_god($user['id']);
	 
       //开始
       $redis = new redis();
       $Model = new Model();
       $Model->action();
       $redis->multi();
	  
       //添加订单
       $data['uid']=$user['uid'];
       $data['vid']=$machine['id'];
       $data['v_pic']=$machine['piclink'];
	   $data['v_title']=$machine['title'];
       $data['m_money']=$machine['ljrd'];
       $data['m_money1']=$ljrd_usdt;
       $data['m_money2']=$ljrd_ptb;
	   $data['m_money3']=$ljrd_usdt3;
       $data['cj_money']=$cj_money;
       $data['rd_time']=time();
       $data['sf_time'] = time();
	   
       $res=$this->vip_order_M->save_by_oid($data);
       empty($res) && error('添加失败',10006);	
       //扣金额      
	  
       $money_S = new \app\service\money();
      
       $oid = $res['oid'];
       $remark = "购买VIP";
       if($ljrd_usdt>0){
       $money_res = $money_S->minus($user['uid'],$ljrd_usdt,'USDT','vip_buy',$oid,$user['uid'],$remark); //记录资金流水
       empty($money_res) && error('添加失败1',10005);  
       }
       if($ljrd_ptb>0){
        $money_res = $money_S->minus($user['uid'],$ljrd_ptb,'coin','vip_buy',$oid,$user['uid'],$remark); //记录资金流水
	    $money_res = $money_S->plus($user['uid'],$ljrd_ptb,'LMJJB','vip_buy',$oid,$user['uid'],$remark); //记录资金流水
       empty($money_res) && error('添加失败2',10005);  
       }
	   if($ljrd_usdt3>0){
       $money_res = $money_S->minus($user['uid'],$ljrd_usdt3,'viprd_usdt','vip_buy',$oid,$user['uid'],$remark); //记录资金流水
       empty($money_res) && error('添加失败3',10005);  
       }
	   
	   if($ar['viprd_zgje']<500&&$machine['ljrd']>=500){
		   $user_s = new \app\service\user();
		   $user_s -> vip_recommend_vip($user['uid']);
	   }
	   //
	   $yj_ar['viprd_ljje[+]']=$machine['ljrd'];
	   if($ar['viprd_zgje']<$machine['ljrd']){
	   $yj_ar['viprd_zgje']=$machine['ljrd'];
	   }
	   if($ar['vip_rating']<$machine['id']){
	   $yj_ar['vip_rating']=$machine['id'];
	   }
	   $yj_ar['viprd_ljed[+]']=$cj_money;
	   $yj_ar['viprd_wsf[+]']=$cj_money;
       $user_M=new \app\model\user();
       $user_M->up($user['uid'],$yj_ar);
	   
	   
       //加业绩
       $yj['vip_buy[+]']=$machine['ljrd'];
       $user_attach_M=new user_attach_Model();
       $user_attach_M->up($user['uid'],$yj);
      
       //加团队业绩
       $user_s = new \app\service\user();
       $user_s -> vip_sales($user['uid'],$machine['ljrd']);
	   
       //判断等级
      // $rating = new \app\service\rating();
       //$rating -> vip($user['uid']);
       //直推奖
      // $this->direct_award($res);
       //战队奖励
      // $this->team_im($res);
       //结束
	   
	   //发放团队奖
       $this->team_award($user['uid'],$machine['ljrd'],$oid,$rdjl_ptb,$price);
	   
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
            $money=$this->vip_order_M->have($where);
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
        $money=$this->vip_order_M->have($where);
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
       $res=$this->vip_order_M->is_have($where);
       if($res){
           return;
       }
       //判断矿机
       $vip_rating_M=new vip_rating_Model();
	   $machine=$vip_rating_M->find($id);
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
       $res=$this->vip_order_M->save_by_oid($data);
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

        $vip_order_ar=$this->vip_order_M->reward_money($user['id']);
        $money=0;
        $money2=0;
        foreach($vip_order_ar as $vo){
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
                $rs=$this->vip_order_M->up($vo['id'],$data);
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
      //  $this->team_award($user['id'],$money2);
        
        //结束
        $Model->run();
        $redis->exec();
        return $money;
    }


    /*团队奖 */
    public function team_award($uid,$money2,$oid,$rdjl_ptb,$price)
    {
		
		
        $user_dtjdj_M=new \app\model\user_dtjdj();
		
        $user_M = new \app\model\user();
		
        $user_attach_M = new \app\model\user_attach();
		
        $money_S = new \app\service\money();
		
        $user_dtjdj_ar=$user_dtjdj_M->lists_all();
		
        $tid=$uid;
		
	   
        foreach($user_dtjdj_ar as $vo){
            $tid=$user_M->find($tid,'tid');
            if(!$tid){
                return;
            }
			$viprd_wsf=$user_M->find($tid,'viprd_wsf');
			//echo $tid.'-'.$viprd_wsf.'<br>';
			
            if($viprd_wsf>0){
              $data=$user_attach_M->find($tid,'vip_yvip');
			  //echo $data;
              if($data && $data>=$vo['zt_num']){
                  $money=$money2*$vo['team_award']/1000;
				  if($money-$viprd_wsf>0){
					  $money=$viprd_wsf;
				  }
                  if($money>0){
					  $yj_ar['viprd_ysf[+]']=$money;
					  $yj_ar['viprd_wsf[-]']=$money;
					  $user_M->up($tid,$yj_ar);
					  //$money_ptb=$money*$rdjl_ptb/1000;
					  //$money_usdt=$money-$money_ptb;
					  $money_ptb=$money;
					  $money_ptb=$money_ptb/$price;
					  if($money_ptb>0){
                      $money_S->plus($tid,$money_ptb,'coin','vip_team',$oid,$uid,'见点奖'); //记录资金流水
					  }
					 // if($money_usdt>0){
                     // $money_S->plus($tid,$money_usdt,'viprd_usdt','vip_team',$oid,$uid,'见点奖'); //记录资金流水
					 // }
                  }
              }
            }
        }
    }
	

}