<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-03-01 17:18:54
 * Desc: 用户相关
 */
namespace app\service;
use app\model\c2c_buy as c2c_buy_Model;
use app\model\c2c_order as c2c_order_Model;
use app\model\coin_price as coin_price_Model;
use core\lib\Model;
use core\lib\redis;

class c2c{
    public $c2c_buy_M;
    public $coin_price_M;
    public $c2c_order_M;
    public function __construct()
    {
		$this->c2c_buy_M = new c2c_buy_Model();
		$this->coin_price_M = new coin_price_Model();
		$this->c2c_order_M = new c2c_order_Model();
    }

    /*订单详情*/
    public function detail($id)
    { 
        $user = $GLOBALS['user'];
        $where['id']=$id;
        $where['OR']=[
            "uid_buy"=>$user['id'],
            "uid_sell"=>$user['id']
        ];
        $order_ar=$this->c2c_order_M->have($where);
        empty($order_ar) && error('订单不存在',10007);

        if($order_ar['uid_buy']==$user['id']){
            $uid=$order_ar['uid_sell'];
            $order_ar['oid']=$order_ar['oid_sell'];
        }else{
            $uid=$order_ar['uid_buy'];
            $order_ar['oid']=$order_ar['oid_buy'];
        }
        $user_other=user_info($uid);
        $order_ar['im']=$user_other['im'];
        $order_ar['tel']=$user_other['tel'];
        $order_ar['other_alipay']=$user_other['alipay'];
        $order_ar['other_wechat']=$user_other['wechat'];
        $order_ar['nickname']=$user_other['nickname'];
        $order_ar['total_price']=bcmul($order_ar['money'],$order_ar['price'],1);
        if(!$order_ar['nickname']){
            $order_ar['nickname']=$user_other['username'];
        }
        //状态
        switch ($order_ar['state'])
        {
        case '0':
            $order_ar['state']="申述";
            break;  
        case '1':
            $order_ar['state']="买家申述中";
            break;  
        case '2':
            $order_ar['state']="卖家申述中";
            break;  
        case '3':
            $order_ar['state']="买家胜诉";
            break;  
        case '4':
            $order_ar['state']="卖家胜诉1";
            break;  
        default:
        }
        $order_ar['countdown']=0;
        //状态
        switch ($order_ar['status'])
        {
        case '1':
            $order_ar['status']='待付款';
            if($order_ar['uid_buy']==$user['id']){
                $order_ar['countdown']=$order_ar['created_time']+c("c2c_payment_time")*3600-time();
                $order_ar['info']='时间内确认付款';
                $order_ar['info2']='请提交转账截图';
                $order_ar['button']='确认付款';
            }else{
                $order_ar['info']='买家拍下未付款，等待买家付款';
                $order_ar['info2']='买家拍下未付款，等待买家付款';
                $order_ar['button']='等待买家付款';
            }
            break;  
        case '2':
            $order_ar['status']='已付款';
            if($order_ar['uid_buy']==$user['id']){
                $order_ar['info']='买家已付款，等待卖家确认收款';
                $order_ar['info2']='等待卖家确认收款';
                $order_ar['button']='等待卖家确认';
            }else{
                $order_ar['countdown']=$order_ar['created_time']+c("c2c_receipt_time")*3600-time();
                $order_ar['info']='时间内确认收款';
                $order_ar['info2']='请务必登录收款账户确认到账明细，避免因错误点击放行造成财产损失';
                $order_ar['button']='确认收款';
            }
            break;
        case '3':
            $order_ar['status']='已完成';
            $order_ar['info']='交易已完成';
            break;
        case '4':
            $order_ar['status']='已取消';
            $order_ar['info']='交易已取消';
            break;
        default:
        }
        if($order_ar['state']!="申述"){
        $order_ar['countdown']=0;
        $order_ar['info']=$order_ar['state'];
        }
        if($order_ar['countdown']<0){
            $order_ar['countdown']=0;
        }

        //收款信息
        if($order_ar['uid_sell']==$user['id']){
            $users=$user;
        }else{
            $users=$user_other;
        }
        $manner_ar=explode("@",$order_ar['manner']);
        foreach($manner_ar as $vo){
            switch ($vo)
            {
            case '支付宝':
                $order_ar['alipay']=$users['alipay'];
                $order_ar['alipay_name']=$users['alipay_name'];
                $order_ar['alipay_pic']=$users['alipay_pic'];
                break;  
            case '微信':
                $order_ar['wechat']=$users['wechat'];
                $order_ar['wechat_pic']=$users['wechat_pic'];
                break;
            case '银行卡':
                $order_ar['bank']=$users['bank'];
                $order_ar['bank_card']=(string)$users['bank_card'];
                $order_ar['bank_network']=$users['bank_network'];
                $order_ar['bank_name']=$users['bank_name'];
                $order_ar['bank_province'] = $users['bank_province'];
                $order_ar['bank_city'] = $users['bank_city'];
                break;
            default:
            }
        }
        return $order_ar;
    }


