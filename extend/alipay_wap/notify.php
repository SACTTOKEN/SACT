<?php

/**
 * Created by yaaaaa_god
 * User: yaaaaa
 * Date: 2018/12/13
 * Desc: 支付宝APP支付
 */

namespace extend\alipay_wap;

class notify
{
    public $config;
    public function __construct($iden='alipay')
    {
        if (!defined('JSAPI_ROOT')) {
            define('JSAPI_ROOT', dirname(__FILE__) . '/');
            require_once(JSAPI_ROOT . 'wappay/service/AlipayTradeService.php');

            $where['iden'] = $iden;
            $where['show'] = 1;
            $pay_ar = (new \app\model\pay())->have($where);
            empty($pay_ar) && error('手机支付宝支付未开启', 404);

            $this->config = array(
                //应用ID,您的APPID。
                'app_id' => $pay_ar['app_id'],

                //商户私钥，您的原始格式RSA私钥
                'merchant_private_key' => 'MIIEpgIBAAKCAQEA0G7Ac/lIzq9wpwaGt2bOr07UIOEJdeOuLM1S+2YCfJG9Qphl11/7/9V1gXWhTO13StK6bUBGRkiz+GF3bTy4lLb/jchd7tb/cmmv2cXodqtof1K6t3rmSVomzHw/OfQNZrSaMwtodkg+QWrbK9CnVXDJRthbj9srOVQZFXQnk/jD/BUhLbXqSy4PjAt0k+y/DA3w5fr5ctT4BVqw3IQHYNQLyb330q4bD/UPWTVj9m17zm9OofhbkRA0l8Y+3lcXUDKjRw696hjY7OSSZVxLp308BQT/VSYCZx4jLvN1yZ+X9gwG7dz+gFh3+uAv1Zh0C2J8ITba/DUGc3ame/hCIQIDAQABAoIBAQCmGTwgr21H2CNL1zWP/cuDhKwjL3Ickj4A0fbpBFfC8VkDMvMleQYW0AJ+EkFiTnKcG+YYnfnilJlmvDUxxgvJ5zMrx5qjdI3InVRXlRE1UE9L9594C+ZsWf1FQ1YXVtc/G3kuaE7sw5FpDEBwYCyZN/IOFOiScTO20b/TiubnUQv5oqqQG0mcibV0bXRph4iFyKHxFLafvw4SXpUHfl03CTisG83HY06WleWb6vnMMz/mp+cBW8fwmoIP4fVFN0QqzFHpafEgkPRP5peI0CAniNnbls8CzjUbIiyNZI0pnNcDSYaZCJA7X0VXirfK87OW0RVf1rcYDCs67zt00+MpAoGBAPVL6iHAr5Qv0mhTxo+Y9NdcVgf2cdDQV0TIG2RAMej2SgShTLCXSjyv0jbKNEHnRs9qke4ZBZqdmmqWqzOrKat5jT18HXUXx06McCOVMGPBmJ8Gz0B1A9tgudZJuV/ErFdJ/X0H+EpM/aD0EYPW592njiVkLHMGVofDgOnfehlbAoGBANmHDHp0409GT4k65t1Rps9RcRwroNh1hhLCDX2KLWkuUtrjcVtza/70B7BsfjpJrFCZltKX0P5l03pyViTICqkwURVqLqbp2z8j4lLWNk/+ZZ8AJdTd5Gj/CuxImZvqVWSScoGIaS7N13CESFjQO7d78+K9BUY2f2nwCJtO8K8zAoGBAIyImLQLu8wPdeGVlZ3xiNzVpuha9iwnIMhkSOUvriiE6jUq4FAP7VVFeg8v266iPTxaFw8tQLurbauBdMZeWrpGInhGYm4SWHqVFS4drCKK6NC7SwPnxnTqPq4ZgN3wRLihyFvYtBSFdY3AJ0S8XAzukQ61DI495FdV18al5UMfAoGBALhxMxpuHAMu2efBItnMDwXAx4icUaDYXZtwIOIulHyXw7dHnOlu/8ZJAnAMPieMKmiZInJkOdhLXLp5UiOT3r5AcrAWvYHXzohGE/QrIBhJ276q8GkC0FZa0tcwY9b5JfjF2AOPN6hw7ti/wVxVDB1zI4NAxMUZFoYr+hA+KgRTAoGBAO3zRCnsZKviqSnqsxvyNchI2lQQRN0gj0I5INJL7U8qCt4Ql9RTjqaJuErxjW+7ar7ihfbYQEoJt1CgOETYpjcrf76c2OF9eIWDKFJvCfquhINOKUHn4dYsp6eX5Xc1mt9eQoZ9Jvoh//OFZ3jdiqAzsz/16epnWn7hXVfS2Tmm',
                //'merchant_private_key' => $pay_ar['key'],
           
                //编码格式
                'charset' => "UTF-8",

                //签名方式
                'sign_type' => "RSA2",

                //支付宝网关
                'gatewayUrl' => "https://openapi.alipay.com/gateway.do",

                //支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
                'alipay_public_key' =>  $pay_ar['app_secret'],
            );
        }
    }

    public function index()
    {
        $arr=$_POST;
       
        $alipaySevice = new \AlipayTradeService($this->config); 
        //$alipaySevice->writeLog(var_export($_POST,true));
        $result = $alipaySevice->check($arr);
      
        //txt_log($result,'pay');
        /* 实际验证过程建议商户添加以下校验。
        1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号，
        2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额），
        3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）
        4、验证app_id是否为该商户本身。
        */
        if($result) {//验证成功
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            //请在这里加上商户的业务逻辑程序代

            
            //——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
            
            //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
            
            //商户订单号

            if($_POST['trade_status'] == 'TRADE_FINISHED' || $_POST['trade_status'] == 'TRADE_SUCCESS') {

                $data['out_trade_no'] = $_POST['out_trade_no'];
                $data['trade_no'] = $_POST['trade_no'];
                $data['total_amount'] = $_POST['total_amount'];
                $data['notify_type'] = $_POST['notify_type'];

                return $data;
                //判断该笔订单是否在商户网站中已经做过处理
                    //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                    //请务必判断请求时的total_amount与通知时获取的total_fee为一致的
                    //如果有做过处理，不执行商户的业务程序			
                //注意：
                //付款完成后，支付宝系统发送该交易状态通知
            }
            //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
                
        }else {
            //验证失败
            echo "fail";	//请不要修改或删除
            exit;
        }
        return false;
    }

}