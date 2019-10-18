<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-14 11:38:07
 * Desc: 分红
 */
namespace app\ctrl\common;

use app\model\vip_order as vip_order_Model;
use app\model\mxq_order as mxq_order_Model;
use app\model\user_fxtj as user_fxtj_Model;
use app\model\config as config_Model;
use app\model\user as user_Model;
use app\model\coin_price as coin_price_Model;

class mall_ocfh
{

    public $vip_order_M;
	public $mxq_order_M;
	public $user_fxtj_M;
    public $config_M;
    public $user_M;
    public $run_oc_M;
    public $money_S;
    public $user_gx_M;
    public $rating_M;
	 public $coin_price_M;


    public function __construct()
    {
        $this->vip_order_M = new vip_order_Model();
		$this->mxq_order_M = new mxq_order_Model();
		$this->user_fxtj_M = new user_fxtj_Model();
        $this->config_M = new config_Model();
        $this->user_M = new user_Model();
        $this->run_oc_M = new \app\model\run_oc();
        $this->money_S = new \app\service\money();
        $this->user_gx_M = new \app\model\user_gx();
        $this->rating_M = new \app\model\rating();
		$this->coin_price_M = new coin_price_Model();
    }

    public function index()
    {
        $ocfh_kqcs=C('ocfh_kqcs');
		$dtsf_usdt=C('dtsf_usdt');
		$dtsf_ptb=C('dtsf_ptb');
		$dzrd_jtsf=C('dzrd_jtsf');
		$rdjl_ptb=C('rdjl_ptb');
		
		$price=$this->coin_price_M->price();
            $redis = new \core\lib\redis();
            $Model = new \core\lib\Model();
            $Model->action();
            $redis->multi();
                $times=0;
                $open_times=0;
                $where=array();
				if($ocfh_kqcs==1){
                        $times = strtotime(date("Y-m-d H:00:00"));
                        $open_times = $times - 3600;
				}
                else{
                        $times = strtotime(date("Y-m-d")) ;
                        $open_times = $times - 86400;
				}
			
                $where['times[>=]'] = $times;
                $run_oc_res = $this->run_oc_M->have($where);
                if(empty($run_oc_res)) {
                    //统计释放 奖励未释放
                    $data['times']=$times;
                    $data['status']=100;
                    $this->run_oc_M->save($data);
					$jtzj=date("w",$times);
					if($jtzj==0){
						$jtzj=7;
					}
					$where1=array();
					$where1['zt_num'] = $jtzj;
					$user_dtsf_M=new \app\model\user_dtsf();
					$machine=$user_dtsf_M->have($where1);
					if(empty($machine)) {
					}
					else
					{
						$dtsf_usdt=$machine['dtsf_usdt'];
						$dtsf_ptb=$machine['dtsf_ptb'];
					}
					//$this->unified_dtsf($dtsf_usdt,$dtsf_ptb,$price);  //判断动态释放
					$this->unified_dtsf($dtsf_usdt,$dtsf_ptb,1);  //判断动态释放
					
                }
				elseif($run_oc_res['status']==100){
					//$issue_res=$this->issue_gcsf($price);  //执行对冲释放
					$issue_res=$this->issue_gcsf(1);  //执行对冲释放
					if($issue_res==false){
                        $this->run_oc_M->up($run_oc_res['id'],['status'=>1]);
                    }
				}
				elseif($run_oc_res['status']==1){
					//echo $run_oc_res['status'];
					$jtzj=date("w",$times);
					if($jtzj==0){
						$jtzj=7;
					}
					$where1=array();
					$where1['zt_num'] = $jtzj;
					$user_dtsf_M=new \app\model\user_dtsf();
					$machine=$user_dtsf_M->have($where1);
					if(empty($machine)) {
					}
					else
					{
						$dtsf_usdt=$machine['dtsf_usdt'];
						$dtsf_ptb=$machine['dtsf_ptb'];
					}
					//$this->unified_dtsf($dtsf_usdt,$dtsf_ptb);
					$issue_res=$this->issue_dtsf($dtsf_usdt,$dtsf_ptb);  //执行动态奖励释放
					if($issue_res==false){
                        $this->run_oc_M->up($run_oc_res['id'],['status'=>2]);
                    }
				}
				elseif($run_oc_res['status']==2){ //执行静态释放
                    //有运行记录，未发放记录，每次发放100条记录
                    $issue_res=$this->issue_jtsf($times,$dzrd_jtsf,$rdjl_ptb,$price);
                    if($issue_res==false){
                        $this->run_oc_M->up($run_oc_res['id'],['status'=>3]);
                    }
                }
				elseif($run_oc_res['status']==3){ //执行星球释放飞船
                    //执行星球释放飞船
                    $issue_res=$this->issue_mxqjl($times);
                    if($issue_res==false){
                        $this->run_oc_M->up($run_oc_res['id'],['status'=>4]);
                    }
                }
				elseif($run_oc_res['status']==4){ //统计飞船团队奖，静态团队奖，节点奖励
				
                    $issue_res=$this->issue_tjfhjl($times);
					$this->run_oc_M->up($run_oc_res['id'],['status'=>5]);
                }
				elseif($run_oc_res['status']==5){ 
                    //执行星球团队奖和VIP团队奖
                    $issue_res=$this->issue_vipxqtdj($times,$rdjl_ptb,$price);
                    if($issue_res==false){
                        $this->run_oc_M->up($run_oc_res['id'],['status'=>6]);
                    }
                }
				elseif($run_oc_res['status']==6){ //统计会员的节点等级
                    $issue_res=$this->issue_gxjddj($times);
					$this->run_oc_M->up($run_oc_res['id'],['status'=>7]);
                }
				elseif($run_oc_res['status']==7){ //更新节点收益
                    $issue_res=$this->issue_gxjdsy($times);
					$this->run_oc_M->up($run_oc_res['id'],['status'=>8]);
                }
				elseif($run_oc_res['status']==8){ 
                    //执行星球团队奖和VIP团队奖
                    $issue_res=$this->issue_jdsyff($times,$rdjl_ptb,$price);
                    if($issue_res==false){
                        $this->run_oc_M->up($run_oc_res['id'],['status'=>9]);
                    }
                }
				elseif($run_oc_res['status']==1){
                   
                }
            
            $Model->run();
            $redis->exec();
      
    }

