<?php

/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-15 17:02:46
 * Desc: 大盘交易
 */

namespace app\ctrl\mobile;

use app\validate\IDMustBeRequire;
use app\validate\TransactionValidate;

class transaction extends PublicController
{
    public $transaction_M;
    public $transaction_order_M;
    public $money_S;
    public function __initialize()
    {
        $this->transaction_M = new \app\model\transaction();
        $this->transaction_order_M = new \app\model\transaction_order();
        $this->money_S = new \app\service\money();
    }

    //当前价格
    public function index()
    {
        $user = (new \app\model\user())->find($GLOBALS['user']['id'], ['USDT', 'coin']);
        $data['my_usdt'] = $user['USDT'];
        $data['my_coin'] = $user['coin'];
        
        $data['now_price'] = (new \app\model\coin_price())->price();
        $usdt_price = (new \app\model\coin_currency())->have(['iden' => 'USDT'], 'price_cny');
        $data['now_price_cny'] = $data['now_price'] * $usdt_price;
        
        $data['usdt_price'] = $usdt_price;
        return $data;
    }

    public function price()
    {
        $data['now_price'] = (new \app\model\coin_price())->price();
        $usdt_price = (new \app\model\coin_currency())->have(['iden' => 'USDT'], 'price_cny');
        $data['now_price_cny'] = $data['now_price'] * $usdt_price;
        $data['min'] = $this->transaction_M::$medoo->query('select price,sum(sy_number) as sy_number from transaction where status=0 and price<' . $data['now_price'] . ' group by price order by price ASC limit 0,5')->fetchAll(\PDO::FETCH_ASSOC);
        $data['max'] = $this->transaction_M::$medoo->query('select price,sum(sy_number) as sy_number from transaction where status=0 and price>' . $data['now_price'] . ' group by price order by price DESC limit 0,5')->fetchAll(\PDO::FETCH_ASSOC);
        return $data;
    }

    //买入
    public function buy()
    {
        $user = $GLOBALS['user'];
        $price = post('price');
        $number = post('number');
		$buylx = post('buylx');
        $iden = 'PTB';
        (new TransactionValidate())->goCheck('buy');
        
        //判断价格
        $now_price = (new \app\model\coin_price())->price();
        $min_price = $now_price - $now_price * c('transation_price') / 1000;
        $max_price = $now_price + $now_price * c('transation_price') / 1000;
        if ($price < $min_price || $price > $max_price) {
            error('价格浮动' . (c('transation_price') / 10) . '%', 404);
        }
        $USDT = (new \app\model\user())->find($user['id'], 'USDT');
        $money = $price * $number;
        if ($USDT < $money) {
            error('USDT金额不足', 404);
        }

        flash_god($user['id']);
        $redis = new \core\lib\redis();
        $Model = new \core\lib\Model();
        $Model->action();
        $redis->multi();
        $data['uid'] = $user['id'];
        $data['iden'] = $iden;
        $data['types'] = 1;
        $data['number'] = $number;
        $data['sy_number'] = $number;
        $data['price'] = $price;
		$data['buylx'] = $buylx;
        $res = $this->transaction_M->save_by_oid($data);
        empty($res) && error('买入失败', 10006);

        //减金额
        $this->money_S->minus($user['id'], $money, 'USDT', 'transaction', $res['oid'], $user['id'], '大盘交易买入');
        //运行交易
        (new \app\service\transaction())->buy($res);

        $Model->run();
        $redis->exec();
        return "买入成功";
    }

