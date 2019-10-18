<?php

/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-03-28 09:23:02
 * Desc: IM
 */

namespace app\ctrl\mobile;

use app\model\coin_price as coin_price_Model;
use app\model\c2c_buy as c2c_buy_Model;
use app\model\c2c_order as c2c_order_Model;
use app\validate\IDMustBeRequire;
use app\validate\C2cValidate;

class c2c extends BaseController
{

    public $coin_price_M;
    public $c2c_buy_M;
    public $c2c_order_M;


    public function __initialize()
    {
        $this->coin_price_M = new coin_price_Model();
        $this->c2c_buy_M = new c2c_buy_Model();
        $this->c2c_order_M = new c2c_order_Model();
    }

    //首页
    public function index()
    {
        $user = $GLOBALS['user'];
        $data['price'] = $this->coin_price_M->price();
        $data['c2c_lowest'] = c("c2c_lowest");
        if ($user['coin_buy'] >= 100) {
            $c2c_highest = c("c2c_highest2");
        } else {
            $c2c_highest = c('c2c_highest');
        }
        $data['c2c_highest'] = $c2c_highest;
        return $data;
    }

    //交易市场
    public function lists()
    {
        (new \app\validate\AllsearchValidate())->goCheck();
        $page = post("page", 1);
        $types = post("types", 1);
        $page_size = post("page_size", 10);
        $username = post("username");
        $where['status'] = 1;
        if ($username != "") {
            $user_where['OR'] = [
                'username[~]' => $username,
                'tel[~]' => $username,
                'nickname[~]' => $username
            ];
            $user_ar = (new \app\model\user())->list_where($user_where);
            empty($user_ar) && $where['id'] = 0;
            $where['uid'] = $user_ar;
        } else {
            $where['types'] = $types;
        }
        $data = $this->c2c_buy_M->lists($page, $page_size, $where);
        $price = $this->coin_price_M->price();
        if ($price <= 0) {
            error('价格错误', 400);
        }
        foreach ($data as &$vo) {
            $user = user_info($vo['uid']);
            $vo['avatar'] = $user['avatar'];
            $vo['nickname'] = $user['nickname'];
            $vo['integrity'] = $user['integrity'];
            if (!$vo['nickname']) {
                $vo['nickname'] = $user['username'];
            }
            $vo['price'] = $price;
        }

        $res['data'] = $data;
        return $res;
    }

    //交易订单
    public function order()
    {
        (new \app\validate\AllsearchValidate())->goCheck();
        $user = $GLOBALS['user'];
        $where['OR'] = [
            "uid_buy" => $user['id'],
            "uid_sell" => $user['id']
        ];
        if ($user['designation'] == 0) {
            $where['LIMIT'] = [0, 1];
        } else {
            $where['LIMIT'] = [0, 20];
        }
        $where['ORDER'] = ["status" => "ASC", 'id' => 'DESC'];
        $data = $this->c2c_order_M->lists_all($where);
    
        foreach ($data as &$vo) {
            if ($vo['uid_buy'] == $user['id']) {
                $uid = $vo['uid_sell'];
            } else {
                $uid = $vo['uid_buy'];
            }
            $user_other = user_info($uid);
            $vo['avatar'] = $user_other['avatar'];
            $vo['nickname'] = $user_other['nickname'];
            if (!$vo['nickname']) {
                $vo['nickname'] = $user_other['username'];
            }
            switch ($vo['status']) {
                case '1':
                    $vo['status'] = '交易中';
                    break;
                case '2':
                    $vo['status'] = '已付款';
                    break;
                case '3':
                    $vo['status'] = '已完成';
                    break;
                case '4':
                    $vo['status'] = '已取消';
                    break;
                default:
            }
            if ($vo['status'] == '交易中' || $vo['status'] == '已付款') {
                switch ($vo['state']) {
                    case '1':
                        $vo['status'] = '买家申述';
                        break;
                    case '2':
                        $vo['status'] = '卖家申述';
                        break;
                    case '3':
                        $vo['status'] = '买家胜诉';
                        break;
                    case '4':
                        $vo['status'] = '卖家胜诉';
                        break;
                    default:
                }
            }
        }
        return $data;
    }

    //交易详情
    public function order_detail()
    {
        (new IDMustBeRequire())->goCheck();
        $id = post('id');

        $c2c_S = new \app\service\c2c();
        $order_ar = $c2c_S->detail($id);

        return $order_ar;
    }