    public function unified($times,$open_times,$rating)
    {
        $this->user_M->up_all(['rating'=>$rating['id']],['is_stock'=>0,'day_dividend'=>0]);
        switch ($rating['dividend_types']) {
            case 1:
                $fields = 'money';
                break;
            case 2:
                $fields = 'money';
                $where_or['types'] = 0;
                break;
            case 3:
                $fields = 'money';
                $where_or['types'] = 1;
                break;
            case 4:
                $fields = 'reward';
                break;
            case 5:
                $fields = 'reward';
                $where_or['types'] = 0;
                break;
            case 6:
                $fields = 'reward';
                $where_or['types'] = 1;
                break;
            default:
                return true;
        }
        $where_or['is_pay']=1;
        $where_or['is_settle']=1;
        $where_ar['settle_time[>=]']=$open_times;
        $where_ar['settle_time[<]']=$times;
        $sum=$this->order_M->find_sum($fields,$where_or);
        if($sum<=0){
            return true;
        }
        $sum=$sum*$rating['dividend']/1000;
        $number=$this->user_M->new_count(['show'=>1,'rating'=>$rating['id']]);
        if($number<=0){
            return true;
        }
        $reward=sprintf("%.3f",$sum/$number);
        if($reward<=0){
            return true;
        }
        $this->user_M->up_all(['show'=>1,'rating'=>$rating['id']],['is_stock'=>1,'day_dividend'=>$reward]);
    }

    public function issue($rating)
    {
        $where['show']=1;
        $where['rating']=$rating['id'];
        $where['is_stock']=1;
        $where['day_dividend[>]']=0;
        $where['LIMIT']=[0,100];
        $user_ar=$this->user_M->lists_all($where,['id','day_dividend']);
        if(empty($user_ar)){
            return false;
        }
        if ($rating['dividend_account'] == 'integral') {
            $sum_str = 'sum_integral';
        } elseif ($rating['dividend_account'] == 'amount') {
            $sum_str = 'sum_amount';
        }
        foreach($user_ar as $vos){
            $this->money_S->plus($vos['id'],$vos['day_dividend'],$rating['dividend_account'],'dividend','无',$vos,'',$sum_str);
        }
        return true;
    }
	
	
	
	
	 public function unified_dtsf($dtsf_usdt,$dtsf_ptb,$price)
    { 
		
	    //重置所有用户不可释放，今天静态释放，今天飞船释放，
		$this->user_M->up_all($where=[],['is_dtjlsf'=>0,'viprd_jtsf'=>0,'mxq_bcjtsl'=>0,'mxq_bcjtsl'=>0,'dtsf_usdt'=>0]);
        $this->user_M->up_all(["OR" =>['viprd_usdt[>]'=>0,'viprd_ptb[>]'=>0]],['is_dtjlsf'=>1]);
		$Model = new \core\lib\Model();
		//根据会员等级
		$Model::$medoo->query("update user set dtsf_usdt=ifnull((SELECT dtsf_usdt from vip_rating where vip_rating.id=user.vip_rating limit 0,1 ),0) where is_dtjlsf=1 and vip_rating>0");
		//根据节点等级
		$Model::$medoo->query("update user set dtsf_usdt=ifnull((SELECT dtsf_usdt from jddj_rating where jddj_rating.id=user.jddj_rating limit 0,1 ),0) where is_dtjlsf=1 and jddj_rating>1");
		
		
		
		//每天释放共冲到可用USDT
		$Model::$medoo->query("update user set is_mtgcsf=0 where is_mtgcsf>0 ");
		$Model::$medoo->query("update user set jtsf_usdt_gc=USDT_storage where USDT_storage>0 and coin_storage*".$price."-jtsf_coin_gc>=USDT_storage "); 
		//可以全部释放
		$Model::$medoo->query("update user set jtsf_usdt_gc=coin_storage*".$price."-jtsf_coin_gc where USDT_storage>0 and coin_storage*".$price."-jtsf_coin_gc<USDT_storage ");
		//只能部分释放
		$Model::$medoo->query("update user set is_mtgcsf=1 where jtsf_coin_gc>0 or jtsf_usdt_gc>0 ");
		//设定可以释放的会员
		
		
		
		
    }
	
