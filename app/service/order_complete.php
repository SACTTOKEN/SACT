<?php

/**
 * Created by yaaaaa__god 
 * User: yaaaaa
 * Date: 2018-12-14 11:19:11
 * Desc: 支付接口
 */

namespace app\service;

use app\model\order as OrderModel;
use app\model\user_attach as user_attachModel;

class order_complete
{
    public $orderM;
    public $user_attach_M;
    public $money_S;
    public $user_gx_M;
    public $rating_M;
    public $user_M;
    public $config_M;
    public function __construct()
    {
        $this->money_S = new \app\service\money();
        $this->orderM = new OrderModel();
        $this->user_attach_M = new user_attachModel();
        $this->user_gx_M = new \app\model\user_gx();
        $this->rating_M = new \app\model\rating();
        $this->config_M = new \app\model\config();
        $this->user_M = new \app\model\user();
    }


    //完成订单
    public function complete($id)
    {
        $order_ar = $this->orderM->find($id);
        if (empty($order_ar)) {
            return "订单不存在";
        }
        if ($order_ar['is_settle'] != 0) {
            return true;
        }
        $order_ar['reward'] = $order_ar['money'] - $order_ar['cost'] - $order_ar['sum_mail'];
        if ($order_ar['reward'] < 0) {
            $order_ar['reward'] = 0;
        }
        $this->orderM->up($id, ['is_settle[+]' => 1, 'settle_time' => time(), 'reward' => $order_ar['reward']]);
        //加业绩
        $yj['buy[+]'] = $order_ar['money'];
        $yj['zcdd[+]'] = 1;
        if ($order_ar['types'] == 1) {
            $yj['buy_vip[+]'] = $order_ar['money'];
            $yj['zcdd_vip[+]'] = 1;
        }
        $this->user_attach_M->up($order_ar['uid'], $yj);

        //加团队业绩
        $user_s = new \app\service\user();
        $user_s->sales($order_ar);
        //升级
        $rating = new \app\service\rating();
        $rating->mall($order_ar['uid']);

        //供货款
        if (res_plugin_is_open('shbxt') and $order_ar['sid']) {
            $this->supply($order_ar);
        }

        //拼团团长面单
        if (res_plugin_is_open('ptgw')) {
            if ($order_ar['types'] == 4) {
                $this->groups($order_ar);
            }
        }

        //返积分
        $this->return_points($order_ar);

        //代理商
        if (res_plugin_is_open('gbfx')) {
            $this->agent_award($order_ar, 4);
            $this->agent_award($order_ar, 1);
            $this->agent_award($order_ar, 2);
            $this->agent_award($order_ar, 3);
        }

        //卡密
        if (res_plugin_is_open('kmcj')) {
            $this->card($order_ar);
        }

        
        //定制结算
        $made = $this->config_M->find('made');
        if ($made) {
            $ctrlfile = MADE . '/' . $made . '/service/order_complete.php';
            if (is_file($ctrlfile)) {
                $cltrlClass = '\made\\' . $made . '\\service\order_complete';
                $made_S = new $cltrlClass();
                $made_res=$made_S->index($order_ar);
                if($made_res==false){
                    return true;
                }
            }
        }

        //五级分销
        if (res_plugin_is_open('tyxfx')) {
            if ($order_ar['reward'] > 0) {
                $this->recommend($order_ar);
            }
        }
        
        //团队奖+平级奖
        if (res_plugin_is_open('sgzfx')) {
            if ($order_ar['reward'] > 0) {
                $this->team_award($order_ar);
            }
        }


        return true;
    }

    //卡密
    public function card($order_ar)
    {
        $order_product_M = new \app\model\order_product();
        $order_product_ar = $order_product_M->lists_all(['oid' => $order_ar['oid'], 'status' => 0], 'pid');
        if ($order_product_ar) {
            $card_M = new \app\model\card();
            foreach ($order_product_ar as $vo) {
                $card_res = array();
                $card_res = $card_M->have(['pid' => $vo, 'status' => 0], ['id', 'key']);
                if ($card_res) {
                    $order_product_M->up($vo, ['card' => $card_res['key']]);
                    $card_M->up($card_res['id'], ['status' => 1, 'uid' => $order_ar['uid'], 'oid' => $order_ar['oid'], 'open_time' => time()]);
                    mb_sms('card', $card_res['id']);
                }
            }
        }
    }



