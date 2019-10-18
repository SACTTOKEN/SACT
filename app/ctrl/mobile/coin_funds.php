<?php

/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-03-28 09:23:02
 * Desc: IM
 */

namespace app\ctrl\mobile;

use app\model\coin_currency as coin_currency_Model;
use app\model\coin_price as coin_price_Model;
use app\model\money as money_Model;
use app\validate\CoinFundsValidate as FundsValidate;

class coin_funds extends BaseController
{
    public $coin_currency_M;
    public function __initialize()
    {
        $this->coin_currency_M = new coin_currency_Model();
    }

    //首页
    public function index()
    {
        $user = $GLOBALS['user'];
        $coin_price_M = new coin_price_Model();

        $coin_currency_ar = $this->coin_currency_M->lists_all();
        $aicq_price_cny = $coin_price_M->price();
        $funds_ar['coin']['money'] = $user['coin'];
        $funds_ar['coin']['money_cny'] = $user['coin'] * $aicq_price_cny;
        $funds_ar['coin_storage']['money'] = $user['coin_storage'];
        $funds_ar['coin_storage']['money_cny'] = $user['coin_storage'] * $aicq_price_cny;
        foreach ($coin_currency_ar as $vo) {
            $funds_ar[$vo['iden']]['money'] = $user[$vo['iden']];
            $funds_ar[$vo['iden']]['money_aicq'] = $user[$vo['iden']] * $vo['price_cny'] / $aicq_price_cny;
            $funds_ar[$vo['iden']]['money_cny'] = $user[$vo['iden']] * $vo['price_cny'];
        }
        return $funds_ar;
    }

    public function index2()
    {
        $user=$GLOBALS['user'];
        $price=(new \app\model\coin_price())->price();
        $data=[
           
			/*
            [
                'title'=>find_reward_redis('viprd_ptb'),
                'iden'=>'coin',
                'money'=>$user['viprd_ptb'],
                'moeny_its'=>$user['viprd_ptb'],
                'moeny_usdt'=>$user['viprd_ptb']*$price,
            ],
			*/
            [
                'title'=>find_reward_redis('USDT'),
                'iden'=>'USDT',
                'money'=>$user['USDT'],
                'moeny_its'=>$user['USDT']/$price,
                'moeny_usdt'=>$user['USDT'],
            ],
			[
                'title'=>find_reward_redis('coin'),
                'iden'=>'coin',
                'money'=>$user['coin'],
                'moeny_its'=>$user['coin'],
                'moeny_usdt'=>$user['coin']*$price,
            ],
            [
                'title'=>find_reward_redis('viprd_usdt'),
                'iden'=>'viprd_usdt',
                'money'=>$user['viprd_usdt'],
                'moeny_its'=>$user['viprd_usdt']/$price,
                'moeny_usdt'=>$user['viprd_usdt'],
            ],
			[
                'title'=>find_reward_redis('USDT_KY'),
                'iden'=>'USDT_KY',
                'money'=>$user['USDT_KY'],
                'moeny_its'=>$user['USDT_KY']/$price,
                'moeny_usdt'=>$user['USDT_KY'],
            ],
            [
                'title'=>find_reward_redis('mxq_fcsl'),
                'iden'=>'mxq_fcsl',
                'money'=>$user['mxq_fcsl'],
                'moeny_its'=>$user['mxq_fcsl']*0.1/$price,
                'moeny_usdt'=>$user['mxq_fcsl']*0.1,
            ],
			[
                'title'=>find_reward_redis('USDT_storage'),
                'iden'=>'USDT_storage',
                'money'=>$user['USDT_storage'],
                'moeny_its'=>$user['USDT_storage']/$price,
                'moeny_usdt'=>$user['USDT_storage'],
            ],
			[
                'title'=>find_reward_redis('coin_storage'),
                'iden'=>'coin_storage',
                'money'=>$user['coin_storage'],
                'moeny_its'=>$user['coin_storage']/$price,
                'moeny_usdt'=>$user['coin_storage'],
            ],
            	[
                'title'=>find_reward_redis('sactloop'),
                'iden'=>'sactloop',
                'money'=>$user['sactloop'],
                'moeny_its'=>$user['sactloop']/$price,
                'moeny_usdt'=>$user['sactloop'],
            ],
        ];
        return $data;
    }


