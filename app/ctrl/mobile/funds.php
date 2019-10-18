<?php 
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-03-28 09:23:02
 * Desc: IM
 */
namespace app\ctrl\mobile;
use app\model\money as money_Model;
use app\validate\FundsValidate;
class funds extends BaseController{
    
	public function __initialize(){
	}

    //首页
    public function index()
    {
        $user = $GLOBALS['user'];
        $funds_ar['sum_amount']=$user['sum_amount'];
        $funds_ar['sum_integral']=$user['sum_integral'];
        $funds_ar['is_recharge']=1;
        $funds_ar['is_withdraw']=c('ye_open');

        $where['iden[!]']=['coin','coin_storage','USDT','BTC','ETH','LTC','BCH','integrity'];
        $where['show']=1;
        $where['types']=2;
        $data=(new \app\model\reward())->reward_lists_all($where,'iden');
        $data_ar=array();
        foreach($data as $key=>&$vo){
            $data_ar[$key]['iden']=$vo;
            $data_ar[$key]['title']=find_reward_redis($vo);
        }
        $funds_ar['iden']=$data_ar;


        return $funds_ar;
    }


    //流水
    public function running_water()
    {
        (new \app\validate\PageValidate())->goCheck();
        (new \app\validate\FundsValidate())->goCheck('water');
        $user = $GLOBALS['user'];
        $iden=post("iden");
        $page = post("page",1);
        $page_size = post("page_size",10);
        
        $money_M=new money_Model();
        $where['cate']=$iden;
        $water=$money_M->lists_one($user['id'],$page,$page_size,$where);
		
        foreach($water as &$vo){
            if($vo['oid']=='无'){
                unset($vo['oid']);
            }
            if($vo['ly_id']==$vo['uid']){
                unset($vo['ly_id']);
            }
            if(isset($vo['ly_id'])){
                $users=user_info($vo['ly_id']);
                $vo['ly_nickname']=$users['nickname']?$users['nickname']:$users['username'];
            }
        }
        return $water;
    }

    public function recharge()
    {
        $pay_type = post('pay_type');
        $pay_S = new \app\service\pay();
        $data['pay'] =$pay_S->types($pay_type,1);
        return $data;
    }

    /*提交充值*/
    public function recharge_add()
    {
        $user = $GLOBALS['user'];
        (new FundsValidate())->goCheck('recharge');
        $money=post("money");
        $pay_id=post("pay_id");

        $where['iden[!]']=['money','integral'];
        $where['show']=1;
        $where['id']=$pay_id;
        $pay_ar=(new \app\model\pay())->have($where);
        empty($pay_ar) && error('支付方式不存在',400);
        flash_god($user['id']);
        $data['uid']=$user['id'];
        $data['money']=$money;
        $data['cate']='money';
        $data['types']=1;
        $order=(new \app\model\recharge())->save_by_oid($data);
        empty($order) && error('添加失败',10006);

        $pay_S = new \app\service\pay();
        $ar=$pay_S->index($pay_ar['iden'],$order['oid']);
        $ar['id']=$order['id'];
        return $ar;
    }

    //支付成功
    public function success()
    {
        (new \app\validate\IDMustBeRequire())->goCheck();
        $id = post('id');
        $or_where['id'] = $id;
        $or_where['uid'] = $GLOBALS['user']['id'];
        $order_ar = (new \app\model\recharge())->have($or_where);
        if (empty($order_ar)) {
            return ['info' => '订单不存在'];
        }

        $data['oid'] = $order_ar['oid'];
        $data['money'] = $order_ar['money'];
        $data['is_pay'] = $order_ar['status'];
        $data['pay_time'] = $order_ar['pay_time'];
        return $data;
    }
    

    public function recharge_lists()
    {
        $user = $GLOBALS['user'];
        (new \app\validate\PageValidate())->goCheck();
		$page=post("page",1);
        $page_size = post("page_size",10);		
        $coin_recharge_M=new \app\model\recharge();
        $where['uid']=$user['id'];
        $where['OR']['pay[!]']='管理员充值';
        $where['OR']['pay']=null;
        $ar=$coin_recharge_M->lists($page,$page_size,$where);
        foreach($ar as &$vo){
            $vo['title']="充币";
            $vo['cate_cn']=find_reward_redis($vo['cate']);
            if($vo['status'] == 1){
				$vo['status']='支付成功';
			}elseif($vo['status'] == 2){
				$vo['status']='支付中';
			}elseif($vo['status'] == 3){
				$vo['status']='支付失败';
			}else{
				$vo['status']='未支付';
			}
        }
        return $ar;
    }