	 public function issue_gcsf($price)
    {
		$where=array();
        $where['is_mtgcsf']=1;
        $where['LIMIT']=[0,100];
        $user_ar=$this->user_M->lists_all($where,['id','coin_storage','USDT_storage','jtsf_coin_gc','jtsf_usdt_gc','is_mtgcsf']);
		if(empty($user_ar)){
            return false;
        }
		$user_M=new \app\model\user();
        foreach($user_ar as $vos){
			if($vos['jtsf_coin_gc']>0&&$vos['coin_storage']>0){
			  $viprd_usdt=$vos['coin_storage']*$price;
			  $viprd_usdt1=$vos['jtsf_coin_gc'];
			  if($viprd_usdt1-$viprd_usdt<0){
				  //$viprd_usdt1=$viprd_usdt;
				  $viprd_usdt=$viprd_usdt1/$price;
			  }else{
				  $viprd_usdt1=$viprd_usdt;
				  $viprd_usdt=$viprd_usdt1/$price;
			  }
			  $viprd_usdt1=sprintf("%.4f",$viprd_usdt1);
			  $viprd_usdt=sprintf("%.4f",$viprd_usdt);
			  if($viprd_usdt1>0&&$viprd_usdt>0){
				  //echo $viprd_usdt;
				 // echo "<br>";
				 // echo $vos['coin_storage'];
                 $this->money_S->minus($vos['id'],$viprd_usdt,'coin_storage','mtgc_sf','无',$vos['id'],'共冲释放');
				 $this->money_S->plus($vos['id'],$viprd_usdt1,'USDT_KY','mtgc_sf','无',$vos['id'],'共冲释放');
			  }
			}
			$jtsf_usdt_gc=0;
			if($vos['jtsf_usdt_gc']>0&&$vos['USDT_storage']>0){
			  $viprd_ptb=$vos['jtsf_usdt_gc'];
			  if($viprd_ptb-$vos['USDT_storage']>0){
				  $viprd_ptb=$vos['USDT_storage'];
			  }
			  $viprd_ptb=sprintf("%.4f",$viprd_ptb);
			  if($viprd_ptb>0){
                 $this->money_S->minus($vos['id'],$viprd_ptb,'USDT_storage','mtgc_sf','无',$vos['id'],'共冲释放');
				 $this->money_S->plus($vos['id'],$viprd_ptb,'USDT_KY','mtgc_sf','无',$vos['id'],'共冲释放');
			  }
			  $jtsf_usdt_gc=$viprd_ptb;
			}
			$where2['is_mtgcsf']=2;
			$where2['jtsf_coin_gc']=$jtsf_usdt_gc; //第三天释放
			$where2['jtsf_usdt_gc']=0; //第三天释放
			$user_M->up($vos['id'],$where2);
        }
        return true;
    }
	
