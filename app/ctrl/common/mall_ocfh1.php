<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-14 11:38:07
 * Desc: 分红
 */
namespace app\ctrl\common;
use app\model\gdxq_order as gdxq_order_Model;
use app\model\config as config_Model;
use app\model\user as user_Model;
use app\model\xqgdjg as xqgdjg_Model;

class mall_ocfh1
{
	public $gdxq_order_M;
    public $config_M;
    public $user_M;
    public $run_oc1_M;
    public $money_S;
	public $xqgdjg;
	
    public function __construct()
    {
		$this->gdxq_order_M = new gdxq_order_Model();
		$this->xqgdjg_M = new xqgdjg_Model();
        $this->config_M = new config_Model();
        $this->user_M = new user_Model();
        $this->run_oc1_M = new \app\model\run_oc1();
        $this->money_S = new \app\service\money();
    }

    public function index()
    {
		$times1 = strtotime(date("Y-m-d")." 20:00:00");
		if(time()-$times1>0){ //晚上8点之后
        $gdxq_zdfc=C('gdxq_zdfc');
		$gdxq_jlqfb=C('gdxq_jlqfb');
		
            $redis = new \core\lib\redis();
            $Model = new \core\lib\Model();
            $Model->action();
            $redis->multi();
                $times=0;
                $open_times=0;
                $where=array();
				$times = strtotime(date("Y-m-d"));
			    $open_times = $times - 86400;
                $where['times[>=]'] = $times;
                $run_oc1_res = $this->run_oc1_M->have($where);
                if(empty($run_oc1_res)) {
                    //统计释放 奖励未释放
                    $data['times']=$times;
                    $data['status']=1;
                    $this->run_oc1_M->save($data);
					$Model = new \core\lib\Model();
					$Model::$medoo->query("update gdxq_order set g_money1=ifnull((SELECT mxq_fcsl from user where user.id=gdxq_order.uid limit 0,1 ),0) where rd_time>=".$times." and rd_time<".$times1." ");
					$Model::$medoo->query("update gdxq_order set g_money2=g_money,sf_time=".time()." where rd_time>=".$times." and rd_time<".$times1." and g_money<=g_money1 "); 
					$Model::$medoo->query("update gdxq_order set g_money2=g_money1,sf_time=".time()." where rd_time>=".$times." and rd_time<".$times1." and g_money>g_money1 "); 
					//重置攻打结算时，飞船时间，
					$where=array();
					$where['cdate'] = $times;
					$xqgdjg_M=new \app\model\xqgdjg();
					$xqgdjg_res = $this->xqgdjg_M->have($where);
					if(empty($xqgdjg_res)) { 
					  //如果未添加星球攻打结果。判断已有的记录，执行最低数量
					  //echo $times;
					  $Model = new \core\lib\Model();
					  $xqgdjg_ar=$Model::$medoo->query("SELECT id from gdxqsz order by ifnull((SELECT sum(g_money2) from gdxq_order where gdxq_order.gid=gdxqsz.id and gdxq_order.rd_time>=".$times." and gdxq_order.rd_time<".$times1." ),0) asc,id asc limit 0,1 ");
					  $jcjg=0;
					  if(empty($xqgdjg_ar)){
					  }
					  else{
						  $xhcs=0;
						  foreach($xqgdjg_ar as $vos){
							 $xhcs=$xhcs+1;
							 if($xhcs==1){
						     $jcjg=$vos["id"];
							 }
						  }
					  }
					  $data=array();
					  $data['cdate']=$times;
					  $data['jcjg']=$jcjg;
					  $this->xqgdjg_M->save($data);
					  $Model::$medoo->query("update gdxq_order set status=2 where rd_time>=".$times." and rd_time<".$times1." and gid<>".$jcjg." and status=0 "); 
					  $Model::$medoo->query("update gdxq_order set status=1,is_dtjlsf=1 where rd_time>=".$times." and rd_time<".$times1." and gid=".$jcjg." and status=0 "); 
					  $Model::$medoo->query("update gdxq_order set bc_money=g_money2*".$gdxq_jlqfb."/1000 where rd_time>=".$times." and rd_time<".$times1." and status=1 "); 
					}
					else{
						$Model::$medoo->query("update gdxq_order set status=2 where rd_time>=".$times." and rd_time<".$times1." and gid<>".$xqgdjg_res['jcjg']." and status=0 "); 
						$Model::$medoo->query("update gdxq_order set status=1,is_dtjlsf=1 where rd_time>=".$times." and rd_time<".$times1." and gid=".$xqgdjg_res['jcjg']." and status=0 "); 
						$Model::$medoo->query("update gdxq_order set bc_money=g_money2*".$gdxq_jlqfb."/1000 where rd_time>=".$times." and rd_time<".$times1." and status=1 "); 
					}
					
                }
				elseif($run_oc1_res['status']==1){
					$issue_res=$this->issue_xqgd($times,$times1);  //执行动态奖励释放
					if($issue_res==false){
                        $this->run_oc1_M->up($run_oc1_res['id'],['status'=>2]);
                    }
				}
            $Model->run();
            $redis->exec();
		}
      
    }

	
	public function issue_xqgd($times,$times1)
	{
		$where=array();
        $where['is_dtjlsf']=1;
		$where['rd_time[>=]']=$times;
        $where['rd_time[<]']=$times1;
		$where['status']=1;
        $where['LIMIT']=[0,100];
		$gdxq_order_M=new \app\model\gdxq_order();
        $gdxq_order_ar=$this->gdxq_order_M->lists_all($where,['id','oid','uid','bc_money']);
		if(empty($gdxq_order_ar)){
            return false;
        }
		$user_M=new \app\model\user();
		$gdxq_order_M=new \app\model\gdxq_order();
		$money_S = new \app\service\money();
		$yj_ar4['is_dtjlsf']=2;
        foreach($gdxq_order_ar as $vos){
			$bc_money=sprintf("%.3f",$vos['bc_money']);
			if($bc_money>0){
				$yj_ar['mxq_jlsl[+]']=$bc_money;
				$yj_ar['mxq_bcgdsl']=$bc_money;
				$user_M->up($vos['uid'],$yj_ar);
				$money_S->plus($vos['uid'],$bc_money,'mxq_fcsl','gdxqjl',$vos['oid'],$vos['uid'],'攻打星球奖励'); //记录资金流水
			}
		   $gdxq_order_M->up($vos['id'],$yj_ar4);
        }
        return true;
    }
	
}