    //提现
    public function withdraw()
    {
        if(c('ye_open')==0 || c('ye_balance')=='' || c('ye_txzczh')==''){
            error('提现未开启',10007);
        }
        if(c('ye_txdsgb')==1){
            $gbtxsj=c('ye_gbtxsj');
            $gbtxsj=explode("|",$gbtxsj);

            $day = date('Y-m-d',time());
            $begin_time = strtotime($day." ".$gbtxsj[0]);
            $end_time = strtotime($day." ".$gbtxsj[1]);
            if(time()<$begin_time || time()>$end_time){
                error('提现未开启',10007);
            }
        }
        
        $balance=renew_c('ye_balance');
        $balance=explode("|",$balance);
        $txzczh=c('ye_txzczh');
        $txzczh=explode("|",$txzczh);
        
        $user = $GLOBALS['user'];
        $data['txzczh']=$txzczh;
        $data['ye_txsxfbfb']=c('ye_txsxfbfb')/10; //提现手续费
        foreach($balance as $vo){
            if($vo){
                $data['balance'][$vo]['title']=find_reward_redis($vo);
                $data['balance'][$vo]['iden']=$vo;
                $data['balance'][$vo]['money']=$user[$vo];
            }
        }
        sort($data['balance']);

        $data['ye_txbs'] = c('ye_txbs'); //提现倍数
        $data['ye_zdtxje'] = c('ye_zdtxje'); //最底提现金额
        return $data;
    }