    //团长面单
    public function groups($order_ar)
    {
        $where['status'] = 1;
        $where['group_face'] = 1;
        $where['is_pay'] = 1;
        $where['AND'] = ['head_oid[=]oid'];
        $groups_ar = (new \app\model\groups())->have($where);
        if ($groups_ar) {
            $money = $order_ar['money'] - $order_ar['sum_mail'];
            if ($money > 0) {
                $this->money_S->plus($order_ar['uid'], $money, 'money', 'group_face', $order_ar['oid'], $order_ar['uid'], '', 'sum_money'); //记录资金流水
            }
        }
    }

    //供货款
    public function supply($order_ar)
    {
        $user = (new \app\model\user_attach())->find($order_ar['sid']);
        if (empty($user)) {
            return;
        }
        $shop_referrer = $order_ar['cost'] * $user['shop_referrer'] / 1000;
        if ($shop_referrer > 0) {
            $tid = $this->user_M->find($order_ar['sid'], 'tid');
            if ($tid) {
                $this->money_S->plus($tid, $shop_referrer, 'amount', 'shop_referrer', $order_ar['oid'], $order_ar['sid'], '', 'sum_amount'); //记录资金流水
            }
        }
        $reward = $order_ar['cost'] - $order_ar['cost'] * $user['shop_fee'] / 1000 - $shop_referrer;
        if ($reward > 0) {
            $this->money_S->plus($order_ar['sid'], $reward, 'supply', 'supply_reward', $order_ar['oid'], $order_ar['uid'], '', 'goods_money'); //记录资金流水
        }
    }

    //返积分
    public function return_points($order_ar)
    {
        if ($order_ar['send_score'] > 0) {
            $this->money_S->plus($order_ar['uid'], $order_ar['send_score'], 'integral', 'send_score', $order_ar['oid'], $order_ar['uid'], '', 'sum_integral'); //记录资金流水
        }
    }


    //五级分销
    public function recommend($order_ar)
    {
        $y_where['uid'] = $order_ar['uid'];
        $y_where['level[<=]'] = 5;
        $y_user_gx_ar = $this->user_gx_M->lists_plus($y_where);
        foreach ($y_user_gx_ar as $vo) {
            $t_rating = $this->user_M->find($vo['tid'], 'rating');
            $level = 'level_' . $vo['level'];
            $bonus_ratio = $this->rating_M->find($t_rating, [$level, 'level_account']);
            $reward = $order_ar['reward'] * $bonus_ratio[$level] / 1000;
            if ($reward > 0) {
                $sum = '';
                $mark = '';
                if ($bonus_ratio['level_account'] == 'integral') {
                    $sum = 'sum_integral';
                } elseif ($bonus_ratio['level_account'] == 'amount') {
                    $sum = 'sum_amount';
                }
                if ($vo['level'] == 1) {
                    $mark = '直推奖';
                }
                $this->money_S->plus($vo['tid'], $reward, $bonus_ratio['level_account'], 'recommend', $order_ar['oid'], $order_ar['uid'], $mark, $sum); //记录资金流水
            }
        }
    }


