<?php

/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-15 09:09:40
 * Desc: 库存
 */

namespace app\service;

class stock
{

    /**
     * 是否存在库存
     * @param  $pid 商品ID  $sku_id product_sku表的ID
     * @return bool
     */
    public function have_stock($sku_id, $number)
    {
        $pro_sku_M = new \app\model\product_sku();
        $res = $pro_sku_M->find($sku_id, 'stock');
        if ($res >= $number) {
            return true;
        }
        return false;
    }

    //扣库存
    public function buckle_inventory($cart)
    {
        $pro_M = new \app\model\product();
        $pro_sku_M = new \app\model\product_sku();
        foreach ($cart as $vos) {
            foreach ($vos['data'] as $vo) {
                $pro_M->up($vo['pid'], ['stock[-]' => $vo['number']]);
                $pro_sku_M->up($vo['sku_id'], ['stock[-]' => $vo['number']]);
            }
        }
    }

    //未支付退单加库存
    public function plus_stock($cart)
    {
        $pro_M = new \app\model\product();
        $pro_sku_M = new \app\model\product_sku();
        foreach ($cart as $vo) {
            $pro_M->up($vo['pid'], ['stock[+]' => $vo['number']]);
            $pro_sku_M->up($vo['sku_id'], ['stock[+]' => $vo['number']]);
        }
    }

    //加销量
    public function increase_sales($cart)
    {
        $pro_M = new \app\model\product();
        foreach ($cart as $vos) {
            foreach ($vos['data'] as $vo) {
                $pro_M->up($vo['pid'], ['real_sale[+]' => $vo['number']]);
            }
        }
    }

    //限购 $product_ar商品数组 $number购买数量
    public function limit($product_ar, $sku_id, $number)
    {
        $car_number = (new \app\model\cart())->find_sum('number', ['pid' => $product_ar['id'], 'uid' => $GLOBALS['user']['id'], 'sku_id[!]' => $sku_id]);
        $order_product_M = new \app\model\order_product();
        $oid_ar = (new \app\model\order())->lists_all(['status' => '已关闭'], 'oid');
        if ($product_ar['all_limit_buy'] > 0) {
            $sum = $order_product_M->find_sum('number', ['pid' => $product_ar['id'], 'uid' => $GLOBALS['user']['id'], 'status[!]' => 4, 'oid[!]' => $oid_ar]);
            if ($sum + $number + $car_number > $product_ar['all_limit_buy']) {
                error('达到商品总限购', 400);
            }
        }
        if ($product_ar['day_limit_buy'] > 0 && $product_ar['day_limit'] > 0) {
            $times = time() - 86400 * $product_ar['day_limit'];
            $sum = $order_product_M->find_sum('number', ['pid' => $product_ar['id'], 'uid' => $GLOBALS['user']['id'], 'status[!]' => 4, 'oid[!]' => $oid_ar, 'created_time[>]' => $times]);
            if ($sum + $number + $car_number > $product_ar['day_limit_buy']) {
                error('达到商品单日限购', 400);
            }
        }
        return true;
    }

    //获取商品实际价格
    //优先级sku价格，设置商品等级价格，活动商品，等级折扣和满减折扣根据折扣数判断
    public function price($rating, $product_ar, $sku_id, $number)
    {
        //suk价格
        $price = (new \app\model\product_sku())->find($sku_id, ['price', 'cost_price']);

        //判断商品等级价格
        $where['rating'] = $rating;
        $where['pid'] = $product_ar['id'];
        $where['sku_id'] = $sku_id;
        $rating_price = (new \app\model\product_price())->have($where, 'price');
        if ($rating_price) {
            $price['price'] = $rating_price;
            $discount = 10;
        } else {
            $discount = (new \app\model\rating())->find($rating, 'discount');
            if ($discount > 10 || $discount <= 0) {
                $discount = 10;
            }
        }

        //如果是限时特惠或者砍价拼团，返回默认价格无等级折扣，满几件折扣
        if ($product_ar['types'] == 4 || $product_ar['types'] == 7 || $product_ar['types'] == 3) {
            if ($price['cost_price'] > $price['price']) {
                $price['price'] = floatval($price['cost_price']);
            }
            return sprintf('%.2f', $price['price']);
        }

        //不算特殊商品判断打几折
        if ($product_ar['discount_number'] > 0 && $number >= $product_ar['discount_number'] && $product_ar['discount'] < 10) {
            $discount1 = $product_ar['discount'];
        } else {
            $discount1 = 10;
        }
        if ($discount > $discount1) {
            $discount = $discount1;
        }
        $price['price'] = (floatval($price['price']) * floatval($discount)) / 10;
        if ($price['cost_price'] > $price['price']) {
            $price['price'] = floatval($price['cost_price']);
        }
        return sprintf('%.2f', $price['price']);
    }

    //获取商品成本价格
    public function cost($sku_id)
    {
        $cost = (new \app\model\product_sku())->find($sku_id, 'cost_price');
        return $cost;
    }

    //获取邮费
    public function get_mail($vos, $province)
    {
        $weight = 0;
        $actualprice = 0;
        foreach ($vos['data'] as $vo) {
            if ($vo['pro']['weight_fix'] == 'KG') {
                $vo['pro']['weight'] = $vo['pro']['weight'] * 1000;
            }
            $weight = $weight + $vo['pro']['weight'] * $vo['number'];
            $actualprice = $actualprice + $vo['pro']['sum_price'];
            if ($vo['pro']['is_mail'] == 1) {
                return 0;   //商品免邮
            }
        }
        if ($weight == 0) {
            return 0;   //总重量0
        }
        $weight_kg = intval($weight / 1000);
        if ($weight_kg > 0 and $weight % 1000 == 0) {
            $weight_kg = $weight_kg - 1;
        }
        $MailM = new \app\model\mail();
        $mailprice = $MailM->have(['sid' => $vos['info']['sid']]);
        if (empty($mailprice)) {
            return 0; //邮费未设置
        }
        if ($mailprice['is_free_post'] == 1 and $mailprice['free_post'] > 0 and $actualprice >= $mailprice['free_post']) {
            return 0;   //满额包邮
        }

        $MailAreaM = new \app\model\mail_area();
        $MailAreaPrice = $MailAreaM->have(["AND" => ['sid' => $vos['info']['sid'], 'province' => $province]], ['first_weight', 'continued_weight']);
        if (!empty($MailAreaPrice)) {
            $mailprice = $MailAreaPrice;
        }
        $mail = $mailprice['first_weight'] + $mailprice['continued_weight'] * $weight_kg;
        return $mail;
    }
}
