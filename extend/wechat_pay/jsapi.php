<?php
/**
 * Created by yaaaaa_god
 * User: yaaaaa
 * Date: 2018/12/13
 * Desc: 微信支付
 */

namespace extend\wechat_pay;

class jsapi{
	public $setting;
	public function __construct($setting='wechat'){
		$this->setting=$setting;
		if (!defined('JSAPI_ROOT')) {
			define('JSAPI_ROOT', dirname(__FILE__) . '/');
			require_once(JSAPI_ROOT . 'lib/WxPay.Api.php');
			require_once(JSAPI_ROOT . 'WxPay.JsApiPay.php');
			require_once(JSAPI_ROOT . 'WxPay.Config.php');
			require_once(JSAPI_ROOT . 'log.php');

			$where['iden']=$setting;
			$where['show']=1;
			$pay_ar=(new \app\model\pay())->have($where);
			empty($pay_ar) && error('微信登录未开启',404);
			define('MerchantId',$pay_ar['username']);
			define('AppId',$pay_ar['app_id']);
			define('Key',$pay_ar['key']);
			define('AppSecret',$pay_ar['app_secret']);
		}
	}

	public function index($oid='',$code='')
	{
		$tools = new \JsApiPay();
		if($this->setting=='wechat'){
			if($oid && $code==''){
				set_cookie('pay_oid',$oid,time()+120);
			}else{
				$oid=$_COOKIE['pay_oid'];
			}
			$openId = $tools->GetOpenid($code);
			if(!$openId){
				return "openid为空";
			}
		}else{
			if($oid){
				set_cookie('pay_oid',$oid,time()+120);
				$baseUrl = 'http://'.cc('web_config','api').'/common/wx/wx_app_pay';
				$err['info']='';
				$err['url']=$baseUrl;
				error($err,10008);
			}else{
				$oid=$_COOKIE['pay_oid'];
			}
		}
		//订单
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
		//是否下单用户
		$redis=new \core\lib\redis();
		$redis_name = 'user:'.$order_ar['uid'];
		$token = $redis->hget($redis_name,'user_token');
		$GLOBALS['user'] = $redis->hget($redis_name);
		if(empty($GLOBALS['user'])){
			return "登录过期，请重新登录";
		}
		if(empty($GLOBALS['user']['show'])){
			return "登录过期，请重新登录";
		}
		if($GLOBALS['user']['show']==0){
			return "账号被冻结";
		}
		/* if(!DEBUG){
			//身份是否合法
			if($token=='' || $_COOKIE['user_token']=='' || $token != $_COOKIE['user_token']){  
				return "登录过期，请重新登录";
			}
		} */ 
		//统一下单
		$input = new \WxPayUnifiedOrder();
		$input->SetBody($order_ar['oid'].'公众号支付');
		$input->SetAttach("order");
		$input->SetOut_trade_no($order_ar['oid']);
		$input->SetTotal_fee($order_ar['money']*100);
		$input->SetNotify_url("http://".cc('web_config','api')."/common/notify_url/wechat");
		if($this->setting=='wechat'){
			$input->SetTrade_type("JSAPI");
			$input->SetOpenid($openId);
		}else{
			$input->SetTrade_type("APP");
		}
		$config = new \WxPayConfig();
		$order = \WxPayApi::unifiedOrder($config, $input);
		if($order['return_code']=='FAIL'){
        	error($order['return_msg'],404);
        }
		$jsApiParameters = $tools->GetJsApiParameters($order,$this->setting);
		$jsApiParameters_ar=json_decode($jsApiParameters,true);
		//获取共享收货地址js函数参数
		$editAddress = $tools->GetEditAddressParameters();
		?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="zh-CN" lang="zh-CN">
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<meta http-equiv="Content-Language" content="zh-CN" />
<meta name="viewport" content="width=device-width; initial-scale=1.0;  minimum-scale=1.0; maximum-scale=2.0"/>
<script language="javascript" type="text/javascript" src="/resource/js/jquery.min.js"></script>
<title>微信安全支付</title>
<head>
    <script type="text/javascript">
	//调用微信JS api 支付
	function jsApiCall()
	{
		WeixinJSBridge.invoke(
			'getBrandWCPayRequest',
			<?php echo $jsApiParameters; ?>,
			function(res){
				WeixinJSBridge.log(res.err_msg);
				//alert(res.err_code+res.err_desc+res.err_msg);
			}
		);
	}

	function callpay()
	{
		<?php if($this->setting=='wechat'){?>
			if (typeof WeixinJSBridge == "undefined"){
				if( document.addEventListener ){
					document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
				}else if (document.attachEvent){
					document.attachEvent('WeixinJSBridgeReady', jsApiCall); 
					document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
				}
			}else{
				jsApiCall();
			}
		<?php }else{?>
			ykAPP.wxPayPreNonTimeSign("<?php echo MerchantId;?>","<?php echo $order['prepay_id'];?>","<?php echo $jsApiParameters_ar['nonceStr'];?>","<?php echo $jsApiParameters_ar['timeStamp'];?>","<?php echo $jsApiParameters_ar['paySign'];?>");
		<?php }?>
	}
	</script>
	<script type="text/javascript">
	//获取共享地址
	function editAddress()
	{
		WeixinJSBridge.invoke(
			'editAddress',
			<?php echo $editAddress; ?>,
			function(res){
				var value1 = res.proviceFirstStageName;
				var value2 = res.addressCitySecondStageName;
				var value3 = res.addressCountiesThirdStageName;
				var value4 = res.addressDetailInfo;
				var tel = res.telNumber;
			}
		);
	}
	
	window.onload = function(){
		if (typeof WeixinJSBridge == "undefined"){
		    if( document.addEventListener ){
		        document.addEventListener('WeixinJSBridgeReady', editAddress, false);
		    }else if (document.attachEvent){
		        document.attachEvent('WeixinJSBridgeReady', editAddress); 
		        document.attachEvent('onWeixinJSBridgeReady', editAddress);
		    }
		}else{
			editAddress();
		}
	};
	
	</script>
</head>
<body style=" margin:0; border:0;background:#eee">
<div style="background:#4db232; text-align:center"><img src="/resource/img/weixingzf.jpg" width="50%"/></div>
<div style="padding:0 2em">
	<div style="height:5em;line-height:5em;color:#ababab">
		<img src="/resource/img/xuanzhong.png" style="width:1em"/> 已开启安全支付
	</div>
	<button type="button" onclick="callpay()"  id="tjdd_1" style="height:2.5em;width:100%;background:#4cb131;border-radius:1em;border:0px;font-size:1.2em;color:#fff;font-family:'微软雅黑'">确 认 支 付</button>
	<div style="height:5em;line-height:5em;text-align:center;font-size:0.75em;"><a href="<?php echo c('wx_mobile_web');?>" style="color:#ccc;text-decoration:none">返回首页</a></div>
</div>
<script>
function sadas()
{
document.getElementById("tjdd_1").click();
}
setTimeout('sadas()',100); //1秒=1000，这里是3秒
setTimeout("pdsfzf()",5000);
function pdsfzf(){
	<?php if($order_ar['oid'][0]=='R'){?>
	$.post("/common/notify_url/is_recharge_pay",{'id':'<?php echo $order_ar['id'];?>'},function(indexData){
		if(indexData==1){
			window.location.href='<?php echo c('wx_mobile_web');?>/pay/paydetails?id=<?php echo $order_ar['id'];?>';
		}
	});	
	<?php }else{?>
	$.post("/common/notify_url/is_pay",{'id':'<?php echo $order_ar['id'];?>'},function(indexData){
		if(indexData==1){
			window.location.href='<?php echo c('wx_mobile_web');?>/order/paydetails?id=<?php echo $order_ar['id'];?>';
		}
	});	
	<?php }?>
	setTimeout("pdsfzf()",1000);	
}
</script>
		<?php
		exit;
	}
}