	 public function issue_dtsf($dtsf_usdt,$dtsf_ptb)
    {
		$where=array();
        $where['is_dtjlsf']=1;
        $where['viprd_usdt[>]']=0;
		$where['viprd_ptb[>]']=0;
        $where['LIMIT']=[0,100];
        $user_ar=$this->user_M->lists_all($where,['id','viprd_usdt','viprd_ptb','dtsf_usdt']);
		if(empty($user_ar)){
            return false;
        }
		$user_M=new \app\model\user();
        foreach($user_ar as $vos){
			if($vos['viprd_usdt']>0){
			  $viprd_usdt=$vos['viprd_usdt']*$vos['dtsf_usdt']/1000;
			  if($viprd_usdt-$vos['viprd_usdt']>0){
				  $viprd_usdt=$vos['viprd_usdt'];
			  }
			  $viprd_usdt=sprintf("%.3f",$viprd_usdt);
			  if($viprd_usdt>0){
                 $this->money_S->minus($vos['id'],$viprd_usdt,'viprd_usdt','dtsc_sf','无',$vos['id'],'锁仓释放');
				 $this->money_S->plus($vos['id'],$viprd_usdt,'USDT_storage','dtsc_sf','无',$vos['id'],'锁仓释放');
			  }
			}
			if($vos['viprd_ptb']>0){
			  $viprd_ptb=$vos['viprd_ptb']*$dtsf_ptb/1000;
			  if($viprd_ptb-$vos['viprd_ptb']>0){
				  $viprd_ptb=$vos['viprd_ptb'];
			  }
			  $viprd_ptb=sprintf("%.3f",$viprd_ptb);
			  if($viprd_ptb>0){
                 $this->money_S->minus($vos['id'],$viprd_ptb,'viprd_ptb','dtsc_sf','无',$vos['id'],'锁仓释放');
				 $this->money_S->plus($vos['id'],$viprd_ptb,'coin','dtsc_sf','无',$vos['id'],'锁仓释放');
			  }
			}
			$where2['is_dtjlsf']=2;
			$user_M->up($vos['id'],$where2);
        }
        return true;
    }


     public function issue_jtsf($times,$dzrd_jtsf,$rdjl_ptb,$price)
    {
		$where=array();
        $where['status']=0;
		$where['sf_time[<]']=$times;
        $where['LIMIT']=[0,100];
		$vip_order_M=new \app\model\vip_order();
        $user_ar=$this->vip_order_M->lists_all($where,['id','oid','uid','m_money','cj_money','sc_jtsf','m_money2']);
		if(empty($user_ar)){
            return false;
        }
		$user_M=new \app\model\user();
		$vip_order_M=new \app\model\vip_order();
		$money_S = new \app\service\money();
		$yj_ar4['sf_time']=time();
        foreach($user_ar as $vos){
			$viprd_wsf=$user_M->find($vos['uid'],'viprd_wsf');
			$bc_money=$vos['m_money2']*$price*$dzrd_jtsf/1000;
			$bd_sfcj=0;
			$bhy_sfcj=0;
			if($bc_money+$vos['sc_jtsf']-$vos['cj_money']>=0){
				//本单出局
				$bd_sfcj=1;
				$bc_money=$vos['cj_money']-$vos['sc_jtsf'];
			}
			if($bc_money-$viprd_wsf>=0){
				//本会员出局
				$bhy_sfcj=1;
				$bc_money=$viprd_wsf;
			}
			if($bc_money>0){
				$yj_ar['viprd_ysf[+]']=$bc_money;
				$yj_ar['viprd_wsf[-]']=$bc_money;
				$yj_ar['viprd_jtsf[+]']=$bc_money;
				$user_M->up($vos['uid'],$yj_ar);
				//exit;
				//$money_ptb=$bc_money*$rdjl_ptb/1000;
				//$money_usdt=$bc_money-$money_ptb;
				$money_usdt=$bc_money;
					 // if($money_ptb>0){
                     // $money_S->plus($vos['uid'],$money_ptb,'coin','vip_jtsf',$vos['oid'],$vos['uid'],'静态释放'); //记录资金流水
					//  }
					  if($money_usdt>0){
                      $money_S->plus($vos['uid'],$money_usdt,'USDT_storage','vip_jtsf',$vos['oid'],$vos['uid'],'静态释放'); //记录资金流水
					  }
			  $yj_ar4['sc_jtsf[+]']=$bc_money;
			  $vip_order_M->up($vos['id'],$yj_ar4);
			}
			
			if($bd_sfcj==1){
				$yj_ar1['status']=1;
				$yj_ar1['cd_time']=time();
				$vip_order_M->up($vos['id'],$yj_ar1);
			}
			if($bhy_sfcj==1){
				$yj_ar2['status']=1;
				$yj_ar2['cd_time']=time();
				$yj_tj['status']=0;
				$yj_tj['uid']=$vos['uid'];
				$this->vip_order_M->up_all($yj_tj,$yj_ar2);
			}
        }
        return true;
    }
	
	
	 public function issue_mxqjl($times)
    {
		$where=array();
        $where['status']=0;
		$where['sf_time[<]']=$times;
        $where['LIMIT']=[0,100];
		$mxq_order_M=new \app\model\mxq_order();
        $user_ar=$this->mxq_order_M->lists_all($where,['id','oid','uid','mid']);
		if(empty($user_ar)){
            return false;
        }
		$user_M=new \app\model\user();
		$mxqsz_M=new \app\model\mxqsz();
		$mxq_order_M=new \app\model\mxq_order();
		$money_S = new \app\service\money();
		$yj_ar4['sf_time']=time();
        foreach($user_ar as $vos){
			$bc_money=$mxqsz_M->find($vos['mid'],'mtsc');
			if($bc_money>0){
				$yj_ar['mxq_jlsl[+]']=$bc_money;
				$yj_ar['mxq_bcjtsl[+]']=$bc_money;
				$user_M->up($vos['uid'],$yj_ar);
				$money_S->plus($vos['uid'],$bc_money,'mxq_fcsl','mxq_scfc',$vos['oid'],$vos['uid'],'M星球生产飞船'); //记录资金流水
				$yj_ar4['sc_jtsf[+]']=$bc_money;
				$mxq_order_M->up($vos['id'],$yj_ar4);
			}
        }
        return true;
    }
	
