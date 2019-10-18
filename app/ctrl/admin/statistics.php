<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-18 13:50:53
 * Desc: 统计 首页数据统计
 */

namespace app\ctrl\admin;


class statistics extends PublicController{
    public $redis;
    public function __initialize(){
        $this->redis = new \core\lib\redis();  
    }

    public function index(){
        $coin = $this->redis->get('statistics:coin'); 
        $c2c = $this->redis->get('statistics:c2c');
        $mail = $this->redis->get('statistics:mail');
        $product = $this->redis->get('statistics:product');
        $order = $this->redis->get('statistics:order');
        $user = $this->redis->get('statistics:user');
        $supplier = $this->redis->get('statistics:supplier');
        $check = $this->redis->get('statistics:check');
        $amount = $this->redis->get('statistics:amount');
        $team = $this->redis->get('statistics:team');
        $res['mail'] = $mail;
        $res['product'] = $product;
        $res['order'] = $order;
        $res['user'] = $user;
        $res['supplier'] = $supplier;
        $res['check'] = $check;
        $res['amount'] = $amount;
        $res['team'] = $team;
        $res['math_1'] = $coin;
        $res['math_2'] = $c2c;
        return $res;
    }

    public function all_math()
    {
        $types=post("types");
        if($types){
        $res = $this->$types();
		admin_log('更新后台统计数据',$types);   
        return $res;
        }
    }

    public function mail()
    {
       $ar[] = ['注册量',(new \app\model\user())->new_count()]; //总注册量
        $ar[] = ['vip会员量',(new \app\model\user())->new_count(['vip_rating[>]'=>1])];  //会员量
		 $ar[] = ['节点会员量',(new \app\model\user())->new_count(['jddj_rating[>]'=>1])];  //会员量
		/*
        $ar[] = ['总订单量',(new \app\model\order())->new_count(['is_pay'=>1])];   //订单数
        $ar[] = ['总营业额',sprintf("%.2f",(new \app\model\order())->find_sum('money',['is_pay'=>1]))]; //总营业额
        $ar[] =['总提现额',sprintf("%.2f",(new \app\model\withdraw_ye())->find_sum('money',['status'=>1]))];           //总提现额
        $ar[] =['总充值额', sprintf("%.2f",(new \app\model\recharge())->find_sum('money',['status'=>1,'types'=>1,'cate'=>'money']))];      //总充值额

        if(plugin_is_open('fhfx')){
        $rating=(new \app\model\rating())->find(1);
        switch ($rating['dividend_cycle']) {
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
        $where_or['settle_time[>=]']=$open_times;
        $where_or['settle_time[<]']=$times;
        $sum=(new \app\model\order())->find_sum($fields,$where_or);

        $where_or2['is_pay']=1;
        $where_or2['is_settle']=1;
        $where_or2['settle_time[>=]']=$times;
        $sum2=(new \app\model\order())->find_sum($fields,$where_or2);
        $ar[] = ['上次订单业绩',sprintf("%.2f",$sum)];      //上次分红
        $ar[] = ['本次订单业绩',sprintf("%.2f",$sum2)];      //本次分红
        }
        $ar[] = ['总'.find_reward_redis('money'),sprintf("%.2f",(new \app\model\user())->find_sum('money',['show'=>1]))];      //总余额
        $ar[] =['总'.find_reward_redis('amount'), sprintf("%.2f",(new \app\model\user())->find_sum('amount',['show'=>1]))];      //总积分
        $ar[] = ['总'.find_reward_redis('integral'),sprintf("%.2f",(new \app\model\user())->find_sum('integral',['show'=>1]))];      //总佣金
        $ar[]  = ['总返利额',sprintf("%.2f",(new \app\model\money())->all_find_sum('money',['cate'=>'amount','types'=>1]))];  //总返利额
		*/
		$ar[] = ['总'.find_reward_redis('coin_storage'),sprintf("%.2f",(new \app\model\user())->find_sum('coin_storage',['show'=>1]))];      //总佣金
		$ar[] = ['总'.find_reward_redis('USDT'),sprintf("%.2f",(new \app\model\user())->find_sum('USDT',['show'=>1]))];      //总佣金
		$ar[] = ['总'.find_reward_redis('viprd_usdt'),sprintf("%.2f",(new \app\model\user())->find_sum('viprd_usdt',['show'=>1]))];      //总佣金
		$ar[] = ['总'.find_reward_redis('mxq_fcsl'),sprintf("%.2f",(new \app\model\user())->find_sum('mxq_fcsl',['show'=>1]))];      //总佣金
		$ar[] = ['总'.find_reward_redis('viprd_ljje'),sprintf("%.2f",(new \app\model\user())->find_sum('viprd_ljje',['show'=>1]))];      //总佣金
		$ar[] = ['总'.find_reward_redis('viprd_ljed'),sprintf("%.2f",(new \app\model\user())->find_sum('viprd_ljed',['show'=>1]))];      //总佣金
		$ar[] = ['总'.find_reward_redis('viprd_ysf'),sprintf("%.2f",(new \app\model\user())->find_sum('viprd_ysf',['show'=>1]))];      //总佣金
		$ar[] = ['总'.find_reward_redis('viprd_wsf'),sprintf("%.2f",(new \app\model\user())->find_sum('viprd_wsf',['show'=>1]))];      //总佣金
		$ar[] = ['总'.find_reward_redis('USDT_storage'),sprintf("%.2f",(new \app\model\user())->find_sum('USDT_storage',['show'=>1]))];      //总佣金
		$ar[] = ['总'.find_reward_redis('USDT_KY'),sprintf("%.2f",(new \app\model\user())->find_sum('USDT_KY',['show'=>1]))];      //总佣金
		$ar[] = ['总'.find_reward_redis('XJJJ'),sprintf("%.2f",(new \app\model\user())->find_sum('XJJJ',['show'=>1]))];      //总佣金
		$ar[] = ['总'.find_reward_redis('LMJJA'),sprintf("%.2f",(new \app\model\user())->find_sum('LMJJA',['show'=>1]))];      //总佣金
		$ar[] = ['总'.find_reward_redis('LMJJ'),sprintf("%.2f",(new \app\model\user())->find_sum('LMJJ',['show'=>1]))];      //总佣金
		$ar[] = ['总'.find_reward_redis('LMJJC'),sprintf("%.2f",(new \app\model\user())->find_sum('LMJJC',['show'=>1]))];      //总佣金
		$ar[] = ['总'.find_reward_redis('LMJJB'),sprintf("%.2f",(new \app\model\user())->find_sum('LMJJB',['show'=>1]))];      //总佣金
		$ar[] = ['总'.find_reward_redis('sactloop'),sprintf("%.2f",(new \app\model\user())->find_sum('sactloop',['show'=>1]))];      //总佣金

        $this->redis->set('statistics:mail',$ar);
        return $ar;
    }

