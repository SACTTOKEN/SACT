<?php

/**
 * Created by yaaaaa_god
 * User: yaaaaa
 * Date: 2018/12/13
 * Desc: 支付宝APP支付
 */

namespace extend\alipay_app;

class pay
{

    public $config;
    public function __construct()
    {
        if (!defined('JSAPI_ROOT')) {
            define('JSAPI_ROOT', dirname(__FILE__) . '/');
            require_once(JSAPI_ROOT . 'AopSdk.php');

            $where['iden'] = 'alipay_app';
            $where['show'] = 1;
            $pay_ar = (new \app\model\pay())->have($where);
            empty($pay_ar) && error('手机支付宝支付未开启', 404);

            $this->config = array(
                //应用ID,您的APPID。
                'app_id' => $pay_ar['app_id'],

                //商户私钥，您的原始格式RSA私钥
                'merchant_private_key' => 'MIIEpgIBAAKCAQEA0G7Ac/lIzq9wpwaGt2bOr07UIOEJdeOuLM1S+2YCfJG9Qphl11/7/9V1gXWhTO13StK6bUBGRkiz+GF3bTy4lLb/jchd7tb/cmmv2cXodqtof1K6t3rmSVomzHw/OfQNZrSaMwtodkg+QWrbK9CnVXDJRthbj9srOVQZFXQnk/jD/BUhLbXqSy4PjAt0k+y/DA3w5fr5ctT4BVqw3IQHYNQLyb330q4bD/UPWTVj9m17zm9OofhbkRA0l8Y+3lcXUDKjRw696hjY7OSSZVxLp308BQT/VSYCZx4jLvN1yZ+X9gwG7dz+gFh3+uAv1Zh0C2J8ITba/DUGc3ame/hCIQIDAQABAoIBAQCmGTwgr21H2CNL1zWP/cuDhKwjL3Ickj4A0fbpBFfC8VkDMvMleQYW0AJ+EkFiTnKcG+YYnfnilJlmvDUxxgvJ5zMrx5qjdI3InVRXlRE1UE9L9594C+ZsWf1FQ1YXVtc/G3kuaE7sw5FpDEBwYCyZN/IOFOiScTO20b/TiubnUQv5oqqQG0mcibV0bXRph4iFyKHxFLafvw4SXpUHfl03CTisG83HY06WleWb6vnMMz/mp+cBW8fwmoIP4fVFN0QqzFHpafEgkPRP5peI0CAniNnbls8CzjUbIiyNZI0pnNcDSYaZCJA7X0VXirfK87OW0RVf1rcYDCs67zt00+MpAoGBAPVL6iHAr5Qv0mhTxo+Y9NdcVgf2cdDQV0TIG2RAMej2SgShTLCXSjyv0jbKNEHnRs9qke4ZBZqdmmqWqzOrKat5jT18HXUXx06McCOVMGPBmJ8Gz0B1A9tgudZJuV/ErFdJ/X0H+EpM/aD0EYPW592njiVkLHMGVofDgOnfehlbAoGBANmHDHp0409GT4k65t1Rps9RcRwroNh1hhLCDX2KLWkuUtrjcVtza/70B7BsfjpJrFCZltKX0P5l03pyViTICqkwURVqLqbp2z8j4lLWNk/+ZZ8AJdTd5Gj/CuxImZvqVWSScoGIaS7N13CESFjQO7d78+K9BUY2f2nwCJtO8K8zAoGBAIyImLQLu8wPdeGVlZ3xiNzVpuha9iwnIMhkSOUvriiE6jUq4FAP7VVFeg8v266iPTxaFw8tQLurbauBdMZeWrpGInhGYm4SWHqVFS4drCKK6NC7SwPnxnTqPq4ZgN3wRLihyFvYtBSFdY3AJ0S8XAzukQ61DI495FdV18al5UMfAoGBALhxMxpuHAMu2efBItnMDwXAx4icUaDYXZtwIOIulHyXw7dHnOlu/8ZJAnAMPieMKmiZInJkOdhLXLp5UiOT3r5AcrAWvYHXzohGE/QrIBhJ276q8GkC0FZa0tcwY9b5JfjF2AOPN6hw7ti/wVxVDB1zI4NAxMUZFoYr+hA+KgRTAoGBAO3zRCnsZKviqSnqsxvyNchI2lQQRN0gj0I5INJL7U8qCt4Ql9RTjqaJuErxjW+7ar7ihfbYQEoJt1CgOETYpjcrf76c2OF9eIWDKFJvCfquhINOKUHn4dYsp6eX5Xc1mt9eQoZ9Jvoh//OFZ3jdiqAzsz/16epnWn7hXVfS2Tmm',
                //'merchant_private_key' => $pay_ar['key'],
                //异步通知地址
                'notify_url' => "http://".cc('web_config','api')."/common/notify_url/alipay_app",

                //同步跳转
                'return_url' => "http://".cc('web_config','api')."/common/notify_url/alipay_return",

                //编码格式
                'charset' => "UTF-8",

                //签名方式
                'sign_type' => "RSA2",

                //支付宝网关
                'gatewayUrl' => "https://openapi.alipay.com/gateway.do",

                //支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
                'alipay_public_key' =>$pay_ar['app_secret'],
            );
        }
    }

    public function index($oid)
    {
        if($oid[0]=='R'){
            $where['oid']=$oid;
            $where['status']=0;
            $order_ar=(new \app\model\recharge())->have($where);
        }else{
            $where['oid']=$oid;
            $where['is_pay']=0;
            $order_ar=(new \app\model\order())->have($where);
        }
        
		if(empty($order_ar)){
			return "订单不存在";
		}
       
        $aop = new \AopClient;
        $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
        $aop->appId = $this->config['app_id'];
        $aop->rsaPrivateKey = $this->config['merchant_private_key'];
        $aop->format = "json";
        $aop->charset = "UTF-8";
        $aop->signType = "RSA2";
        $aop->alipayrsaPublicKey = $this->config['alipay_public_key'];
        //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
        $request = new \AlipayTradeAppPayRequest();
        //SDK已经封装掉了公共参数，这里只需要传入业务参数
        $bizcontent = "{\"body\":\"".$order_ar['oid']."支付宝APP支付\"," 
                        . "\"subject\": \"".$order_ar['oid']."支付宝APP支付\","
                        . "\"out_trade_no\": \"".$order_ar['oid']."\","
                        . "\"timeout_express\": \"30m\"," 
                        . "\"total_amount\": \"".sprintf("%.2f",$order_ar['money'])."\","
                        . "\"product_code\":\"QUICK_MSECURITY_PAY\""
                        . "}";
        $request->setNotifyUrl("http://".cc('web_config','api')."/common/notify_url/alipay_wap");
        $request->setBizContent($bizcontent);
        //这里和普通的接口调用不同，使用的是sdkExecute
        $response = $aop->sdkExecute($request);
        //htmlspecialchars是为了输出到页面时防止被浏览器将关键参数html转义，实际打印到日志以及http传输不会有这个问题
        return ['id'=>$order_ar['id'],'is_alipay_app'=>1,'data'=>$response];//就是orderString 可以直接给客户端请求，无需再做处理。
    }

}