    //卖出
    public function sell()
    {
        $user = $GLOBALS['user'];
        $price = post('price');
        $number = post('number');
        $iden = 'PTB';
		//$buylx = post('buylx');
        (new TransactionValidate())->goCheck('sell');
        //判断价格
        $now_price = (new \app\model\coin_price())->price();
        $min_price = $now_price - $now_price * c('transation_price') / 1000;
        $max_price = $now_price + $now_price * c('transation_price') / 1000;
        if ($price < $min_price || $price > $max_price) {
            error('价格浮动' . (c('transation_price') / 10) . '%', 404);
        }
        $coin = (new \app\model\user())->find($user['id'], 'coin');
        if ($coin < $number) {
            error(c('coin_title') . '金额不足', 404);
        }

        flash_god($user['id']);
        $redis = new \core\lib\redis();
        $Model = new \core\lib\Model();
        $Model->action();
        $redis->multi();
        $data['uid'] = $user['id'];
        $data['iden'] = $iden;
        $data['types'] = 2;
        $data['number'] = $number;
        $data['sy_number'] = $number;
        $data['price'] = $price;
		//$data['buylx'] = $buylx;
        $res = $this->transaction_M->save_by_oid($data);
        empty($res) && error('卖出失败', 10006);

        //减金额
        $this->money_S->minus($user['id'], $number, 'coin', 'transaction', $res['oid'], $user['id'], '大盘交易卖出');
        //运行交易
        (new \app\service\transaction())->sell($res);
//cs($Model->log(),1);
        $Model->run();
        $redis->exec();
        return "卖出成功";
    }

    //最近成交
    public function recent_transaction()
    {
        $iden = 'PTB';
        $data = $this->transaction_order_M->lists_all(['iden' => $iden, 'ORDER' => ['id' => 'DESC'], 'LIMIT' => [0, 25]], ['created_time', 'number', 'buy_price']);
        return $data;
    }



    //我的订单
    public function my_order()
    {
        $iden = 'PTB';
        $page = post("page", 1);
        $page_size = post("page_size", 10);
        $order = ['id' => 'DESC'];
        $where['iden'] = $iden;
        $where['status'] = 0;
        $where['uid'] = $GLOBALS['user']['id'];
        $data = $this->transaction_M->lists_sort($page, $page_size, $where, $order);
        foreach ($data as &$vo) {
            if ($vo['iden'] == 'PTB') {
                $vo['iden'] = c('coin_title');
            }
        }
        return $data;
    }


    //撤单
    public function withdrawal_order()
    {
        $id = post('id');
        (new IDMustBeRequire())->goCheck();
        $where['uid'] = $GLOBALS['user']['id'];
        $where['status'] = 0;
        $where['id'] = $id;
        $where['sy_number[>]'] = 0;
        $data = $this->transaction_M->have($where);
        empty($data) && error('订单已完成', 404);
        $save_data['sy_number'] = 0;
        $save_data['status'] = 1;
        $save_data['cd_number'] = $data['sy_number'];
        $res = $this->transaction_M->up($id, $save_data);
        empty($res) && error('撤单失败', 10006);
        if ($data['types'] == 1) {
            $this->money_S->plus($GLOBALS['user']['id'], $data['sy_number'] * $data['price'], 'USDT', 'transaction', $data['oid'], $GLOBALS['user']['id'], '大盘交易买入撤单');
        } else {
            $this->money_S->plus($GLOBALS['user']['id'], $data['sy_number'], 'coin', 'transaction', $data['oid'], $GLOBALS['user']['id'], '大盘交易卖出撤单');
        }
        return '撤单成功';
    }


    //已完成
    public function order_lists()
    {
        $iden = 'PTB';
        $page = post("page", 1);
        $page_size = post("page_size", 10);
        $order = ['id' => 'DESC'];
        $where['iden'] = $iden;
        $where['OR'] = [
            'buy_uid' => $GLOBALS['user']['id'],
            'sell_uid' => $GLOBALS['user']['id'],
        ];
        $data = $this->transaction_order_M->lists_sort($page, $page_size, $where, $order);
        foreach ($data as &$vo) {
            if ($vo['buy_uid'] == $GLOBALS['user']['id']) {
                $buy_user = user_info($vo['sell_uid']);
            } else {
                $buy_user = user_info($vo['buy_uid']);
            }
            $vo['username'] = $buy_user['nickname'] ? $buy_user['nickname'] : $buy_user['username'];
            $vo['avatar'] = $buy_user['avatar'];

            if ($vo['iden'] == 'PTB') {
                $vo['iden'] = c('coin_title');
            }
        }
        return $data;
    }
}
