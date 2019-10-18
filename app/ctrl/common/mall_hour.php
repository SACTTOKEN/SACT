<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-14 11:38:07
 * Desc: 分红
 */
namespace app\ctrl\common;

use app\model\order as order_Model;
use app\model\config as config_Model;
use app\model\user as user_Model;

class mall_hour
{

    public $order_M;
    public $config_M;
    public $user_M;
    public $run_M;
    public $money_S;
    public $user_gx_M;
    public $rating_M;


    public function __construct()
    {
        $this->order_M = new order_Model();
        $this->config_M = new config_Model();
        $this->user_M = new user_Model();
        $this->run_M = new \app\model\run();
        $this->money_S = new \app\service\money();
        $this->user_gx_M = new \app\model\user_gx();
        $this->rating_M = new \app\model\rating();
    }

    public function index()
    {
        if(plugin_is_open('fhfx')){
            $redis = new \core\lib\redis();
            $Model = new \core\lib\Model();
            $Model->action();
            $redis->multi();
            
            $rating_M = new \app\model\rating();
            $rating_ar = $rating_M->lists_all(['dividend[>]' => 0], ['id', 'dividend', 'dividend_cycle', 'dividend_account', 'dividend_types']);
            foreach ($rating_ar as $vo) {
                $times=0;
                $open_times=0;
                $where=array();
                switch ($vo['dividend_cycle']) {
                    case 0:
                        $times = strtotime(date("Y-m-d H:00:00"));
                        $open_times = $times - 3600;
                        break;
                    case 1:
                        $times = strtotime(date("Y-m-d")) ;
                        $open_times = $times - 86400;
                        break;
                    case 2:
                        $times = mktime(0, 0, 0, date("m"), date("d") - date("w") + 1 , date("Y"));
                        $open_times = mktime(0, 0, 0, date("m"), date("d") - date("w") + 1 - 7, date("Y"));
                        break;
                    case 3:
                        $times = mktime(0, 0, 0, date("m") , 1, date("Y"));
                        $open_times = mktime(0, 0, 0, date("m") - 1, 1, date("Y"));
                        break;
                    default:
                        return false;
                }
                $where['iden'] = 'dividend';
                $where['rating'] = $vo['id'];
                $where['times[>=]'] = $times;
                $run_res = $this->run_M->have($where);
                if(empty($run_res)) {
                    //无运行记录添加运行记录，运行全站统计
                    $data['times']=$times;
                    $data['iden']='dividend';
                    $data['rating']=$vo['id'];
                    $data['status']=1;
                    $this->run_M->save($data);
                    $this->unified($times,$open_times,$vo);
                }elseif($run_res['status']==1){
                    //有运行记录，未发放记录，每次发放100条记录
                    $issue_res=$this->issue($vo);
                    if($issue_res==false){
                        $this->run_M->up($run_res['id'],['status'=>2]);
                    }
                }
            }
            $Model->run();
            $redis->exec();
        }
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
}