	   public function issue_tjfhjl($times)
    {
		$Model = new \core\lib\Model();
		$Model::$medoo->query("truncate table user_fxtj");
		//查询结果插入表
		$Model::$medoo->query("insert into user_fxtj(uid,tid,viprd_jtsf,viprd_wsf,mxq_bcjtsl,mxq_cysl) select id,tid,viprd_jtsf,viprd_wsf,mxq_bcjtsl,mxq_cysl from user where 1=1 order by id asc");
		//查询VIP直推人数
		$Model::$medoo->query("update user_fxtj set vip_yvip=ifnull((SELECT vip_yvip from user_attach where user_attach.uid=user_fxtj.uid limit 0,1 ),0) ");
		//查询M星球直推人数
		$Model::$medoo->query("update user_fxtj set mxq_yvip=(SELECT count(id) from user where user.tid=user_fxtj.uid and user.mxq_cysl>0 ) ");
		//统计VIP 1代到20代分别奖励金额
		$Model::$medoo->query("update user_fxtj set vip_money1=(SELECT ifnull(sum(viprd_jtsf),0) from (select uid,tid,viprd_jtsf from user_fxtj) temp where temp.tid=user_fxtj.uid and temp.viprd_jtsf>0 ) ");
		for ($x=2; $x<=20; $x++) {
			$x1=$x-1;
			$Model::$medoo->query("update user_fxtj set vip_money".$x."=(SELECT ifnull(sum(vip_money".$x1."),0) from (select uid,tid,vip_money".$x1." from user_fxtj) temp where temp.tid=user_fxtj.uid and temp.vip_money".$x1.">0 ) ");
		} 
		
		//统计M星球 1代到10代分别奖励金额
		
		$Model::$medoo->query("update user_fxtj set mxq_money1=(SELECT ifnull(sum(mxq_bcjtsl),0) from (select uid,tid,mxq_bcjtsl from user_fxtj) temp where temp.tid=user_fxtj.uid and temp.mxq_bcjtsl>0 ) ");
		for ($x=2; $x<=10; $x++) {
			$x1=$x-1;
			$Model::$medoo->query("update user_fxtj set mxq_money".$x."=(SELECT ifnull(sum(mxq_money".$x1."),0) from (select uid,tid,mxq_money".$x1." from user_fxtj) temp where temp.tid=user_fxtj.uid and temp.mxq_money".$x1.">0 ) ");
		}
		$user_jttdj_ar=$Model::$medoo->query("SELECT * from user_jttdj order by id asc limit 0,20 "); 
		if(empty($user_jttdj_ar)){
			//未设置
        }
		else{
		$zx_vip_yj="update user_fxtj set vip_money=";
		$x=0;
        foreach($user_jttdj_ar as $vos){
			$x+=1;
			if($x==1){
				$zx_vip_yj=$zx_vip_yj."vip_money".$x."*".$vos['team_award']."/1000";
			}
			else{
				$zx_vip_yj=$zx_vip_yj."+vip_money".$x."*".$vos['team_award']."/1000";
		    }
			$zx_vip_yj1=$zx_vip_yj." where vip_yvip>=".$vos['zt_num']." ";
			$Model::$medoo->query($zx_vip_yj1);
			
        }
		 //超出剩余的额度。最高只发放剩余额度
		 $Model::$medoo->query("update user_fxtj set vip_money=viprd_wsf where vip_money>viprd_wsf ");
		}
		
		
		//星球团队奖开始
		$user_xqtdj_ar=$Model::$medoo->query("SELECT * from user_xqtdj order by id asc limit 0,10 "); 
		if(empty($user_xqtdj_ar)){
           //未设置星球团队奖
        }
		else{
		$zx_mxq_yj="update user_fxtj set mxq_money=";
		$x=0;
        foreach($user_xqtdj_ar as $vos){
			$x+=1;
			if($x==1){
				$zx_mxq_yj=$zx_mxq_yj."mxq_money".$x."*".$vos['team_award']."/1000";
			}
			else{
			$zx_mxq_yj=$zx_mxq_yj."+mxq_money".$x."*".$vos['team_award']."/1000";
		    }
			$zx_mxq_yj1=$zx_mxq_yj." where mxq_cysl>0 and mxq_yvip>=".$vos['zt_num']." ";
			$Model::$medoo->query($zx_mxq_yj1);
        }
		
		}
		//团队奖统计有大于0的。执行可以发放奖励
		$Model::$medoo->query("update user_fxtj set is_dtjlsf=1 where vip_money>0 or mxq_money>0 ");
		//星球团队奖结束
		 return true;
		
	}