    //流水
    public function running_water()
    {
        (new \app\validate\PageValidate())->goCheck();
        (new FundsValidate())->goCheck('water');
        $user = $GLOBALS['user'];
        $iden = post("iden");
        $cate = post("cate");
        $page = post("page", 1);
        $page_size = post("page_size", 10);
        if ($iden == "coin" || $iden == "viprd_ptb") {
            $coin_price_M = new coin_price_Model();
            $aicq_price_cny = $coin_price_M->price();
            $funds_ar['coin']['money'] = $user['coin'];
            $funds_ar['coin_storage']['money'] = $user['viprd_ptb'];
            $funds_ar['coin']['money_cny'] = $user['viprd_ptb'] * $aicq_price_cny + $user['coin'] * $aicq_price_cny;
            $iden = ['coin', 'viprd_ptb'];
            $types = 0;
        } else {
            $coin_price_M = new coin_price_Model();
            $coin_currency_ar = $this->coin_currency_M->find($iden);
            $aicq_price_cny = $coin_price_M->price();
            empty($coin_currency_ar) && error('币种不存在', 10007);
            $funds_ar['money'] = $user[$iden];
            $funds_ar['title'] = $coin_currency_ar['title'];
            //empty($funds_ar['money']) && error('币种不存在',10007);
            $funds_ar['money_aicq'] = $user[$iden] * $coin_currency_ar['price_cny'] / $aicq_price_cny;
            $funds_ar['money_cny'] = $user[$iden] * $coin_currency_ar['price_cny'];
            $types = 1;
        }
        $money_M = new money_Model();
        $where['cate'] = $iden;
        if($cate){
        $where['iden'] = $cate;
        }
        $water = $money_M->lists_one($user['id'], $page, $page_size, $where);

        foreach ($water as &$vo) {
			/*
            if ($vo['cate'] == "coin") {
                $vo['cate'] = "活动钱包";
            } else if ($vo['cate'] == "viprd_ptb") {
                $vo['cate'] = "存储钱包";
            } else {
                $vo['cate'] = find_reward_redis($iden) . "钱包";
				
            }
			*/
			$vo['cate'] = find_reward_redis($iden);
            if($vo['types']==2){
                $vo['money']='-'.$vo['money'];
            }
        }
        $data['types'] = $types;
        $data['funds'] = $funds_ar;

        $where['types']=1;
        $where['uid']=$user['id'];
        $data['count']=$money_M->all_find_sum('money',$where);
        $data['water']['data'] = $water;
        $data['reward']=(new \app\model\reward())->reward_lists_all(['show'=>1,'types'=>0,'id[>]'=>77,'ORDER'=>['sort'=>'DESC']],['id','title','iden']);
        return $data;
    }

    public function recharge()
    {
        $user = $GLOBALS['user'];
        (new FundsValidate())->goCheck('water');
        $iden = post("iden");
        $coin_currency_ar = $this->coin_currency_M->find($iden);
        empty($coin_currency_ar) && error('币种不存在', 10007);
        $data['uid'] = $user['id'];
        $data['cate'] = $iden;
        $recharge_ar = (new \app\model\coin_recharge())->save_by_oid($data);
        empty($recharge_ar) && error('添加失败', 10006);

        $url = 'http://cryptopay.icaipay.net/cn/cryptopay?crypto=' . $iden . '&amount=0&app_id=' . c('coin_appid') . '&number=' . $recharge_ar['oid'] . '&notify_url=http://' . cc('web_config', 'api') . '/mobile/common/coin_pay';
        return $url;
    }

    public function recharge_lists()
    {
        $user = $GLOBALS['user'];
        (new \app\validate\PageValidate())->goCheck();
        $page = post("page", 1);
        $page_size = post("page_size", 10);
        $coin_recharge_M = new \app\model\coin_recharge();
        $where['uid'] = $user['id'];
        $ar = $coin_recharge_M->lists($page, $page_size, $where);
        foreach ($ar as &$vo) {
            $vo['title'] = $vo['cate'] . "充币";
            if ($vo['status'] == 0) {
                $vo['status'] = "未支付";
            } else {
                $vo['status'] = "支付成功";
            }
        }

        $data['data'] = $ar;
        return $data;
    }

    public function withdraw()
    {
        $user = $GLOBALS['user'];

        $uwhere['id']=$user['id'];
        $uwhere['pay_password']='';
        $user_ar=(new \app\model\user())->is_find($uwhere);
      
		
        $data['fee'] = c("coin_withdraw_fee") / 10;
        $data['coin'] = $this->coin_currency_M->lists_all(['iden'=>'USDT_KY']);
        foreach ($data['coin'] as &$vo) {
            $vo['money'] = $user[$vo['iden']];
        }
        $data['arrival']=[
            '第三方钱包',
            '星际钱包',
        ];
        return $data;
    }

