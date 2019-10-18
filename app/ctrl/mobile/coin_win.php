<?php 
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-03-28 09:23:02
 * Desc: 币定盈
 */
namespace app\ctrl\mobile;
use app\model\coin_win as coin_win_Model;
use app\validate\IDMustBeRequire;
use app\validate\CoinWinValidate;

class coin_win extends BaseController{

    public $coin_win_M;
	public function __initialize(){
		$this->coin_win_M = new coin_win_Model();
	}

   

    //7天，30天，50天 三个周期，同一个人下注一个周期内只下注一次。
    //coin_storage AICQ存储   coin AICQ活动
    public function coin_bet(){
        (new coinWinValidate())->goCheck('sence_add');

        $is_open = c('is_open_coin_win');
        empty($is_open) && error('未开放,敬请期待！',400);

        $uid = $GLOBALS['user']['id'];
        $stake = post('stake');  //转AICQ活动 为 AICQ存储 拿利息

        if(intval($stake)<=0){
            error('投入值错误',400);
        }

        $stage_type = post('stage_type');   //stage_7,stage_30,stage_50

        switch ($stage_type) {
            //体验版 每个用户只能买一次
            case 'stage_0': 
                $cycle = 30;
                if($stake!=5){error('非法金额',400);}
                $where_0['uid'] = $uid;
                $where_0['stage_type'] = 'stage_0';
                $is_have = $this->coin_win_M->is_have($where_0);
                if($is_have){
                    error('体验版只能购买一次',400);
                }
                break;

            case 'stage_7':   
                $cycle = 7;
                break;
            case 'stage_30':
                $cycle = 30;
            break;
            case 'stage_50':
                $cycle = 50;
            break; 
            default:
                error('下单类型错误',400);
            break;        
        }
    
        $where['uid'] = $uid;
        $where['stage_type'] = $stage_type;
        $where['OR']['stage[>=]'] = time();
        $where['OR']['is_end'] = 0;
        $is_have = $this->coin_win_M->is_have($where);
        if($is_have){
            error('已投注本期,你在周期内不能复投',400);
        }

        $is_open_five = c('is_open_five_win');

            $model = new \core\lib\Model();
            $redis = new \core\lib\redis();  
            $model->action();
            $redis->multi();

            $this->coin_in($stake,$cycle,$stage_type); //投币
            if($is_open_five && $stage_type!='stage_0'){
                $this->five_win($stake); //五级分销
            }     
        
            //cs($this->coin_win_M->log(),1);

            $model->run();
            $redis->exec();

        return true;
    }


    //投币
    public function coin_in($stake,$cycle,$stage_type){
            if($stake <= 0){
                error('非法金额',400);
            }
            $uid = $GLOBALS['user']['id'];
            $user_M = new \app\model\user();
            $my_coin = $user_M->find($uid,'coin');

            if($my_coin < $stake ){
                error('币不足',400);
            }

            $data['stake'] = $stake;
            $data['stage'] = time() + 3600*24*$cycle; 
            $data['balance_type'] = 'coin_storage';
            $data['uid'] = $uid;
            $data['stage_type'] = $stage_type;
            if($stage_type == 'stage_0'){
                $data['is_ty'] = 1;
            }else{
                $data['is_ty'] = 0;
            }    
            $res = $this->coin_win_M->save_by_oid($data);
            empty($res) && error('投币失败',400);
            $money_S = new \app\service\money();
            $remark1 = '币定盈转入存储';
            $money_S->minus($uid,$stake,'coin','coin_win_change',$res['oid'],$uid,$remark1);
            $remark2 = '币定盈投入';
            $money_S->plus($uid,$stake,'coin_storage','coin_win_in',$res['oid'],$uid,$remark2);
    }

    //币定盈列表
    public function coin_list(){

        $time_now = time();
        $uid = $GLOBALS['user']['id'];
        $where['uid'] = $uid;
        $where['ORDER'] = ['is_ty'=>'DESC','id'=>'DESC'];
        $ar = $this->coin_win_M->lists_all($where);
        
        foreach($ar as &$one){
            switch ($one['stage_type']){
                case 'stage_0':
                    $win = renew_c('coin_win_stage_0');
                    $cycle = 30;
                break;
                case 'stage_7':
                    $win = renew_c('coin_win_stage_7');
                    $cycle = 7;
                break;
                
                case 'stage_30':
                    $win = renew_c('coin_win_stage_30');
                    $cycle = 30;
                break;

                case 'stage_50':
                    $win = renew_c('coin_win_stage_50');
                    $cycle = 50;                    
                break; 
                default:
                    error('周期类型错误',400);
                break;
            }

 
            //求当前时间已涨到的币值，求每秒涨币数 is_end 0：未结算 1：已结算 2：结算中            
            if($one['is_end']=='0'){
                $earn = floatval($one['stake']) * $win/100 ; //最终得到

                $miao = ($one['stake'] * $win) / ($cycle * 24 * 3600 * 100);  //每秒涨币数

                $miao = number_format($miao, 8, '.', '');

                $created_time =  $one['created_time'];

                if($one['stage'] < $time_now){
                    $one['is_end'] = 2;
                }else{
                   
                    $coin_now = intval(time() - $created_time) * $miao;
                    $coin_now  = number_format($coin_now, 8, '.', '');
                    if($coin_now > $earn){$coin_now = 0;}
                }

                $one['coin_now'] = $coin_now;
                $one['coin_all'] = ($one['stake'] * $win/100) + $one['stake'];
                $one['miao'] = $miao;
                $one['mytime'] = (floatval($one['stage']) - time())*1000;
            }else{
                $one['coin_now'] = ($one['stake'] * $win/100) + $one['stake'];
                $one['coin_all'] = ($one['stake'] * $win/100) + $one['stake'];
                $one['miao'] = 0;
                $one['mytime'] = 0;
            }
        }
        unset($one);
        return $ar;
    }



    //五级分销  参考service/order_complete return_points
    //调用示例：
    // if(res_plugin_is_open('tyxfx')){
    //     if($win>0){
    //         $this->five_win($win);
    //     }
    // }
    public function five_win($win){
        $uid = $GLOBALS['user']['id'];
        $y_where['uid'] = $uid;
        $y_where['level[<=]'] = 5;
        $y_where['coin_rating[!]'] = 1;
        $y_where['t_coin_rating[!]'] = 1;

        $user_gx_M = new \app\model\user_gx();
        $y_user_gx_ar = $user_gx_M->lists_plus($y_where);

        $user_M = new \app\model\user();
        $money_S = new \app\service\money();
        $config_M = new \app\model\config();

        foreach ($y_user_gx_ar as $vo) {
            $t_rating = $user_M->find($vo['tid'], 'rating'); //推荐人的等级 
            $level = 'coin_win_lv_' . $vo['level'];   //我上面第几代推荐人，直推是1

            $my_fee = $config_M->find($level);
            $money = $win * $my_fee/1000;
            $oid = date('Ymd').time().rand(1,1000);
            $remark = '币定盈奖励';
            $money_S->plus($vo['tid'], $money, 'coin', 'coin_winner', $oid, $uid, $remark, 'sum_coin');
        }
    }    

}

 