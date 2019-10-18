<?php

/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-07-23 19:19:17
 * Desc: 生活缴费
 */

namespace app\ctrl\mobile;

use app\model\living_pay as LivingPayModel;
use app\validate\IDMustBeRequire;
use app\validate\LivingPayValidate;

class living_pay extends BaseController
{

    public $lv_pay_M;
    public function __initialize()
    {
        $this->lv_pay_M = new LivingPayModel();
    }



    //是否开放
    public function is_living()
    {
        (new LivingPayValidate())->goCheck('scene_types');
        $types = post('types');


        $is_allow = $this->is_open_living($types);


        return $is_allow;
    }



    //是否开放，6个项目，传types
    public function is_open_living($types = 0)
    {
        $is_allow = 0;
        $c_ar = [
            '1' => 'open_living_water',
            '2' => 'open_living_elec',
            '3' => 'open_living_gas',
            '4' => 'open_living_web',
            '5' => 'open_living_tv',
            '6' => 'open_living_property',
        ];
        if ($types > 0) {
            $is_allow = c($c_ar[$types]);
        }

        return $is_allow;
    }

    //币价
    public function coin_price(){
        $coin_price_M = new \app\model\coin_price();
        $price = $coin_price_M->price(); //AICQ币价
        return $price;
    }


    //缴费
    public function saveadd()
    {
        (new LivingPayValidate())->goCheck('scene_saveadd');
        $w = date("w"); //周一至周五10:00～17:00 
        $w_ar = [1, 2, 3, 4, 5];
        $h = date("H");
        if (!in_array($w, $w_ar)) {
            error('周一到周五开放缴费！', 400);
        }
        if ($h >= 17 || $h < 10) {
            error('10点到17点开放缴费！', 400);
        }


        $city = post('city');
        $types = post('types'); //1水费 2电费 3燃气费 4宽带缴费 5有线电视费 6物业费 
        $title = post('title');
        $company = post('company'); //缴费单位
        $number = post('number');  //缴费户号
        $money = post('money'); //30,50
        $fee_per = c('living_pay_fee');

        $fee = floatval($fee_per) * floatval($money) / 100; //手续费
        $coin_price_M = new \app\model\coin_price();
        $price = $coin_price_M->price(); //AICQ币价
        $pay = (floatval($money) + floatval($fee)) / floatval($price);
        $is_allow = $this->is_open_living($types);

        if (!$is_allow) {
            error('未开放该项', 400);
        }

        $uid = $GLOBALS['user']['id']; //判断是否有足够的coin支付
        $user_M = new \app\model\user();
        $user = $user_M->find($uid);
        $have_coin = $user['coin'];
        if ($have_coin < $pay) {
            error('币不足支付,请充币', 400);
        }
        
        if($user['coin_rating']==1){
            error('等级权限不足',400);//游客提示权限不足
        }

        $where['uid'] =  $GLOBALS['user']['id'];
        $where['types'] = $types;
        $where['created_time[>=]'] = strtotime(date('Y-m-d'));
        $is_have = $this->lv_pay_M->is_have($where);
        $is_have && error('亲，单项缴费一天只能付一次哟', 400);

        $data['uid'] = $GLOBALS['user']['id'];
        $data['types'] = $types;
        $data['city'] = $city;
        $data['title'] = $title;
        $data['company'] = $company;
        $data['number'] = $number;
        $data['fee'] = $fee;
        $data['money'] = $money;
        $data['pay'] = $pay;

        flash_god($data['uid']);
        $model = new \core\lib\Model();
        $redis = new \core\lib\redis();
        $model->action();
        $redis->multi();

        $res = $this->lv_pay_M->save_by_oid($data);

        $money = $res['pay'];
        $uid = $res['uid'];
        $cate = 'coin';
        $oid = $res['oid'];
        $ly_id = $res['uid'];
        $remark = '生活缴费扣款';
        $money_S = new \app\service\money();
        $money_S->minus($uid, $money, $cate, "living_pay", $oid, $ly_id, $remark);

        $model->run();
        $redis->exec();

        empty($res) && error('提交失败', 400);
        return $res;
    }



    //缴费记录
    public function lists()
    {
        $uid =  $GLOBALS['user']['id'];
        $where['uid'] = $uid;
        $page = post("page", 1);
        $page_size = post('page_size', 10);
        $data  = $this->lv_pay_M->lists($page, $page_size, $where);
        return $data;
    }
}