    public function withdraw_lists()
    {
        $user = $GLOBALS['user'];
        (new \app\validate\PageValidate())->goCheck();
        $page = post("page", 1);
        $page_size = post("page_size", 10);
        $coin_withdraw_M = new \app\model\coin_withdraw();
        $where['uid'] = $user['id'];
        $ar = $coin_withdraw_M->lists($page, $page_size, $where);
        foreach ($ar as &$vo) {
            $vo['title'] = $vo['cate'] . "提币";
            if ($vo['status'] == 0) {
                $vo['status'] = "申请中";
            } elseif ($vo['status'] == 2) {
                $vo['status'] = "提币失败";
            }else {
                $vo['status'] = "提币成功";
            }
        }

        $data['data'] = $ar;
        return $data;
    }

    /*提交提币*/
    public function withdraw_add()
    {
        empty(c('withdraw_coin')) && error('敬请期待', 400);
        $user = $GLOBALS['user'];
        (new FundsValidate())->goCheck('withdraw');
        $iden = 'USDT_KY';
        $money = post("money");
        $types = post("types");
        $add = post("add");
        $arrival=post("arrival");
        $coin_currency_ar = $this->coin_currency_M->find($iden);
        empty($coin_currency_ar) && error('币种不存在', 400);
/* 
        $password=post("password");
        $password = rsa_decrypt($password);
        $password = md5($password.'inex10086');
        $auth = (new \app\model\user())->check_user_pay($user['username'],$password);
        empty($auth['id']) && error("密码错误",400); */

        $coin_withdraw_M = new \app\model\coin_withdraw();
        $where['uid'] = $user['id'];
        $where['status'] = 0;
        $coin_withdraw_ar = $coin_withdraw_M->is_have($where);
        if ($coin_withdraw_ar) {
            error('提币申请中，等待提币通过才能再申请', 400);
        }
        if (c('coin_withdraw_min')) {
            if ($money < c('coin_withdraw_min')) {
                error('最低提币金额' . c('coin_withdraw_min'), 404);
            }
        }

        //判断金额
        $user_M = new \app\model\user();
        $coin = $user_M->find($user['uid'], $iden);
        $fee = $money * c("coin_withdraw_fee") / 1000;
        if ($money - $coin > 0) {
            error('金额不足1', 10003);
        }

        flash_god($user['id']);

        $redis = new \core\lib\redis();
        $Model = new \core\lib\Model();
        $Model->action();
        $redis->multi();

        $data['fee'] = $fee;
        $data['recharge_money'] = $money;
        $data['types'] = $types;
        $data['add'] = $add;
        $data['uid'] = $user['id'];
        if($arrival==1){
        	$data['arrival']='星际钱包';
        }else{
        	$data['arrival']='第三方钱包';
        }
        if($arrival==1){
            $data['status']=1;
        }
        $data['cate'] = $iden;
        $recharge_ar = $coin_withdraw_M->save_by_oid($data);
        empty($recharge_ar) && error('添加失败', 10006);

        $money_S = new \app\service\money();
        $money = $recharge_ar['recharge_money'];
        if($arrival==1){
            $money_S->plus($user['uid'], $fee, 'LMJJ', 'coin_withdraw', $recharge_ar['oid'], $user['uid'], '提币手续费到联盟基金'); //记录资金流水
            $money_S->plus($user['uid'], $money-$fee, 'USDT', 'coin_withdraw', $recharge_ar['oid'], $user['uid'], '提币到星际钱包'); //记录资金流水
            $money_S->minus($user['uid'], $money, $iden, 'coin_withdraw', $recharge_ar['oid'], $user['uid'], '申请提币到星际钱包'); //记录资金流水
        }else{
            $money_S->minus($user['uid'], $money, $iden, 'coin_withdraw', $recharge_ar['oid'], $user['uid'], '申请提币到第三方钱包'); //记录资金流水
        }

        $Model->run();
        $redis->exec();
        return "申请成功，等待审核";
    }

    public function exchange()
    {
        $user = $GLOBALS['user'];

        $uwhere['id']=$user['id'];
        $uwhere['pay_password']='';
        $user_ar=(new \app\model\user())->is_find($uwhere);
      

        $data['fee'] = c("exchange_fee") / 10;

        $balance = c('exchange_coin_account');
        $balance = explode("|", $balance);
        foreach ($balance as $vo) {
            if ($vo) {
                $data['balance'][$vo]['title'] = find_reward_redis($vo);
                $data['balance'][$vo]['iden'] = $vo;
                $data['balance'][$vo]['money'] = $user[$vo];
            }
        }
        sort($data['balance']);

        $data['coin'][0]['iden'] = 'ETH';
        $data['coin'][0]['money'] = $user['ETH'];
        
        return $data;
    }

