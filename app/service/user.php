<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-03-01 17:18:54
 * Desc: 用户相关
 */
namespace app\service;
use core\lib\redis;

class user{
    public $user_M;
    public $redis;
    public $user_gx_M;
    public $user_attach_M;
    public function __construct()
    {
        $this->user_M = new \app\model\user();
        $this->redis = new redis();
        $this->user_gx_M = new \app\model\user_gx();
        $this->user_attach_M = new \app\model\user_attach();
    }

    /*注册统一执行*/
    public function reg_run($uid)
    { 
        $tid=$this->user_M->find($uid,'tid'); //推荐人ID
        $this -> recommend($uid,$tid,1);
        //新手任务- 分享一个好友
        $config_M = new \app\model\config();
        $res = $config_M->find('is_fxyghy');
        if($res==1){
            $new_duty_S = new \app\service\new_duty();
            $new_duty_S->paid_reward_noredis($tid,'fxyghy'); 
        }

        //注册送积分
        $zcsjf = $config_M->find('zcsjf');
        if($zcsjf>0){
            $money_S = new \app\service\money();
            $zcsjf = $zcsjf;
            $oid = '无';
            $remark = '注册送积分';
            $money_S->plus($uid,$zcsjf,'integral',"jfyx_zc",$oid,$uid,$remark);
        }

        //推荐送积分
        $tjsjf = $config_M->find('tjsjf');
        if($tjsjf>0 && $tid){
            $money_S = new \app\service\money();
            $tjsjf = $tjsjf;
            $oid = '无';
            $remark = '推荐送积分';
            $money_S->plus($tid,$tjsjf,'integral',"jfyx_tj",$oid,$uid,$remark);
        }

    }

    /*登录统一执行*/
    public function logins_run($uid)
    {
        //登录判断等级 
        $rating = new \app\service\rating();
        $rating -> mall($uid);
        $rating -> coin($uid);
    }


    /*升级统一执行*/
    public function rating_run($uid,$coin_rating,$rating)
    {
        $this -> coin_recommend_vip($uid);
    }
   

    /*商城升级统一执行*/
    public function mall_rating_run($uid,$coin_rating,$rating)
    {
        $this -> mall_recommend_vip($uid);
    }
   
    
    /*加注册人数*/
    public function recommend($uid,$tid,$level,$rating=1,$coin_rating=1)
    {
        if($level<=100){
            $data['uid']=$uid;
            $data['tid']=$tid;
            $data['level']=$level;
            $data['coin_rating']=$coin_rating;
            $data['rating']=$rating;
            $t_ar=$this->user_M->find($tid,['coin_rating','rating']);
            $data['t_coin_rating']=$t_ar['coin_rating'];
            $data['t_rating']=$t_ar['rating'];
            $rs=$this->user_gx_M->save($data);
            if($rs){
                if($level==1)$update['ynumber[+]']=1;
                $update['znumber[+]']=1;
                $this->user_attach_M->up($tid,$update);
            
                $tid_new=$this->user_M->find($tid,'tid');
                if($tid_new){
                    $this->recommend($uid,$tid_new,$level+1,$rating,$coin_rating);
                }
            }
        }
    }

    /* 后台修改推荐人加下级的注册人数 */
    public function subordinate($uid,$tid)
    {
        $rs=$this->user_gx_M->lists_all(['tid'=>$uid]);
        if(empty($rs)){
            return;
        }
        $t_ar=$this->user_M->find($tid,['coin_rating','rating']);
        foreach($rs as $vo){
            $vo['tid']=$tid;
            $vo['t_coin_rating']=$t_ar['coin_rating'];
            $vo['t_rating']=$t_ar['rating'];
            $vo['level']=$vo['level']+1;
            unset($vo['id']);
            $this->user_gx_M->save($vo);
        }
    }

    /*减注册人数*/
    public function recommend_remove($uid)
    {
        //前面3级
        $where['uid']=$uid;
        $where['level']=1;
        $user_gx_ar=$this->user_gx_M->lists_plus($where);
        foreach($user_gx_ar as $vo){
            if($vo['level']==1){
            $update['ynumber[-]']=1;
            $upwhere['uid']=$vo['tid'];
            $this->user_attach_M->up_all($upwhere,$update);

            $rd_name = 'user:'.$vo['tid'];
            $user_tj=$this->user_attach_M->find($vo['tid'],['ynumber']);
            $this->redis->hset($rd_name,'ynumber',$user_tj['ynumber']);
            }
        }

        //总人数
        $where_sum['uid']=$uid;
        $user_sum_gx_ar=$this->user_gx_M->lists_tid($where_sum);
        if($user_sum_gx_ar){
            $update_sum['znumber[-]']=1;
            $upwhere_sum['uid']=$user_sum_gx_ar;
            $this->user_attach_M->up_all($upwhere_sum,$update_sum);

            foreach($user_sum_gx_ar as $vo){
                $rd_name = 'user:'.$vo;
                $znumber=$this->user_attach_M->find($vo,'znumber');
                $this->redis->hset($rd_name,'znumber',$znumber);
            }
        }
        $this->user_gx_M->del_all(['uid'=>$uid]);
    }

    
    