    public function pay($id)
    {
        $user = $GLOBALS['user'];
        $judge['uid']=$user['id'];
        $judge['status[<]']=3;
        $is_buy=$this->c2c_buy_M->is_have($judge);
        if($user['designation']==0){
            if($is_buy){
                error('交易完成才能发布下一个',400);
            }
        }
        $where['id']=$id;
        $where['status']=1;
        $where['types']=2;
        $buy_ar=$this->c2c_buy_M->have($where);
        empty($buy_ar) && error('订单已被抢售',400);
        if($buy_ar['uid']==$user['id']){
            error('自己发布的出售',400);
        }

        $uwhere['id']=$user['id'];
        $uwhere['pay_password']='';
        $user_ar=(new \app\model\user())->is_have($uwhere);
        if($user_ar){
            $err['info']='请先设置支付密码';
            $err['url']='/setting/pay_password';
            error($err,10008);	
        }
        
        if($user['integrity']<=0){
            error('诚信值不足，请联系客服',400);
        }
   

        $price=$this->coin_price_M->price();
        if($price<=0){
            error('价格错误',400);	
        }
        
        flash_god($user['id']);

        $Model = new Model();
        $redis = new redis();
        $Model->action();
        $redis->multi();
        //下单
        $data['money']=$buy_ar['money'];
        $data['manner']=$buy_ar['manner'];
        $data['uid']=$user['id'];
        $data['status']=1;
        $data['types']=1;
        $data['price']=$price;
        $sell_ar=$this->c2c_buy_M->save_by_oid($data);
        empty($sell_ar) && error('添加失败',10006);	
        //生成订单
        $this->c2c_buy_M->up($buy_ar['id'],['status'=>2]);
        $this->c2c_buy_M->up($sell_ar['id'],['status'=>2]);
        $order['oid_sell']=$buy_ar['oid'];
        $order['uid_sell']=$buy_ar['uid'];
        $order['money']=$buy_ar['money'];
        $order['price']=$price;
        $order['manner']=$buy_ar['manner'];
        $order['oid_buy']=$sell_ar['oid'];
        $order['uid_buy']=$sell_ar['uid'];
        $order['fee']=$buy_ar['fee'];
        $order_ar=$this->c2c_order_M->save_by_oid($order);
        empty($order_ar) && error('添加失败',10006);
      
        $Model->run();
        $redis->exec();
        return true;
    }