    /*提交兑币*/
    public function exchange_add()
    {
        empty(c('exchange_coin')) && error('敬请期待', 400);
        $user = $GLOBALS['user'];
        (new FundsValidate())->goCheck('exchange');
        $types = post('types');
        $iden = post('iden');
        $iden_CN=find_reward_redis($iden);
        $money = post('money');
        if (!($types == 1 || $types == 2)) {
            error('类型错误', 400);
        }

    /*     $password=post("password");
        $password = rsa_decrypt($password);
        $password = md5($password.'inex10086');
        $auth = (new \app\model\user())->check_user_pay($user['username'],$password);
        empty($auth['id']) && error("密码错误",400);
         */
        $coin_exchange_M = new \app\model\coin_exchange();
        if($types==2){
            $exchange_day_sum=c('exchange_day_sum');
            if($exchange_day_sum>0){
                $sum=$coin_exchange_M->find_sum('money',['types'=>2]);
                if($sum+$money>$exchange_day_sum){
                    error('已达单日兑换总额', 400);
                }
            }
        }
        $balance = c('exchange_coin_account');
        $balance = explode("|", $balance);
        $idens='';
        foreach($balance as $vo){
            if($vo){
                if($vo==$iden){
                    $idens=$iden;
                }
            }
        }
        empty($idens) && error('账户类型不支持',400);
        
        $coin_currency_ar = $this->coin_currency_M->find('ETH');
        //if ($iden == 'coin' || $iden == 'coin_storage') {
		if ($iden == 'coin' || $iden == 'coin_storage') {	
            $coin_price_M = new coin_price_Model();
            $price = $coin_price_M->price();
        } elseif ($iden == 'integral') {
            $price = 0.1;
        } else {
            $price = 1;
        }
        //判断金额
        if ($types == 1) {
            //法币到币币
            $user_M = new \app\model\user();
            $coin = $user_M->find($user['uid'], $iden);
            $fee = $money * c("exchange_fee") / 1000;
            if ($money - $coin > 0) {
                error('金额不足1', 10003);
            }
            $actual = $money - $fee;
            $actual = $actual * $price / $coin_currency_ar['price_cny'];
        } else {
            //币币到法币
            $user_M = new \app\model\user();
            $coin = $user_M->find($user['uid'], 'ETH');
            $fee = 0;
            if ($money - $coin > 0) {
                error('金额不足1', 10003);
            }
            $actual = $money - $fee;
            $actual = $actual * $coin_currency_ar['price_cny'] / $price;
        }

        flash_god($user['id']);

        $redis = new \core\lib\redis();
        $Model = new \core\lib\Model();
        $Model->action();
        $redis->multi();

        $data['fee'] = $fee;
        $data['money'] = $money;
        $data['actual'] = $actual;
        $data['types'] = $types;
        $data['status'] = 1;
        $data['uid'] = $user['id'];
        $data['cate'] = $iden;
        $exchange_ar = $coin_exchange_M->save_by_oid($data);
        empty($exchange_ar) && error('添加失败', 10006);

        if ($types == 1) {
            $money_S = new \app\service\money();
            $money_S->minus($user['uid'], $data['money'], $iden, 'coin_exchange', $exchange_ar['oid'], $user['uid'], $iden_CN.'转ETH'); //记录资金流水
            $money_S->plus($user['uid'], $data['actual'], 'ETH', 'coin_exchange', $exchange_ar['oid'], $user['uid'], $iden_CN.'转ETH'); //记录资金流水
        } else {
            $money_S = new \app\service\money();
            $money_S->minus($user['uid'], $data['money'], 'ETH', 'coin_exchange', $exchange_ar['oid'], $user['uid'], 'ETH转'.$iden_CN); //记录资金流水
            $money_S->plus($user['uid'], $data['actual'], $iden, 'coin_exchange', $exchange_ar['oid'], $user['uid'], 'ETH转'.$iden_CN); //记录资金流水
        }

        $Model->run();
        $redis->exec();
        return "划转成功";
    }


    public function exchange_lists()
    {
        $user = $GLOBALS['user'];
        (new \app\validate\PageValidate())->goCheck();
        $page = post("page", 1);
        $page_size = post("page_size", 10);
        $coin_exchange_M = new \app\model\coin_exchange();
        $where['uid'] = $user['id'];
        $ar = $coin_exchange_M->lists($page, $page_size, $where);
        foreach ($ar as &$vo) {
            if ($vo['types'] == 1) {
                $vo['title'] = find_reward_redis($vo['cate']).'转ETH';
            } else {
                $vo['title'] = 'ETH转'.find_reward_redis($vo['cate']);
            }
            if ($vo['status'] == 0) {
                $vo['status'] = "划转中";
            } else {
                $vo['status'] = "划转成功";
            }
        }

        $data['data'] = $ar;
        return $data;
    }
}