    /*加减矿机分销人数*/
    public function coin_recommend_vip($uid,$types='+')
    {
        $where['uid']=$uid;
        $where['level']=1;
        $user_gx_ar=$this->user_gx_M->lists_plus($where);
        $rating = new \app\service\rating();
        foreach($user_gx_ar as $vo){
            if($vo['level']==1){
                $update['coin_yvip['.$types.']']=1;
                if($update){
                    $upwhere['uid']=$vo['tid'];
                    $this->user_attach_M->up_all($upwhere,$update);
                    $rd_name = 'user:'.$vo['tid'];
                    $user_tj=$this->user_attach_M->find($vo['tid'],['coin_yvip']);
                    $this->redis->hset($rd_name,'coin_yvip',$user_tj['coin_yvip']);
                }
                if($types=='+'){
                    $rating -> coin($vo['tid']);
                }
            }
        }

        //总人数
        $where_sum['uid']=$uid;
        $user_sum_gx_ar=$this->user_gx_M->lists_tid($where_sum);
        if($user_sum_gx_ar){
            $update_sum['coin_zvip['.$types.']']=1;
            $upwhere_sum['uid']=$user_sum_gx_ar;
            $this->user_attach_M->up_all($upwhere_sum,$update_sum);

            foreach($user_sum_gx_ar as $vo){
                $rd_name = 'user:'.$vo;
                $zvip=$this->user_attach_M->find($vo,'coin_zvip');
                $this->redis->hset($rd_name,'coin_zvip',$zvip);
            }
        }
    }
    
	
	/*加减矿机分销人数*/
    public function vip_recommend_vip($uid,$types='+')
    {
        $where['uid']=$uid;
        $where['level']=1;
        $user_gx_ar=$this->user_gx_M->lists_plus($where);
        $rating = new \app\service\rating();
        foreach($user_gx_ar as $vo){
            if($vo['level']==1){
                $update['vip_yvip['.$types.']']=1;
                if($update){
                    $upwhere['uid']=$vo['tid'];
                    $this->user_attach_M->up_all($upwhere,$update);
                    $rd_name = 'user:'.$vo['tid'];
                    $user_tj=$this->user_attach_M->find($vo['tid'],['vip_yvip']);
                    $this->redis->hset($rd_name,'vip_yvip',$user_tj['vip_yvip']);
                }
                if($types=='+'){
                    $rating -> coin($vo['tid']);
                }
            }
        }

        //总人数
        $where_sum['uid']=$uid;
        $user_sum_gx_ar=$this->user_gx_M->lists_tid($where_sum);
        if($user_sum_gx_ar){
            $update_sum['vip_zvip['.$types.']']=1;
            $upwhere_sum['uid']=$user_sum_gx_ar;
            $this->user_attach_M->up_all($upwhere_sum,$update_sum);

            foreach($user_sum_gx_ar as $vo){
                $rd_name = 'user:'.$vo;
                $zvip=$this->user_attach_M->find($vo,'vip_zvip');
                $this->redis->hset($rd_name,'vip_zvip',$zvip);
            }
        }
    }
    
    
    /*加减商城分销人数*/
    public function mall_recommend_vip($uid,$types='+')
    {
        $where['uid']=$uid;
        $where['level']=1;
        $user_gx_ar=$this->user_gx_M->lists_plus($where);
        $rating = new \app\service\rating();
        foreach($user_gx_ar as $vo){
            if($vo['level']==1){
                $update['yvip['.$types.']']=1;
                if($update){
                    $upwhere['uid']=$vo['tid'];
                    $this->user_attach_M->up_all($upwhere,$update);
                    $rd_name = 'user:'.$vo['tid'];
                    $user_tj=$this->user_attach_M->find($vo['tid'],['yvip']);
                    $this->redis->hset($rd_name,'yvip',$user_tj['yvip']);
                }
                if($types=='+'){
                    $rating -> mall($vo['tid']);
                }
            }
        }

        //总人数
        $where_sum['uid']=$uid;
        $user_sum_gx_ar=$this->user_gx_M->lists_tid($where_sum);
        if($user_sum_gx_ar){
            $update_sum['zvip['.$types.']']=1;
            $upwhere_sum['uid']=$user_sum_gx_ar;
            $this->user_attach_M->up_all($upwhere_sum,$update_sum);

            foreach($user_sum_gx_ar as $vo){
                $rd_name = 'user:'.$vo;
                $zvip=$this->user_attach_M->find($vo,'zvip');
                $this->redis->hset($rd_name,'zvip',$zvip);
            }
        }
    }