    /* 卖出 */
    public function sell($id)
    {
        $user = $GLOBALS['user'];
        $judge['uid']=$user['id'];
        $judge['status[<]']=3;
        $is_buy=$this->c2c_buy_M->is_have($judge);
        if($user['designation']==0){
            if($is_buy){
                error('交易完成才能发布下一个',400);
            }
        }
        $where['id']=$id;
        $where['status']=1;
        $where['types']=1;
        $buy_ar=$this->c2c_buy_M->have($where);
        empty($buy_ar) && error('订单已被抢售',400);
        if($buy_ar['uid']==$user['id']){
            error('自己发布的求购',400);
        }
        if($user['coin_rating']==1){
            error('游客不能出售',400);
        }
       
        
        $uwhere['id']=$user['id'];
        $uwhere['pay_password']='';
        $user_ar=(new \app\model\user())->is_have($uwhere);
        if($user_ar){
            $err['info']='请先设置支付密码';
            $err['url']='/setting/pay_password';
            error($err,10008);	
        }

        
        if($user['integrity']<=0){
            error('诚信值不足，请联系客服',400);
        }
        
        //判断金额
        $user_M = new \app\model\user();
        $coin = $user_M->find($user['uid'],'coin');
        $fee=$buy_ar['money']*c("trade_fee")/1000;

        if($buy_ar['money']+$fee-$coin>0){
           error('金额不足1',10003);
        }

        //判断资格
        $manner_ar=explode("@",$buy_ar['manner']);
        foreach($manner_ar as $vo){
            switch ($vo)
            {
            case '支付宝':
                if($user['alipay']=="" || $user['alipay_name']=="" || $user['alipay_pic']==""){
                    error(['info'=>'请绑定收款信息','url'=>'/setting/alpaysetting'],10008);
                }
                break;  
            case '微信':
                if($user['wechat']=="" || $user['wechat_pic']==""){
                    error(['info'=>'请绑定收款信息','url'=>'/setting/alpaywx'],10008);
                }
                break;
            case '银行卡':
                if($user['bank']=="" || $user['bank_card']=="" || $user['bank_network']=="" || $user['bank_name']==""){
                    error(['info'=>'请绑定收款信息','url'=>'/setting/skzh'],10008);
                }
                break;
            default:
            }
        }

        $price=$this->coin_price_M->price();
        if($price<=0){
            error('价格错误',400);	
        }
        
        flash_god($user['id']);

        $Model = new Model();
        $redis = new redis();
        $Model->action();
        $redis->multi();
        //下单
        $data['money']=$buy_ar['money'];
        $data['manner']=$buy_ar['manner'];
        $data['uid']=$user['id'];
        $data['status']=1;
        $data['types']=2;
        $data['price']=$price;
        $data['fee']=$fee;
        $sell_ar=$this->c2c_buy_M->save_by_oid($data);
        empty($sell_ar) && error('添加失败',10006);	
        //生成订单
        $this->c2c_buy_M->up($buy_ar['id'],['status'=>2]);
        $this->c2c_buy_M->up($sell_ar['id'],['status'=>2]);
        $order['oid_buy']=$buy_ar['oid'];
        $order['uid_buy']=$buy_ar['uid'];
        $order['money']=$buy_ar['money'];
        $order['price']=$price;
        $order['manner']=$buy_ar['manner'];
        $order['oid_sell']=$sell_ar['oid'];
        $order['uid_sell']=$sell_ar['uid'];
        $order['fee']=$sell_ar['fee'];
        $order_ar=$this->c2c_order_M->save_by_oid($order);
        empty($order_ar) && error('添加失败',10006);
        
        $money_S = new \app\service\money();
        $money=$order['money']+$order['fee'];
        $money_S->minus($user['uid'],$money,'coin','coin_c2c',$sell_ar['oid'],$buy_ar['uid'],'出售币'); //记录资金流水

        $Model->run();
        $redis->exec();

        //匹配成功短信通知
        if(c('c2c_sms')==1){
        $buy_user=(new \app\model\user())->find($buy_ar['uid'],['tel','quhao','id']);
        $sms_S = new \app\service\msms();
        $sms_S->c2c($buy_user['tel'],$buy_user['quhao'],$buy_user['id']);
        }
        return true;
    }


    /* 取消订单 */
    public function cancel($order_ar)
    {
        $this->c2c_order_M->up($order_ar['id'],['status'=>4]);
        $this->c2c_buy_M->up_oid($order_ar['oid_sell'],['status'=>4]);
        $this->c2c_buy_M->up_oid($order_ar['oid_buy'],['status'=>4]);
        $money_S = new \app\service\money();
        $money=$order_ar['money']+$order_ar['fee'];
        $money_S->plus($order_ar['uid_sell'],$money,'coin','coin_c2c',$order_ar['oid_sell'],$order_ar['uid_buy'],'取消订单退回'); //记录资金流水
        return true;
    }

    /* 确认收款 */
    public function carry_out($order_ar)
    {
        $this->c2c_order_M->up($order_ar['id'],['status'=>3,'confirm_time'=>time()]);
        $this->c2c_buy_M->up_oid($order_ar['oid_sell'],['status'=>3]);
        $this->c2c_buy_M->up_oid($order_ar['oid_buy'],['status'=>3]);
        $money_S = new \app\service\money();
        $money_S->plus($order_ar['uid_buy'],$order_ar['money'],'coin','coin_c2c',$order_ar['oid_buy'],$order_ar['uid_sell'],'确认收款'); //记录资金流水
        $this->fee_reward($order_ar);   //手续费极差
        return true;
    }

    public function fee_reward($order_ar){
        $user_M = new \app\model\user();
        $money_S = new \app\service\money();
        $user_gx_M = new \app\model\user_gx();
        $coin = $user_gx_M->lists_tid(['uid'=>$order_ar['uid_sell']]);

        $coin_rating_M = new \app\model\coin_rating();
        $coin_rating_ar=$coin_rating_M->lists();
        $coin_rating_ar=array_column($coin_rating_ar,NULL,'id');
        $trading_fee=0;
        foreach($coin as $vo){
            $reward=0;
            $coin_rating=$user_M->find($vo,'coin_rating');
            $reward=$coin_rating_ar[$coin_rating]['trading_fee'];
            if($reward-$trading_fee>0){
                $money=$order_ar['fee']*$reward/1000;
                $money_S->plus($vo,$money,'coin','coin_c2c_jc',$order_ar['oid_sell'],$order_ar['uid_sell'],'c2c级差奖'); //记录资金流水
                $trading_fee=$trading_fee+$reward;
            }
        }
	}
}