    public function product()
    {
        $ar['insale'] = (new \app\model\product())->new_count(['sid'=>0,'show'=>1,'stock[>]'=>0]); //出售中商品
        $ar['warehouse'] = (new \app\model\product())->new_count(['sid'=>0,'show'=>0]);  //仓库中商品
        $ar['sold'] = (new \app\model\product())->new_count(['sid'=>0,'stock'=>0]);   //已售光商品
        $ar['pending']  = (new \app\model\order())->new_count(['sid'=>0,'is_pay'=>'0','status[!]'=>'已关闭']);  //待付款订单
        $ar['delivered'] = (new \app\model\order())->new_count(['sid'=>0,'status'=>['配货中','已支付']]); //待发货订单
        $ar['return'] = (new \app\model\order())->new_count(['sid'=>0,'is_return'=>1]);           //待退货订单
        $this->redis->set('statistics:product',$ar);
        return $ar;
    }

    public function supplier()
    {
        $ar['supplier_user'] = (new \app\model\user())->new_count(['is_supplier'=>1]); //总商户人数
        $ar['supplier_order'] = (new \app\model\order())->new_count(['sid[!]'=>0,'is_pay'=>1]);  //商品订单总量
        $ar['supplier_payment'] =  sprintf("%.2f",(new \app\model\money())->all_find_sum('money', ['iden'=>'supply']));   //已结贷款总额
        $ar['pending']  =  sprintf("%.2f",(new \app\model\order())->find_sum('cost', ['sid[!]' => 0, 'is_pay' => 1, 'is_settle' => 0]));  //未结贷款总额
        $ar['check_supplier'] = (new \app\model\supplier())->new_count(['is_check'=>0]); //待审核商户
        $ar['check_agent'] = (new \app\model\agent())->new_count(['is_check'=>0]);           //待审核代理
        $ar['agent_user'] = (new \app\model\user())->new_count(['is_agent[!]'=>0]);           //代理总人数
        $ar['agent_payment'] =  sprintf("%.2f",(new \app\model\money())->all_find_sum('money', ['iden'=>'agentaward']));           //代理总分润
        $this->redis->set('statistics:supplier',$ar);
        return $ar;
    }