    //发布中
    public function mybuy()
    {
        $user = $GLOBALS['user'];
        $where['status'] = 1;
        $where['uid'] = $user['id'];
        $where['LIMIT'] = [0, 20];
        $where['ORDER'] = ['id' => 'DESC'];
        $data = $this->c2c_buy_M->lists_all($where);
        $price = $this->coin_price_M->price();
        if ($price <= 0) {
            error('价格错误', 400);
        }
        foreach ($data as &$vo) {
            $vo['avatar'] = $user['avatar'];
            $vo['nickname'] = $user['nickname'];
            $vo['integrity'] = $user['integrity'];
            if (!$vo['nickname']) {
                $vo['nickname'] = $user['username'];
            }
            $vo['status'] = '发布中';
            $vo['price'] = $price;
        }
        return $data;
    }

    //发布买
    public function buy()
    {

        (new C2cValidate())->goCheck('buy');
        $money = post('money');
        $manner = post('manner');
        $user = $GLOBALS['user'];

        /* if($user['is_real']!=1){
            $err['info']='请先完善资料';
            $err['url']='/setting/setting';
            error($err,10008);	
        } */

        $uwhere['id'] = $user['id'];
        $uwhere['pay_password'] = '';
        $user_ar = (new \app\model\user())->is_find($uwhere);
        if ($user_ar) {
            $err['info'] = '请先设置支付密码';
            $err['url'] = '/setting/pay_password';
            error($err, 10008);
        }

        if ($user['coin_buy'] >= 100) {
            $c2c_highest = c("c2c_highest2");
        } else {
            $c2c_highest = c('c2c_highest');
        }

        if ($money < c("c2c_lowest") || $money > $c2c_highest) {
            error('购买数量超出范围', 400);
        }
        if ($user['designation'] == 0) {
            $where['uid'] = $user['id'];
            $where['status[<]'] = 3;
            $is_buy = $this->c2c_buy_M->is_have($where);
            if ($is_buy) {
                error('交易完成才能发布下一个', 400);
            }
        }

        if ($user['integrity'] <= 0) {
            error('诚信值不足，请联系客服', 400);
        }

        flash_god($user['id']);

        //下单
        $data['money'] = $money;
        $data['manner'] = $manner;
        $data['uid'] = $user['id'];

        $res = $this->c2c_buy_M->save_by_oid($data);
        empty($res) && error('添加失败', 10006);
        return "发布成功";
    }

    //购买
    public function pay()
    {
        (new IDMustBeRequire())->goCheck();
        $id = post('id');
        $c2c_S = new \app\service\c2c();
        $c2c_S->pay($id);
        return "购买成功";
    }

    //发布卖
    public function sale()
    {

        (new C2cValidate())->goCheck('buy');
        $money = post('money');
        $manner = post('manner');
        $user = $GLOBALS['user'];

        /* if ($user['is_real'] != 1) {
            $err['info'] = '请先完善资料';
            $err['url'] = '/setting/setting';
            error($err, 10008);
        } */

        $uwhere['id'] = $user['id'];
        $uwhere['pay_password'] = '';
        $user_ar = (new \app\model\user())->is_find($uwhere);
        if ($user_ar) {
            $err['info'] = '请先设置支付密码';
            $err['url'] = '/setting/pay_password';
            error($err, 10008);
        }
/* 
        if($user['coin_rating']==1){
            error('游客不能出售',400);
        } */
        //判断资格
        $manner_ar=explode("@",$manner);
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

        if ($user['coin_buy'] >= 100) {
            $c2c_highest = c("c2c_highest2");
        } else {
            $c2c_highest = c('c2c_highest');
        }

        if ($money < c("c2c_lowest") || $money > $c2c_highest) {
            error('购买数量超出范围', 400);
        }
        if ($user['designation'] == 0) {
            $where['uid'] = $user['id'];
            $where['status[<]'] = 3;
            $is_buy = $this->c2c_buy_M->is_have($where);
            if ($is_buy) {
                error('交易完成才能发布下一个', 400);
            }
        }

        if ($user['integrity'] <= 0) {
            error('诚信值不足，请联系客服', 400);
        }

         //判断金额
         $user_M = new \app\model\user();
         $coin = $user_M->find($user['uid'],'coin');
         $fee=$money*c("trade_fee")/1000;
 
         if($money+$fee-$coin>0){
            error('金额不足1',10003);
         }

        flash_god($user['id']);

        //下单
        $redis = new \core\lib\redis();
        $Model = new \core\lib\Model();
        $Model->action();
        $redis->multi();

        $data['money'] = $money;
        $data['manner'] = $manner;
        $data['uid'] = $user['id'];
        $data['types'] = 2;
        $data['fee']=$fee;

        $res = $this->c2c_buy_M->save_by_oid($data);
        empty($res) && error('添加失败', 10006);
          
        $money_S = new \app\service\money();
        $money=$money+$fee;
        $money_S->minus($user['uid'],$money,'coin','coin_c2c',$res['oid'],$user['uid'],'出售币'); //记录资金流水

        $Model->run();
        $redis->exec();
        return "发布成功";
    }