     public function issue_vipxqtdj($times,$rdjl_ptb,$price)
    {
		$where=array();
        $where['is_dtjlsf']=1;
        $where['LIMIT']=[0,100];
		$user_fxtj_M=new \app\model\user_fxtj();
        $user_ar=$this->user_fxtj_M->lists_all($where,['id','uid','vip_money','mxq_money']);
		if(empty($user_ar)){
            return false;
        }
		$user_M=new \app\model\user();
		$user_fxtj_M=new \app\model\user_fxtj();
		$money_S = new \app\service\money();
		$vip_order_M=new \app\model\vip_order();
		$yj_ar4['is_dtjlsf']=2;
        foreach($user_ar as $vos){
			$bc_money=$vos['mxq_money'];
			if($bc_money>0){
				$yj_ar10['mxq_jlsl[+]']=$bc_money;
				$yj_ar10['mxq_bctdsl']=$bc_money;
				$user_M->up($vos['uid'],$yj_ar10);
				$money_S->plus($vos['uid'],$bc_money,'mxq_fcsl','mxq_tdj','无',$vos['uid'],'M星球团队奖'); //记录资金流水
			
			}
			$bc_money=$vos['vip_money'];
			if($bc_money>0){
				$viprd_wsf=$user_M->find($vos['uid'],'viprd_wsf');
				$bhy_sfcj=0;
				if($bc_money-$viprd_wsf>0){
					  $bc_money=$viprd_wsf;
					  $bhy_sfcj=1;
				}
				if($bc_money>0){
				$yj_ar['viprd_ysf[+]']=$bc_money;
				$yj_ar['viprd_wsf[-]']=$bc_money;
				$user_M->up($vos['uid'],$yj_ar);
				$money_ptb=$bc_money/$price;  //奖励到币的-需要除于价格
				//$money_usdt=$bc_money-$money_ptb;
				if($money_ptb>0){
					$money_S->plus($vos['uid'],$money_ptb,'coin','vip_tdj','无',$vos['uid'],'VIP团队奖'); //记录资金流水
				 }
				// if($money_usdt>0){
				//	 $money_S->plus($vos['uid'],$money_usdt,'viprd_usdt','vip_tdj','无',$vos['uid'],'VIP团队奖'); //记录资金流水
				// }
				 }
				 if($bhy_sfcj==1){
				$yj_ar2['status']=1;
				$yj_ar2['cd_time']=time();
				$yj_tj['status']=0;
				$yj_tj['uid']=$vos['uid'];
				$this->vip_order_M->up_all($yj_tj,$yj_ar2);
				}
			}
			
		   $user_fxtj_M->up($vos['id'],$yj_ar4);
        }
        return true;
    }