    public function check()
    {
        $ar['agent'] = (new \app\model\agent())->new_count(['is_check'=>0]);           //待审核代理
        $ar['supplier'] = (new \app\model\supplier())->new_count(['is_check'=>0]); //待审核商户
        $ar['withdraw'] = (new \app\model\withdraw_ye())->new_count(['status'=>[0,3]]); //待审核提现
        $ar['return'] = (new \app\model\order())->new_count(['sid'=>0,'is_return'=>1]);   //待退货订单
        $ar['feedback'] = (new \app\model\feedback())->new_count(['is_check'=>0]);   //待审核留言
        $ar['review'] = (new \app\model\product_review())->new_count(['is_check'=>0]);   //待审核评论
        $ar['product'] = (new \app\model\product())->new_count(['is_check'=>0]);   //待审核商品
        $ar['promotion'] = (new \app\model\supplier_promotion())->new_count(['is_check'=>0]);   //待审核促销商品
        $this->redis->set('statistics:check',$ar);
        return $ar;
    }


    public function amount()
    {
        $where['ORDER']=['viprd_ysf'=>'DESC'];
        $where['LIMIT']=[0,10];
        $ar=(new \app\model\user())->lists_all($where,['id','username','nickname','avatar','viprd_ysf','coin']);
        foreach($ar as &$vo){
			$vo['uid']=$vo['id'];
            
            $vo['money']=$vo['coin'];
			 $vo['sum_amount']=$vo['viprd_ysf'];
        }
        $this->redis->set('statistics:amount',$ar);
        return $ar;
    }


    public function team()
    {
        $where['ORDER']=['vip_zvip'=>'DESC'];
        $where['LIMIT']=[0,10];
        $ar=(new \app\model\user_attach())->lists_all($where,['uid','vip_zvip','vip_yvip']);
        foreach($ar as &$vo){
            $users=user_info($vo['uid']);
            $vo['username']=$users['username'];
            $vo['nickname']=$users['nickname'];
            $vo['avatar']=$users['avatar'];
			 $vo['zvip']=$vo['vip_zvip'];
			  $vo['yvip']=$vo['vip_yvip'];
        }
        $this->redis->set('statistics:team',$ar);
        return $ar;
    }



    public function order()
    {
        $data['date']=array();
        $order_M=new \app\model\order();
        for($i=0;$i<10;$i++){
            $times=mktime(0, 0, 0, date("m"),date("d")-$i,date("Y"));
            $data['date'][$i]=date('m-d',mktime(0, 0, 0, date("m"),date("d")-$i,date("Y")));
            $where=array();
            $where['is_pay']=1;
            $where['sid']=0;
            $where['types[!]']=1;
            $where['pay_time[>=]']=$times;
            $where['pay_time[<]']=$times+86400;
            $data['amount'][$i]['all_count']=$order_M->new_count($where);
            $data['amount'][$i]['all_sum']=sprintf("%.2f",$order_M->find_sum('money',$where));
            unset($where['types[!]']);
            $where['types']=1;
            $data['amount'][$i]['vip_count']=$order_M->new_count($where);
            $data['amount'][$i]['vip_sum']=sprintf("%.2f",$order_M->find_sum('money',$where));

        }
        $this->redis->set('statistics:order',$data);
        return $data;
    }

    public function user()
    {
        $data['date']=array();
        $user_M=new \app\model\user();
        $user_attach_M=new \app\model\user_attach();
        for($i=0;$i<7;$i++){
            $times=mktime(0, 0, 0, date("m"),date("d")-$i,date("Y"));
            $data['date'][$i]=date('m-d',mktime(0, 0, 0, date("m"),date("d")-$i,date("Y")));
            $where1=array();
            $where2=array();
            $where1['rating']=1;
            $where1['created_time[>=]']=$times;
            $where1['created_time[<]']=$times+86400;
            $data[$i]['all']=$user_M->new_count($where1);

            $where2['upgrade_time[>=]']=$times;
            $where2['upgrade_time[<]']=$times+86400;
            $data[$i]['vip']=$user_attach_M->new_count($where2);
        }
        $this->redis->set('statistics:user',$data);
        return $data;
    }
    