    /*coin加业绩*/
    public function coin_sales($uid,$money)
    {
       $y_where['uid']=$uid;
       $y_where['level']=1;
       $user_gx_M = new \app\model\user_gx();
       $y_user_gx_ar=$user_gx_M->lists_tid($y_where);

       $y_upwhere['uid']=$y_user_gx_ar;
       $y_update['coin_ysales[+]']=$money;
       $this->user_attach_M->up_all($y_upwhere,$y_update);
       foreach($y_user_gx_ar as $vo){
            $y_rd_name = 'user:'.$vo;
            $y_coin_ysales=$this->user_attach_M->find($vo,'coin_ysales');
            $this->redis->hset($y_rd_name,'coin_ysales',$y_coin_ysales);
       }
       
       $where['uid']=$uid;
       $user_gx_ar=$user_gx_M->lists_tid($where);
       $upwhere['uid']=$user_gx_ar;
       $update['coin_sales[+]']=$money;
       $this->user_attach_M->up_all($upwhere,$update);
       foreach($user_gx_ar as $vo){
            $rd_name = 'user:'.$vo;
            $coin_sales=$this->user_attach_M->find($vo,'coin_sales');
            $this->redis->hset($rd_name,'coin_sales',$coin_sales);
       }
    }
	
	 /*VIP加业绩*/
    public function vip_sales($uid,$money)
    {
       $y_where['uid']=$uid;
       $y_where['level']=1;
       $user_gx_M = new \app\model\user_gx();
       $y_user_gx_ar=$user_gx_M->lists_tid($y_where);

       $y_upwhere['uid']=$y_user_gx_ar;
       $y_update['vip_ysales[+]']=$money;
       $this->user_attach_M->up_all($y_upwhere,$y_update);
       foreach($y_user_gx_ar as $vo){
            $y_rd_name = 'user:'.$vo;
            $y_coin_ysales=$this->user_attach_M->find($vo,'vip_ysales');
            $this->redis->hset($y_rd_name,'vip_ysales',$y_coin_ysales);
       }
       
       $where['uid']=$uid;
       $user_gx_ar=$user_gx_M->lists_tid($where);
       $upwhere['uid']=$user_gx_ar;
       $update['vip_sales[+]']=$money;
       $this->user_attach_M->up_all($upwhere,$update);
       foreach($user_gx_ar as $vo){
            $rd_name = 'user:'.$vo;
            $coin_sales=$this->user_attach_M->find($vo,'vip_sales');
            $this->redis->hset($rd_name,'vip_sales',$coin_sales);
       }
    }

    /*加订单团队业绩*/
    public function sales($order_ar)
    {
       $uid=$order_ar['uid'];
       $money=$order_ar['money'];
       $y_where['uid']=$uid;
       $y_where['level']=1;
       $user_gx_M = new \app\model\user_gx();
       $y_user_gx_ar=$user_gx_M->lists_tid($y_where);

       $y_upwhere['uid']=$y_user_gx_ar;
       $y_update['ysales[+]']=$money;
       if($order_ar['types']==1){
            $y_update['ysales_vip[+]']=$money;
       }
       $this->user_attach_M->up_all($y_upwhere,$y_update);
       foreach($y_user_gx_ar as $vo){
            $y_rd_name = 'user:'.$vo;
            $y_coin_ysales=$this->user_attach_M->find($vo,'ysales');
            $this->redis->hset($y_rd_name,'ysales',$y_coin_ysales);
            if($order_ar['types']==1){
            $y_coin_ysales=$this->user_attach_M->find($vo,'ysales_vip');
            $this->redis->hset($y_rd_name,'ysales_vip',$y_coin_ysales);
            }
       }
       
       $where['uid']=$uid;
       $user_gx_ar=$user_gx_M->lists_tid($where);
       $upwhere['uid']=$user_gx_ar;
       $update['zsales[+]']=$money;
       if($order_ar['types']==1){
            $update['zsales_vip[+]']=$money;
       }
       $this->user_attach_M->up_all($upwhere,$update);
       foreach($user_gx_ar as $vo){
            $rd_name = 'user:'.$vo;
            $coin_sales=$this->user_attach_M->find($vo,'zsales');
            $this->redis->hset($rd_name,'zsales',$coin_sales);
            if($order_ar['types']==1){
                $coin_sales=$this->user_attach_M->find($vo,'zsales_vip');
                $this->redis->hset($rd_name,'zsales_vip',$coin_sales);
           }
       }
    }

    /*判断推荐人是否死循环，你的推荐人不能是你的下级会员*/
	public function judge_tid($uid,$tid){	
        $where['uid']=$tid;
        $where['tid']=$uid;	      
        $tid_one = $this->user_gx_M->is_have($where);
        return !$tid_one;
    }
    
}