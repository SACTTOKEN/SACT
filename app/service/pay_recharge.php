<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-08-12 10:51:45
 * Desc: 充值 支付接口（话费，油卡，流量）
 */
namespace app\service;

class pay_recharge{
	public $juhe_M; //相当$orderM
    public $money_S;
    public $pay_M;
    public function __construct()
    {
		$this->juhe_M = new \app\model\juhe_recharge();
        $this->money_S = new \app\service\money();
        $this->pay_M = new \app\model\pay();
    }

    public function index($iden,$oid)
    {
        switch ($iden) {
            case 'money':
                $ar = $this->pay_money($oid);
                break;
            case 'coin':
                $ar = $this->pay_coin($oid);
                break;
            case 'alipay':
                $ar = $this->pay_alipay($oid);
                break;
            case 'alipay_app':
                $ar = $this->pay_alipay_app($oid);
                break;
            case 'wechat':
                $ar = $this->pay_wechat($oid);
                break;
            case 'wechat_app':
                $ar = $this->pay_wechat_app($oid);
                break;
            case 'full_pay':
                $ar = $this->pay_full_pay($oid);
                break;
            case 'full_pay_app':
                $ar = $this->pay_full_pay_app($oid);
                break;
            default:
        }
        return $ar;
    }

    public function types($pay_type,$is_recharge=0)
    {
        //1微信  2app  3网页
        $pay_where['show'] = 1;
        switch ($pay_type) {
            case 1:
                $pay_where['iden[!]'] = ['wechat_app', 'alipay','alipay_app','full_pay_app'];
                break;
            case 2:
                if($this->pay_M->is_have(['iden'=>'alipay_app','show'=>1])){
                    $pay_where['iden[!]'] = ['wechat','alipay','full_pay','full_pay'];
                }else{
                    $pay_where['iden[!]'] = ['wechat','alipay_app','full_pay','full_pay'];
                }
                break;
            default:
                $pay_where['iden[!]'] = ['wechat_app', 'wechat','alipay_app','full_pay'];
        }
        if($is_recharge==1){
        $pay_where['AND']['iden[!]']=['money','integral'];
        }
        $pay_where['ORDER'] = ['sort' => 'DESC'];
        $pay = $this->pay_M->lists_all($pay_where, ['id', 'title', 'piclink']);
        return $pay;
    }

    //余额
	public function pay_money($oid){
        $order=(new \app\model\juhe_recharge())->have(['oid'=>$oid]);
        $user=$GLOBALS['user'];
        //判断金额
        $user_M = new \app\model\user();
        $money = $user_M->find($user['uid'],'money');
        if($order['money']-$money>0){
            error('金额不足',10003);
        }
        
        $redis = new \core\lib\redis();
        $Model = new \core\lib\Model();
        $Model->action();
        $redis->multi();
        $this->money_S->minus($user['id'],$order['money'],'money','juhe_in',$order['oid'],$user['id'],'订单支付'); //记录资金流水
        $data['is_pay']=1;
        //$data['status']='已支付';
        //$data['pay_time']=time();
        $data['pay']='余额支付';
        $this->juhe_M->up($order['id'],$data);

        $Model->run();
        $redis->exec();
        $ar['out_trade_no'] = $oid;
        $ar['money'] = $order['money'];
        $trade_type = '余额支付';
        $juhe_S = new \app\service\juhe_recharge();
        $juhe_S -> pay_success($ar,$trade_type);
        return ['is_pay'=>1,'id'=>$order['id']];
	}


    
    //支付宝
	public function pay_alipay($oid){
        $alipay=new \extend\alipay_wap\pay();
        $res=$alipay->index($oid);
        return $res;
    }

    //支付宝APP
	public function pay_alipay_app($oid){
        $alipay=new \extend\alipay_app\pay();
        $res=$alipay->index($oid);
        return $res;
    }
    
    //微信
	public function pay_wechat($oid){
        $wechat=new \extend\wechat_pay\jsapi();
        $res=$wechat->index($oid);
        return $res;
	}
    
    //微信APP
	public function pay_wechat_app($oid){
        $wechat=new \extend\wechat_pay\jsapi('wechat_app');
        $res=$wechat->index($oid);
        return $res;
    }

    //中信银行微信公众号
	public function pay_full_pay($oid){
        $wechat=new \extend\full_pay\request();
        $res['data']=$wechat->submitOrderInfo($oid);
        $res['is_full_pay']=1;
        return $res;
    }

    //中信银行微信APP支付
	public function pay_full_pay_app($oid){
        $wechat=new \extend\full_pay\request();
        $res['data']=$wechat->submitOrderInfo_app($oid);
        $res['is_full_pay_app']=1;
        return $res;
    }

    


}