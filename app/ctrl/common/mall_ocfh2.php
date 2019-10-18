<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-14 11:38:07
 * Desc: 分红
 */
namespace app\ctrl\common;
use app\model\hjzj_order as hjzj_order_Model;
use app\model\config as config_Model;
use app\model\user as user_Model;
use app\model\coin_price as coin_price_Model;
class mall_ocfh2
{
	public $hjzj_order_M;
    public $config_M;
    public $user_M;
    public $run_oc2_M;
    public $money_S;
	public $coin_price_M;

	
    public function __construct()
    {
		$this->hjzj_order_M = new hjzj_order_Model();
        $this->config_M = new config_Model();
        $this->user_M = new user_Model();
        $this->run_oc2_M = new \app\model\run_oc2();
        $this->money_S = new \app\service\money();
		$this->coin_price_M = new coin_price_Model();
    }

    public function index()
    {
		
		$jtgdsj=date("w");
		if($jtgdsj==6){  //只有周六
		    $hjzj_zdqfb=C('hjzj_zdqfb');
			
			$price=$this->coin_price_M->price();
		
            $redis = new \core\lib\redis();
            $Model = new \core\lib\Model();
            $Model->action();
            $redis->multi();
                $times=0;
                $open_times=0;
                $where=array();
				$times = strtotime(date("Y-m-d"));
			    $open_times = $times - 86400;
                $where['times'] = $times;
                $run_oc2_res = $this->run_oc2_M->have($where);
                if(empty($run_oc2_res)) {
                    //统计释放 奖励未释放
                    $data['times']=$times;
                    $data['status']=1;
                    $this->run_oc2_M->save($data);
					$issue_res=$this->issue_xqgd($times,$hjzj_zdqfb,$price);  //执行动态奖励释放
					
                }
				elseif($run_oc2_res['status']==1){
					$issue_res=$this->issue_xqgd($times,$hjzj_zdqfb,$price);  //执行动态奖励释放
					if($issue_res==false){
                        $this->run_oc2_M->up($run_oc2_res['id'],['status'=>2]);
                    }
				}
            $Model->run();
            $redis->exec();
		}
      
    }

	
	public function issue_xqgd($times,$hjzj_zdqfb,$price)
	{
		$where=array();
        $where['rd_time[<]']=$times;
		$where['status']=0;
        $where['LIMIT']=[0,100];
		$hjzj_order_M=new \app\model\hjzj_order();
        $hjzj_order_ar=$this->hjzj_order_M->lists_all($where,['id','oid','uid','money']);
		if(empty($hjzj_order_ar)){
            return false;
        }
		$user_M=new \app\model\user();
		$hjzj_order_M=new \app\model\hjzj_order();
		$money_S = new \app\service\money();
		$yj_ar4['status']=2;
		$yj_ar4['sf_time']=$times;
        foreach($hjzj_order_ar as $vos){
			$bc_money=$vos['money']*$hjzj_zdqfb/1000;
			$bc_money=sprintf("%.3f",$bc_money);
			$yj_ar4['bc_money']=$bc_money;
			if($bc_money>0){
				//$money=$vos['money']+$bc_money;
				$money=$bc_money;
				$money=$bc_money*$price;
				$money_S->plus($vos['uid'],$vos['money'],'coin','hjzj_del',$vos['oid'],$vos['uid'],'攻打黄金战舰返航'); //记录资金流水
				$money_S->plus($vos['uid'],$money,'USDT_storage','hjzj_del',$vos['oid'],$vos['uid'],'攻打黄金战舰返航'); //记录资金流水
			}
		   $hjzj_order_M->up($vos['id'],$yj_ar4);
        }
        return true;
    }
	
}