    public function issue_gxjddj($times)
    {
		$Model = new \core\lib\Model();
		//查询伞下总业绩
		$Model::$medoo->query("update user_fxtj set vip_sales=ifnull((SELECT vip_sales from user_attach where user_attach.uid=user_fxtj.uid limit 0,1 ),0) ");
		//获取会员节点等级
		$Model::$medoo->query("update user_fxtj set jddj_rating=ifnull((SELECT jddj_rating from user where user.id=user_fxtj.uid limit 0,1 ),1) ");
		$Model::$medoo->query("update user_gx set jddj_rating=ifnull((SELECT jddj_rating from user where user.id=user_gx.uid limit 0,1 ),1) ");
		//获取伞下最高节点等级
		$Model::$medoo->query("update user_fxtj set jddj_sxzgid=ifnull((SELECT jddj_rating from user_gx where user_gx.tid=user_fxtj.uid order by jddj_rating desc limit 0,1 ),1) ");
		$Model::$medoo->query("update user_fxtj set jddj_sxzgid=jddj_rating where jddj_sxzgid<jddj_rating ");
		$jddj_rating_ar=$Model::$medoo->query("SELECT * from jddj_rating where id>1 order by id asc"); 
		foreach($jddj_rating_ar as $vos){  //
			$sqtj="update user_fxtj set jddj_rating=".$vos['id']." where jddj_rating<".$vos['id']." "; //比较低等级的执行升级，比较高的无视
			if($vos['zt_num']>0){
				$sqtj=$sqtj." and vip_yvip>=".$vos['zt_num']." ";
			}
			if($vos['sxyj']>0){
				$sqtj=$sqtj." and vip_sales>=".$vos['sxyj']." ";
			}
			if($vos['ztjd_num']>0&&$vos['ztjd_id']>0){
				$sqtj=$sqtj." and (SELECT count(id) from (select uid,tid,jddj_sxzgid from user_fxtj) temp where temp.tid=user_fxtj.uid and jddj_sxzgid>=".$vos['ztjd_id']." )>=".$vos['ztjd_num']." ";
			}
			
			$Model::$medoo->query($sqtj);  //更新会员节点等级
			$Model::$medoo->query("update user_fxtj set jlqfb=".$vos['jlqfb']." where jddj_rating=".$vos['id']." ");
			//更新会员节点等级
			$Model::$medoo->query("update user set jddj_rating=ifnull((SELECT jddj_rating from user_fxtj where user.id=user_fxtj.uid limit 0,1 ),1) ");
			$Model::$medoo->query("update user_gx set jddj_rating=ifnull((SELECT jddj_rating from user where user.id=user_gx.uid limit 0,1 ),1) ");
			//重新获取伞下最高节点等级
			$Model::$medoo->query("update user_fxtj set jddj_sxzgid=ifnull((SELECT jddj_rating from user_gx where user_gx.tid=user_fxtj.uid order by jddj_rating desc limit 0,1 ),1) ");
			$Model::$medoo->query("update user_fxtj set jddj_sxzgid=jddj_rating where jddj_sxzgid<jddj_rating ");
			
        }
		return true;
		
	}
	 public function issue_gxjdsy($times)
    {
		$Model = new \core\lib\Model();
		$jddj_rating_ar=$Model::$medoo->query("SELECT * from jddj_rating where id>1 order by id asc"); 
		$ylzxcs=0;
		$Model::$medoo->query("update user_fxtj set ljjdsy=0,sfypp_tdj=0,pid=0,is_jdsysf=0 ");
		$user_fxtj_M=new \app\model\user_fxtj();
		foreach($jddj_rating_ar as $vos){  //
		    $ylzxcs=$ylzxcs+1;
		    if($ylzxcs==1){
				$Model::$medoo->query("update user_fxtj set pid=tid,tdj_yfbl=0,sfypp_tdj=0,jl_sxxzcb_qfb1=0,ljjdsy=0 where tid>0");
			}
			else
			{
				$Model::$medoo->query("update user_fxtj set sfypp_tdj=0,pid=ifnull((select pid from (select uid,pid from user_fxtj ) temp where temp.uid=user_fxtj.pid limit 0,1),0) where pid>0 and tdj_yfbl<".$vos["jlqfb"]."");
			}
			$Model::$medoo->query("update user_fxtj set sfypp_tdj=1 where pid>0 and (select count(id) from (select uid,jlqfb from user_fxtj ) temp where temp.uid=user_fxtj.pid and temp.jlqfb>user_fxtj.tdj_yfbl )>0  ");
			$x=0;
			$where=array();
			$where['sfypp_tdj']=0;
			$where['pid[>]']=0;
			$where['tdj_yfbl[<]']=$vos["jlqfb"];
			for ($x=1; $x<=20; $x++){
				//$bcxh_sfjs=$Model::$medoo->query("select id from user_fxtj where sfypp_tdj=0 and pid>0 and tdj_yfbl<".$vos["jlqfb"]." limit 0,1");
				$is_have = $user_fxtj_M->is_have($where);
				if($is_have){
					$Model::$medoo->query("update user_fxtj set sfypp_tdj=1 where sfypp_tdj=0 and pid>0 and tdj_yfbl<".$vos["jlqfb"]." and (select count(id) from (select uid,jlqfb from user_fxtj ) temp where temp.uid=user_fxtj.pid and temp.jlqfb>user_fxtj.tdj_yfbl )>0  ");
					//如果上一级匹配到的已经是更高比例。那就是已匹配
					$Model::$medoo->query("update user_fxtj set sfypp_tdj=1,pid=ifnull((select pid from (select uid,pid from user_fxtj ) temp where temp.uid=user_fxtj.pid limit 0,1),0) where sfypp_tdj=0 and pid>0 and tdj_yfbl<".$vos["jlqfb"]." and (select count(id) from (select uid,jlqfb from user_fxtj ) temp1 where temp1.uid=user_fxtj.pid and temp1.jlqfb>user_fxtj.tdj_yfbl )>0  ");
					//如果上一级的
					$Model::$medoo->query("update user_fxtj set pid=ifnull((select pid from (select uid,pid from user_fxtj ) temp where temp.uid=user_fxtj.pid  limit 0,1),0) where sfypp_tdj=0 and pid>0 and tdj_yfbl<".$vos["jlqfb"]." ");
				}
				else
				{
					break;
				}
			} 
			  $Model::$medoo->query("update user_fxtj set jl_sxxzcb_qfb1=ifnull((select jlqfb from (select uid,jlqfb from user_fxtj ) temp where temp.uid=user_fxtj.pid limit 0,1),0) where pid>0 and sfypp_tdj=1  ");
			 //更大部分重新赋值
			  $Model::$medoo->query("update user_fxtj set ljjdsy=ljjdsy+(select ifnull(sum(viprd_jtsf*(jl_sxxzcb_qfb1-tdj_yfbl)/1000),0) from (select pid,viprd_jtsf,jl_sxxzcb_qfb1,tdj_yfbl,sfypp_tdj from user_fxtj ) temp where temp.pid=user_fxtj.uid and temp.viprd_jtsf>0 and temp.sfypp_tdj=1 and temp.tdj_yfbl<temp.jl_sxxzcb_qfb1 ) where jlqfb>0  ");
			  //累加伞下收益-无限代差额
			  //更新已返给上级级差比例
			  $Model::$medoo->query("update user_fxtj set tdj_yfbl=jl_sxxzcb_qfb1 where tdj_yfbl<jl_sxxzcb_qfb1  ");
			  //更新已返给上级级差比例
        }
		$Model::$medoo->query("update user_fxtj set is_jdsysf=1 where ljjdsy>0 ");
		return true;
		
	}
	
