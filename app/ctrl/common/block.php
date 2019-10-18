<?php

/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-14 11:38:07
 * Desc: 虚拟币余额查询
 */

namespace app\ctrl\common;

class block
{

    public $block_wallet_M;
    public $ETHBances;
    public $ETHSend;
    public $block_recharge_M;
    public $money_S;
    public $config_M;
    public $block_freed_M;
    public $block_withdraw_M;
    public function __construct()
    {
		set_time_limit(0);
        $this->block_wallet_M = new \app\model\block_wallet();
        $this->block_recharge_M = new \app\model\block_recharge();
        $this->ETHBances = new \extend\block\ETHBances();
        $this->money_S = new \app\service\money();
        $this->config_M = new \app\model\config();
        $this->block_freed_M = new \app\model\block_freed();
        $this->block_withdraw_M = new \app\model\block_withdraw();
    }

    public function cs()
    {
        //return $this->ETHBances->index('0xc2b76698974f63969d972a710c552a54f82e7ba2');
        return $this->ETHBances->index('0xa568e69663f9beb18ce7eb93e931203966b0819d');
    }

    public function index()
    {
        //$redis = new \core\lib\redis();
        //$Model = new \core\lib\Model();
        //$Model->action();
        //$redis->multi();
        $this->block_recharge();    //查询充值
        $this->block_freed();    //释放
        //cs($this->config_M->log());
        //$Model->run();
        //$redis->exec();
    }

    public function block_recharge()
    {
        $where['status'] = 1;
        $where['carried_time[<]'] = time();
        $where['ORDER'] = ['carried_time' => 'ASC'];
        $where['LIMIT'] = [0, 1];
        $wallet_ar = $this->block_wallet_M->lists_all($where);
        foreach ($wallet_ar as $vo) {
            flash_god($vo['uid']);
            $data = array();
            $data_recharge = array();
            $data['carried_time'] =time()+10;
            $money = $this->ETHBances->index($vo['publickey']);
            if ($money > 0) {
                $data['account_money'] = $money;
            } else {
                $data['account_money'] = $vo['account_money'];
            }
            $recharge_money = $data['account_money'] - $vo['distribution_money'];
            if ($recharge_money > 0) {
                $data_recharge['publickey'] = $vo['publickey'];
                $data_recharge['uid'] = $vo['uid'];
                $data_recharge['cate'] = $vo['cate'];
                $data_recharge['money'] = $recharge_money;
                $data_recharge['distribution_money'] = $vo['distribution_money'];
                $data_recharge['account_money'] = $data['account_money'];
                $recharge_res = $this->block_recharge_M->save_by_oid($data_recharge);
                $this->money_S->plus($recharge_res['uid'], $recharge_money, $recharge_res['cate'], 'block_recharge', $recharge_res['oid'], $recharge_res['uid']); //记录资金流水
                $data['distribution_money'] = $data['account_money'];
                $data['carried_time[+]'] = $vo['freed_time']+1000;
            }
            $this->block_wallet_M->up($vo['id'], $data);
        }
    }

    public function block_freed()
    {
        $where['status'] = 1;
        $where['freed_time[<]'] = time();
        $where['ORDER'] = ['freed_time' => 'ASC'];
        $where['LIMIT'] = [0, 1];
        $wallet_ar = $this->block_wallet_M->lists_all($where);
        foreach ($wallet_ar as $vo) {
            flash_god($vo['uid']);
            $data = array();
            $data_freed = array();
            $data_recharge = array();
            $data['carried_time'] = time()+20;
            $money = $this->ETHBances->index($vo['publickey']);
            if ($money > 0) {
                $data['account_money'] = $money;
            } else {
                $data['account_money'] = $vo['account_money'];
            }
            $recharge_money = $data['account_money'] - $vo['distribution_money'];
            if ($recharge_money > 0) {
                $data_recharge['publickey'] = $vo['publickey'];
                $data_recharge['uid'] = $vo['uid'];
                $data_recharge['cate'] = $vo['cate'];
                $data_recharge['money'] = $recharge_money;
                $data_recharge['distribution_money'] = $vo['distribution_money'];
                $data_recharge['account_money'] = $data['account_money'];
                $recharge_res = $this->block_recharge_M->save_by_oid($data_recharge);
                $this->money_S->plus($recharge_res['uid'], $recharge_money, $recharge_res['cate'], 'block_recharge', $recharge_res['oid'], $recharge_res['uid']); //记录资金流水
                $data['distribution_money'] = $data['account_money'];
            }
            $data_freed['publickey'] = $vo['publickey'];
            $data_freed['uid'] = $vo['uid'];
            $data_freed['distribution_money'] = $data['account_money'];
            $data_freed['account_money'] = $data['account_money'];
            $this->block_freed_M->save($data_freed);
            $data['status'] = 0;
            $data['uid'] = 0;
            $data['freed_money'] = $data['account_money'];
            $qbkx_fpsj = $this->config_M->find('qbkx_fpsj', 'value');
            $data['assignable_time'] = time() + $qbkx_fpsj;
            $this->block_wallet_M->up($vo['id'], $data);
        }
    }


    public function block_withdraw()
    {
        $qbfp_zd = $this->config_M->find('qbfp_zd', 'value');
        $qbzdz_blye = $this->config_M->find('qbzdz_blye', 'value');
        $transferkey = $this->config_M->find('transferkey', 'value');
        if (empty($transferkey)) {
            return;
        }
        if ($qbfp_zd <= 0) {
            return;
        }
        $where['status'] = 0;
        $where['account_money[>=]'] = $qbfp_zd;
        $where['LIMIT'] = [0, 1];
        $wallet_ar = $this->block_wallet_M->lists_all($where);
      
        foreach ($wallet_ar as $vo) {
            $withdraw_ar = array();
            $withdraw_data = array();
            $money = $this->ETHBances->index($vo['publickey']);
            $withdraw_money = $money - $qbzdz_blye;
           
            if ($withdraw_money > 0) {
                $withdraw_ar['publickey'] = $vo['publickey'];
                $withdraw_ar['privatekey'] = $vo['privatekey'];
                $withdraw_ar['transferkey'] = $transferkey;
                $withdraw_ar['money'] = $withdraw_money;
                //cs($withdraw_ar);
                $this->ETHSend = new \extend\block\ETHSend();
                $send_res = $this->ETHSend->index($withdraw_ar);
                $withdraw_data['publickey'] = $vo['publickey'];
                $withdraw_data['cate'] = $vo['cate'];
                $withdraw_data['distribution_money'] = $vo['distribution_money'];
                $withdraw_data['account_money'] = $money;
                $withdraw_data['hash'] = $send_res;
                $withdraw_data['transferkey'] = $transferkey;
                $withdraw_data['money'] = $withdraw_money;
                $this->block_withdraw_M->save_by_oid($withdraw_data);
                $this->block_wallet_M->up($vo['id'], ['account_money' => $qbzdz_blye, 'distribution_money' => $qbzdz_blye]);
            }
        }
        //cs($this->block_wallet_M->log());
    }

    
}