    //出售
    public function sell()
    {
        (new IDMustBeRequire())->goCheck();
        $id = post('id');
        $c2c_S = new \app\service\c2c();
        $c2c_S->sell($id);
        return "出售成功";
    }



    //撤销发布
    public function delbuy()
    {
        (new IDMustBeRequire())->goCheck();
        $user = $GLOBALS['user'];
        $id = post('id');
        $where['id'] = $id;
        $where['uid'] = $user['id'];
        $where['status'] = 1;
        $buy_ar = $this->c2c_buy_M->have($where);
        empty($buy_ar) && error('订单已出售', 400);
        if($buy_ar['types']==2){
            $money_S = new \app\service\money();
            $money=$buy_ar['money']+$buy_ar['fee'];
            $money_S->minus($buy_ar['uid'],$money,'coin','coin_c2c',$buy_ar['oid'],$buy_ar['uid'],'取消订单退回'); //记录资金流水
    
        }
        $this->c2c_buy_M->del($buy_ar['id']);
        return "撤销成功";
    }

    //拒绝打款
    public function cancel()
    {
        (new IDMustBeRequire())->goCheck();
        $user = $GLOBALS['user'];
        $id = post('id');

        $where['id'] = $id;
        $where['uid_buy'] = $user['id'];
        $where['status'] = 1;
        $order_ar = $this->c2c_order_M->have($where);
        if ($order_ar) {
            $this->c2c_S->cancel($order_ar);
            $data['remark'] = '买家取消订单';
            $this->c2c_order_M->up($order_ar['id'], $data);
            $money_S = new \app\service\money();
            $money_S->minus($user['id'], 1, 'integrity', 'coin_c2c', $order_ar['oid_buy'], $order_ar['uid_sell'], '买家取消订单'); //记录资金流水
        }
        return "取消成功";
    }


    //确认付款
    public function payment()
    {
        $user = $GLOBALS['user'];
        (new IDMustBeRequire())->goCheck();
        (new C2cValidate())->goCheck('payment');
        $id = post("id");
        $piclink = post("piclink");

        $password = post("password");
        $password = rsa_decrypt($password);
        $password = md5($password . 'inex10086');
        $auth = (new \app\model\user())->check_user_pay($user['username'], $password);
        empty($auth['id']) && error("密码错误", 400);

        $id = post("id");
        $where['id'] = $id;
        $where['uid_buy'] = $user['id'];
        $where['status'] = 1;
        $order_ar = $this->c2c_order_M->have($where);
        empty($order_ar) && error('订单不存在', 400);

        $data['status'] = 2;
        $data['piclink'] = $piclink;
        $data['payment_time'] = time();
        $res = $this->c2c_order_M->up($id, $data);
        empty($res) && error('付款失败', 10006);
        return "确认付款成功";
    }

    //确认收款
    public function confirm()
    {
        $user = $GLOBALS['user'];
        (new IDMustBeRequire())->goCheck();
        (new C2cValidate())->goCheck('confirm');

        $password = post("password");
        $password = rsa_decrypt($password);
        $password = md5($password . 'inex10086');
        $auth = (new \app\model\user())->check_user_pay($user['username'], $password);
        empty($auth['id']) && error("密码错误", 400);

        $id = post("id");
        $where['id'] = $id;
        $where['uid_sell'] = $user['id'];
        $where['status'] = 2;
        $order_ar = $this->c2c_order_M->have($where);
        empty($order_ar) && error('订单不存在', 400);

        $redis = new \core\lib\redis();
        $Model = new \core\lib\Model();
        $Model->action();
        $redis->multi();

        $c2c_S = new \app\service\c2c();
        $c2c_S->carry_out($order_ar);

        $Model->run();
        $redis->exec();

        return "确认收款成功";
    }