    /*后台首页统计数据*/
    public function coin(){
        $user_M = new \app\model\user();
        
        $all_reg = $user_M->new_count();//总注册量
        $where_1['coin_rating[>]'] = 1;
        $user_number = $user_M->new_count($where_1);//会员量

        $all_coin = $user_M->find_sum('coin');
        $all_coin_storage = $user_M->find_sum('coin_storage');
        $all_web_coin = floatval($all_coin) + floatval($all_coin_storage); //总流通币量

        $money_M = new \app\model\money();
        $where_2['cate'] = 'coin_kjjl';
        $where_3['cate'] = 'coin_direct';
        $where_4['cate'] = 'coin_team';
        $all_coin_kjjl = $money_M->find_sum('money',$where_2); 
        $all_coin_direct = $money_M->find_sum('money',$where_3); 
        $all_coin_team = $money_M->find_sum('money',$where_4); 
        $all_reward = floatval($all_coin_kjjl) + floatval($all_coin_direct) + floatval($all_coin_team);
        //总奖励量 流水money表(分红coin_kjjl 直推coin_direct 团队coin_team)                       

        $all_publish = $all_web_coin - $all_reward; //总发行量  （流通币量-总奖励量)
       
         
        $c2c_order_M = new \app\model\c2c_order();

        $where_5['status']=3;
        $all_business = $c2c_order_M->find_sum('money',$where_5); //总交易量   c2c_order status=3 money和

        $coin_price_M = new \app\model\coin_price();
        $price = $coin_price_M->price();//当前价格

        $ar['all_reg'] = $all_reg;//总注册量
        $ar['user_number'] = $user_number;//会员量
        $ar['all_web_coin'] = $all_web_coin;//总流通币量
        $ar['all_reward'] = $all_reward;//总奖励量
        $ar['all_publish'] = $all_publish;//总发行量
        $ar['all_business'] = $all_business;//总交易量 
        $ar['price'] = $price;//当前价格

        $redis = new \core\lib\redis();  
        $redis->set('statistics:coin',$ar);
        return $ar;
    }
    
    public function c2c(){
        $where_6['status']= 1; //发布中
        $c2c_buy_M = new \app\model\c2c_buy();
        
        $all_buy_num = $c2c_buy_M->new_count($where_6); //求购中总量（笔） 
        $all_buy_coin = $c2c_buy_M->find_sum('money',$where_6);  //求购中总量（币） 

        $coin_recharge_M = new \app\model\coin_recharge();
        $where_7['status']= 1; //充值成功
        $all_recharge = $coin_recharge_M->find_sum('money',$where_7); //总充币量（币）   
             
        $c2c_order_M = new \app\model\c2c_order();     
        $where_8['status']=3; //交易成功
        $all_trade_fee = $c2c_order_M->find_sum('fee',$where_8); //总交易手续费（币）  
          
        $where_9['status']=1; //互转成功
        $transfer_M = new \app\model\transfer();
        $all_tran_fee = $transfer_M->find_sum('fee',$where_9); //总互转手续费（币） 

        $where_10['status'] =1 ; //提币成功
        $coin_exchange_M = new \app\model\coin_exchange();
        $all_exchange_fee = $coin_exchange_M->find_sum('fee',$where_10);  //总兑币手续费（币）

        $res['all_buy_num'] = $all_buy_num;//求购中总量（笔） 
        $res['all_buy_coin'] = $all_buy_coin;//求购中总量（币） 
        $res['all_recharge'] = $all_recharge;//总充币量（币）
        $res['all_trade_fee'] = $all_trade_fee;//总交易手续费（币）
        $res['all_tran_fee'] = $all_tran_fee;//总互转手续费（币） 
        $res['all_exchange_fee'] = $all_exchange_fee;//总兑币手续费（币）
        $redis = new \core\lib\redis();  
        $redis->set('statistics:c2c',$res);
        return $res;
    }

}