    /*提交提币*/
    public function withdraw_add()
    {
        $data=$this->withdraw();
        $user = $GLOBALS['user'];
        (new FundsValidate())->goCheck('withdraw');
        
        $iden=post("iden");
        $money=post("money");
        $pay=post("pay");
        
        $is_iden=0;
        foreach($data['balance'] as $vo){
            if($vo['iden']==$iden){
                $is_iden=1;
            }
        }
        empty($is_iden) && error('提现账户不支持',404);

        if(!in_array($pay,$data['txzczh'])){
            error('提现方式不支持',404);
        }
        if($money<c('ye_zdtxje')){
            error('最低提现金额'.c('ye_zdtxje'),404);
        }
        if(c('ye_txbs')>0){
            if (!is_int($money / c('ye_txbs'))) {
                error('提现金额必须是'.c('ye_txbs').'的倍数',404);
            }
        }

        $coin_withdraw_M=new \app\model\withdraw_ye();
        $sum_where['uid']=$user['id'];
        $sum_where['cate']=$iden;
        $sum_where['status']=[0,1,3];
        $sum_where['created_time[>]']=strtotime(date('Y-m-d'));
        $sum_money=$coin_withdraw_M->find_sum('money',$sum_where);
        if($sum_money+$money>c('ye_mrxtje')){
            error('超过每日限提金额',404);
        }

        $where['uid']=$user['id'];
        $where['status']=0;
        $coin_withdraw_ar=$coin_withdraw_M->is_have($where);
        if($coin_withdraw_ar){
            error('提现申请中，等待通过才能再申请',400);
        }

        //判断资格
        switch ($pay)
        {
        case '支付宝':
            if($user['alipay']=="" || $user['alipay_name']=="" || $user['alipay_pic']==""){
                error(['info'=>'请绑定收款信息','url'=>'/setting/alpaysetting'],10008);
            }else{
                $add_data['alipay']=$user['alipay'];
                $add_data['alipay_name']=$user['alipay_name'];
                $add_data['alipay_pic']=$user['alipay_pic'];
            }
            break;  
        case '微信':
            if($user['wechat']=="" || $user['wechat_pic']==""){
                error(['info'=>'请绑定收款信息','url'=>'/setting/alpaywx'],10008);
            }else{
                $add_data['wechat']=$user['wechat'];
                $add_data['wechat_pic']=$user['wechat_pic'];
            }
            break;
        case '网银':
            if($user['bank']=="" || $user['bank_card']=="" || $user['bank_network']=="" || $user['bank_name']=="" || $user['bank_province']=="" || $user['bank_city']==""){
                error(['info'=>'请绑定收款信息','url'=>'/setting/skzh'],10008);
            }else{
                $add_data['bank']=$user['bank'];
                $add_data['bank_card']=$user['bank_card'];
                $add_data['bank_network']=$user['bank_network'];
                $add_data['bank_name']=$user['bank_name'];
                $add_data['bank_province']=$user['bank_province'];
                $add_data['bank_city']=$user['bank_city'];
            }
            break;
        default:
            error('不支持提现方式',404);
        }


        //判断金额
        $user_M = new \app\model\user();
        $coin = $user_M->find($user['uid'],$iden);
        $fee=$money*c("ye_txsxfbfb")/1000;
        $integral=$money*c("ye_txzjf")/1000;
        if($iden=='supply'){
            $fee=0;
            $integral=0;
        }
        if($money-$coin>0){
           error('金额不足',10003);
        }

        //前台是否开启微信自动提现,开启后不用后台审核实到 发送现金红包
        $auto_withdraw = c('auto_mobile_wx_withdraw');    
        $wx_withdraw_type = c('wx_withdraw_type');

        $is_sqyctqyj = c('is_sqyctqyj');

        flash_god($user['id']);
        
        $redis = new \core\lib\redis();
        $Model = new \core\lib\Model();
        $Model->action();
        $redis->multi();

        $add_data['uid']=$user['id'];
        $add_data['fee']=$fee;
        $add_data['integral']=$integral;
        $add_data['money']=$money;
        $add_data['real_money']=sprintf("%.2f",($money-$fee-$integral));
        $add_data['cate']=$iden;
        $add_data['pay']=$pay;

        if($wx_withdraw_type == '红包发送' && $auto_withdraw==1 && $pay=='微信'){  
            $add_data['status'] = 1; //0申请中 1审核成功 2审核不成功 3审核中',
            $add_data['finish_time'] = time();
        }

        if($wx_withdraw_type == '企业付款' && $auto_withdraw==1 && $pay=='微信'){  
            $add_data['status'] = 1; //0申请中 1审核成功 2审核不成功 3审核中',
            $add_data['finish_time'] = time();
        }

        $recharge_ar=$coin_withdraw_M->save_by_oid($add_data);
        empty($recharge_ar) && error('添加失败',10006);

        $money_S = new \app\service\money();
        $money_S->minus($user['uid'],$money,$iden,'withdraw',$recharge_ar['oid'],$user['uid'],'申请提现'); //记录资金流水
        
        $uid = $GLOBALS['user']['id'];
        if($is_sqyctqyj==1){
            $new_duty_S = new \app\service\new_duty();
            $new_duty_S->paid_reward_noredis($uid,'sqyctqyj');   //新手任务 - 申请一次提现佣金
        }

        if($wx_withdraw_type == '红包发送' && $auto_withdraw==1 && $pay=='微信'){
            $user_M = new \app\model\user();
            $openid = $user_M->find($user['id'],'openid');
            $wechat_redpack_S = new \app\service\wechat_redpack();
            $wechat_redpack_S->wx_redpack($openid,$money,$add_data['real_money']*100,$recharge_ar['oid'],$uid,$iden);
        }

        if($wx_withdraw_type == '企业付款' && $auto_withdraw==1 && $pay=='微信'){
            $user_M = new \app\model\user();
            $openid = $user_M->find($user['id'],'openid');
            $wechat_redpack_S = new \app\service\wechat_redpack();
            $wechat_redpack_S->qy_redpack($openid,$money,$add_data['real_money']*100,$recharge_ar['oid'],$uid,$iden); 
        }

        $Model->run();
        $redis->exec();        
        return "申请成功，等待审核";
    }


    public function withdraw_lists()
    {
        $user = $GLOBALS['user'];
        (new \app\validate\PageValidate())->goCheck();
		$page=post("page",1);
        $page_size = post("page_size",10);		
        $coin_withdraw_M=new \app\model\withdraw_ye();
        $where['uid']=$user['id'];
        $ar=$coin_withdraw_M->lists($page,$page_size,$where);
        foreach($ar as &$vo){
                $vo['title']=$vo['cate']."提现";
                if($vo['status']==0){
                    $vo['status']="申请中";
                }else if($vo['status']==1){
                    $vo['status'] = "提现成功";
                }else if($vo['status']==2){
                    $vo['status'] = "审核不成功";
                }else{
                    $vo['status'] = "审核中";
                }
            }
        return $ar;
    }

   
}



 