    //申述
    public function state()
    {
        $user = $GLOBALS['user'];
        (new IDMustBeRequire())->goCheck();
        $id = post("id");
        $where['id'] = $id;
        $where['OR'] = [
            "uid_buy" => $user['id'],
            "uid_sell" => $user['id']
        ];
        $where['status[<]'] = 3;
        $order_ar = $this->c2c_order_M->have($where);

        empty($order_ar) && error('订单不存在', 400);

        switch ($order_ar['state']) {
            case '0':
                $data['title'] = '提交申述';
                break;
            case '1':
                if ($order_ar['uid_buy'] == $user['id']) {
                    $data['title'] = '取消申述';
                } else {
                    $data['title'] = '买家审诉中';
                }
                break;
            case '2':
                if ($order_ar['uid_sell'] == $user['id']) {
                    $data['title'] = '取消申述';
                } else {
                    $data['title'] = '卖家审诉中';
                }
                break;
            case '3':
                $data['title'] = '买家胜诉';
                break;
            case '4':
                $data['title'] = '卖家胜诉';
                break;
            default:
        }
        $data['order_ar'] = $order_ar['state_detail'];
        $data['content'] = $order_ar['content'];
        return $data;
    }

    //提交申述
    public function add_state()
    {
        $user = $GLOBALS['user'];
        (new IDMustBeRequire())->goCheck();
        (new C2cValidate())->goCheck('state');
        $id = post("id");
        $state_detail = post("state_detail");
        $where['id'] = $id;
        $where['OR'] = [
            "uid_buy" => $user['id'],
            "uid_sell" => $user['id']
        ];
        $where['status[<]'] = 3;
        $where['state'] = 0;
        $order_ar = $this->c2c_order_M->have($where);
        empty($order_ar) && error('订单不存在', 400);

        if ($order_ar['uid_buy'] == $user['id']) {
            $data['state'] = 1;
        } else {
            $data['state'] = 2;
        }
        $data['state_detail'] = $state_detail;
        $data['state_time'] = time();
        $res = $this->c2c_order_M->up($id, $data);
        empty($res) && error('申述失败', 10006);
        return "申述成功";
    }

    //取消申述
    public function del_state()
    {
        $user = $GLOBALS['user'];
        (new IDMustBeRequire())->goCheck();
        (new C2cValidate())->goCheck('state');
        $id = post("id");
        $state_detail = post("state_detail");
        $where['id'] = $id;
        $where['OR'] = [
            "uid_buy" => $user['id'],
            "uid_sell" => $user['id']
        ];

        $where['status[<]'] = 3;
        $order_ar = $this->c2c_order_M->have($where);
        empty($order_ar) && error('订单不存在', 400);

        if ($order_ar['uid_buy'] == $user['id']) {
            if ($order_ar['state'] != 1) {
                empty($order_ar) && error('订单不存在', 400);
            }
        } else {
            if ($order_ar['state'] != 2) {
                empty($order_ar) && error('订单不存在', 400);
            }
        }
        $data['state'] = 0;
        $data['state_detail'] = '';
        $res = $this->c2c_order_M->up($id, $data);
        empty($res) && error('取消申述失败', 10006);
        return "取消申述成功";
    }

    public function content()
    {
        $user = $GLOBALS['user'];
        (new IDMustBeRequire())->goCheck();
        (new C2cValidate())->goCheck('content');
        $id = post("id");
        $content = post("content");
        $where['id'] = $id;
        $where['OR'] = [
            "uid_buy" => $user['id'],
            "uid_sell" => $user['id']
        ];
        $where['status[<]'] = 3;
        $where['state'] = [1, 2];
        $order_ar = $this->c2c_order_M->have($where);
        empty($order_ar) && error('订单不存在', 400);

        if ($order_ar['uid_buy'] == $user['id']) {
            $data['content'] = $order_ar['content'] . "<div class='c2c_content'><p>买家留言 " . date("Y-m-d H:i:s", time()) . "</p><p>" . $content . "</p></div>";
        } else {
            $data['content'] = $order_ar['content'] . "<div class='c2c_content'><p>卖家留言 " . date("Y-m-d H:i:s", time()) . "</p><p>" . $content . "</p></div>";
        }
        $res = $this->c2c_order_M->up($id, $data);
        empty($res) && error('留言失败', 10006);
        return "留言成功";
    }
}
