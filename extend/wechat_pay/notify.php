<?php
/**
 * Created by yaaaaa_god
 * User: yaaaaa
 * Date: 2018/12/13
 * Desc: 微信支付
 */

namespace extend\wechat_pay;

class notify{
	public function __construct(){
		if (!defined('JSAPI_ROOT')) {
			define('JSAPI_ROOT', dirname(__FILE__) . '/');
			require_once(JSAPI_ROOT . 'lib/WxPay.Api.php');
			require_once(JSAPI_ROOT . 'lib/WxPay.Notify.php');
			require_once(JSAPI_ROOT . 'WxPay.JsApiPay.php');
			require_once(JSAPI_ROOT . 'WxPay.Config.php');
			require_once(JSAPI_ROOT . 'log.php');

			//获取通知的数据
			$xml = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : file_get_contents("php://input");
			if (empty($xml)) {
				# 如果没有数据，直接返回失败
				return false;
			}
			//如果返回成功则验证签名
			try {
				if(!$xml){
					return false;
				}
				//将XML转为array
				//禁止引用外部xml实体
				libxml_disable_entity_loader(true);
				$this->values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
				$result = $this->values;
			} catch (WxPayException $e){
				return false;
			}

			if($result['trade_type']=='APP'){
				$where['iden']='wechat_app';
			}else{
				$where['iden']='wechat';
			}
			$where['show']=1;
			$pay_ar=(new \app\model\pay())->have($where);
			empty($pay_ar) && error('微信登录未开启',404);
			define('MerchantId',$pay_ar['username']);
			define('AppId',$pay_ar['app_id']);
			define('Key',$pay_ar['key']);
			define('AppSecret',$pay_ar['app_secret']);
		}
	}

	public function index()
	{
     
        $config = new \WxPayConfig();
        $notify = new \WxPayNotify($config,false);
        $notify->Handle($config,true);
        $ret_code = $notify->GetReturn_code();
        if ($ret_code == 'SUCCESS') {
            $values = $notify->GetValues();
            return $values;
        }
        error('错误',404);

	}
}