	public function issue_jdsyff($times,$rdjl_ptb,$price)
    {
		$where=array();
        $where['is_jdsysf']=1;
        $where['LIMIT']=[0,100];
		$user_fxtj_M=new \app\model\user_fxtj();
		$user_ar=$this->user_fxtj_M->lists_all($where,['id','uid','ljjdsy']);
		if(empty($user_ar)){
            return false;
        }
		$user_M=new \app\model\user();
		$user_fxtj_M=new \app\model\user_fxtj();
		$money_S = new \app\service\money();
		$vip_order_M=new \app\model\vip_order();
		$yj_ar4['is_jdsysf']=2;
        foreach($user_ar as $vos){
			$bc_money=$vos['ljjdsy'];
			$bhy_sfcj=0;
			if($bc_money>0){
				$viprd_wsf=$user_M->find($vos['uid'],'viprd_wsf');
				if($bc_money-$viprd_wsf>0){
					  $bc_money=$viprd_wsf;
					  $bhy_sfcj=1;
				}
				if($bc_money>0){
				$yj_ar['viprd_ysf[+]']=$bc_money;
				$yj_ar['viprd_wsf[-]']=$bc_money;
				$user_M->up($vos['uid'],$yj_ar);
				$money_ptb=$bc_money;
				//$money_usdt=$bc_money-$money_ptb;
				if($money_ptb>0){
					$money_ptb=$money_ptb/$price;
					$money_S->plus($vos['uid'],$money_ptb,'coin','vip_jdsy','无',$vos['uid'],'节点收益'); //记录资金流水
				 }
				 //if($money_usdt>0){
				//	 $money_S->plus($vos['uid'],$money_usdt,'viprd_usdt','vip_jdsy','无',$vos['uid'],'节点收益'); //记录资金流水
				// }
				 }
				 
				 if($bhy_sfcj==1){
				$yj_ar2['status']=1;
				$yj_ar2['cd_time']=time();
				$yj_tj['status']=0;
				$yj_tj['uid']=$vos['uid'];
				$this->vip_order_M->up_all($yj_tj,$yj_ar2);
				}
			}
			
		   $user_fxtj_M->up($vos['id'],$yj_ar4);
        }
        return true;
    }

}