    //团队奖+平级奖
    public function team_award($order_ar)
    {
        $y_where['uid'] = $order_ar['uid'];
        $y_user_gx_ar = $this->user_gx_M->lists_plus($y_where);
        $team = 0;
        foreach ($y_user_gx_ar as $vo) {
            $t_rating = $this->user_M->find($vo['tid'], 'rating');
            $bonus_ratio = $this->rating_M->find($t_rating, ['team', 'team_same', 'team_account']);

            if ($bonus_ratio['team'] > $team) {
                $reward = $order_ar['reward'] * ($bonus_ratio['team'] - $team) / 1000;
                if ($reward > 0) {
                    $sum = '';
                    if ($bonus_ratio['team_account'] == 'integral') {
                        $sum = 'sum_integral';
                    } elseif ($bonus_ratio['team_account'] == 'amount') {
                        $sum = 'sum_amount';
                    }
                    $this->money_S->plus($vo['tid'], $reward, $bonus_ratio['team_account'], 'team_award', $order_ar['oid'], $order_ar['uid'], '', $sum); //记录资金流水

                    //发放平级奖
                    if ($bonus_ratio['team_same'] > 0) {
                        $this->team_same($order_ar, $vo['tid'], $t_rating, $bonus_ratio['team_same'], $bonus_ratio['team_account']);
                    }
                    $team = $team + ($bonus_ratio['team'] - $team);
                }
            }
            $is_max = $this->rating_M->is_have(['id[>]' => $t_rating]);
            if (!$is_max) {
                return;
            }
        }
    }

    //平级奖
    public function team_same($order_ar, $tid, $t_rating, $team_same, $team_account)
    {
        $tid = $this->user_M->find($tid, 'tid');
        if (empty($tid)) {
            return;
        }
        $where['rating'] = $t_rating;
        $where['id'] = $tid;
        $users = $this->user_M->have($where, 'id');
        if ($users) {
            $reward = $order_ar['reward'] * $team_same / 1000;
            if ($reward > 0) {
                $sum = '';
                if ($team_account == 'integral') {
                    $sum = 'sum_integral';
                } elseif ($team_account == 'amount') {
                    $sum = 'sum_amount';
                }
                $this->money_S->plus($users, $reward, $team_account, 'team_same', $order_ar['oid'], $order_ar['uid'], '', $sum); //记录资金流水
            }
        }
    }


    //代理商
    public function agent_award($order_ar, $types)
    {
        switch ($types) {
            case 1:
                $where['agent_area'] = $order_ar['mail_area'];
                $where['agent_city'] = $order_ar['mail_city'];
                $where['agent_province'] = $order_ar['mail_province'];
                $where['is_agent'] = 1;
                $agent_area = $this->config_M->find('agent_area');
                $reward = $order_ar['reward'] * $agent_area / 1000;
                break;
            case 2:
                $where['agent_city'] = $order_ar['mail_city'];
                $where['agent_province'] = $order_ar['mail_province'];
                $where['is_agent'] = 2;
                $agent_city = $this->config_M->find('agent_city');
                $reward = $order_ar['reward'] * $agent_city / 1000;
                break;
            case 3:
                $where['agent_province'] = $order_ar['mail_province'];
                $where['is_agent'] = 3;
                $agent_province = $this->config_M->find('agent_province');
                $reward = $order_ar['reward'] * $agent_province / 1000;
                break;
            case 4:
                $where['agent_town'] = $order_ar['mail_town'];
                $where['agent_area'] = $order_ar['mail_area'];
                $where['agent_city'] = $order_ar['mail_city'];
                $where['agent_province'] = $order_ar['mail_province'];
                $where['is_agent'] = 4;
                $agent_town = $this->config_M->find('agent_town');
                $reward = $order_ar['reward'] * $agent_town / 1000;
                break;
            default:
                return;
        }
        if ($reward <= 0) {
            return;
        }
        $user_ar = $this->user_M->lists_all($where, 'id');
        if (empty($user_ar)) {
            return;
        }
        $reward = $reward / count($user_ar);
        $team_account = $this->config_M->find('agent_account');
        if ($team_account == 'integral') {
            $sum = 'sum_integral';
        } elseif ($team_account == 'amount') {
            $sum = 'sum_amount';
        }
        foreach ($user_ar as $vo) {
            $this->money_S->plus($vo, $reward, $team_account, 'agentaward', $order_ar['oid'], $vo, '', $sum); //记录资金流水
        }
    }
}
