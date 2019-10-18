<?php
/**
 * 支付接口调测例子
 * ================================================================
 * index 进入口，方法中转
 * submitOrderInfo 提交订单信息
 * queryOrder 查询订单
 * 
 * ================================================================
 */
namespace extend\full_pay;


class request{
    //$url = 'http://192.168.1.185:9000/pay/gateway';

    private $resHandler = null;
    private $reqHandler = null;
    private $pay = null;
    private $cfg = null;
    
    public function __construct(){
        if (!defined('JSAPI_ROOT')) {
			define('JSAPI_ROOT', dirname(__FILE__) . '/');
			require_once(JSAPI_ROOT . 'Utils.class.php');
			require_once(JSAPI_ROOT . 'config/config.php');
			require_once(JSAPI_ROOT . 'class/RequestHandler.class.php');
			require_once(JSAPI_ROOT . 'class/ClientResponseHandler.class.php');
			require_once(JSAPI_ROOT . 'class/PayHttpClient.class.php');
		}
        $this->Request();
    }

    public function Request(){
        $this->resHandler = new \ClientResponseHandler();
        $this->reqHandler = new \RequestHandler();
        $this->pay = new \PayHttpClient();
        $this->cfg = new \Config();

        $this->reqHandler->setGateUrl($this->cfg->C('url'));
       
        $sign_type = $this->cfg->C('sign_type');
        
        if ($sign_type == 'MD5') {
            $this->reqHandler->setKey($this->cfg->C('key'));
            $this->resHandler->setKey($this->cfg->C('key'));
            $this->reqHandler->setSignType($sign_type);
        } else if ($sign_type == 'RSA_1_1' || $sign_type == 'RSA_1_256') {
            $this->reqHandler->setRSAKey($this->cfg->C('private_rsa_key'));
            $this->resHandler->setRSAKey($this->cfg->C('public_rsa_key'));
            $this->reqHandler->setSignType($sign_type);
        }
    }
    
    /**
     * 提交订单信息
     */
    public function submitOrderInfo($oid){
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
        $sub_openid=(new \app\model\user())->find($order_ar['uid'],'openid');
        if(empty($sub_openid)){
            return "请先绑定公众号";
        }
        $parameter['method']='submitOrderInfo';
        $parameter['out_trade_no']=$order_ar['oid'];
        $parameter['body']=$order_ar['oid'].'公众号支付';
        $parameter['total_fee']=sprintf("%.0f",$order_ar['money']*100);
        $parameter['mch_create_ip']=ip();
        $this->reqHandler->setReqParams($parameter,array('method'));
        $this->reqHandler->setParameter('service','pay.weixin.jspay');//接口类型：pay.weixin.jspay
        $this->reqHandler->setParameter('mch_id',$this->cfg->C('mchId'));//必填项，商户号，由平台分配
        $this->reqHandler->setParameter('version',$this->cfg->C('version'));
        $this->reqHandler->setParameter('sign_type',$this->cfg->C('sign_type'));
		$this->reqHandler->setParameter('notify_url','http://'.cc('web_config','api').'/common/notify_url/fill_pay');//支付成功异步回调通知地址，目前默认是空格，商户在测试支付和上线时必须改为自己的，且保证外网能访问到
		// $this->reqHandler->setParameter('callback_url','http://www.swiftpass.com');
		$this->reqHandler->setParameter('sub_appid','wx81181ab98ff3a5b6');//对应公众号appid，使用测试号时置空，使用正式商户号时必填
		$this->reqHandler->setParameter('sub_openid',$sub_openid);//对应公众号获取到的用户openid，使用测试号时置空，使用正式商户号时必填(使用微信官方网页授权接口获取地址：            https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421140842&token=&lang=zh_CN )
        $this->reqHandler->setParameter('is_raw','1');
        $this->reqHandler->setParameter('nonce_str',mt_rand());//随机字符串，必填项，不长于 32 位
        $this->reqHandler->createSign();//创建签名
        
        $data = \Utils::toXml($this->reqHandler->getAllParameters());
        //var_dump($data);
        //\Utils::dataRecodes(date("Y-m-d H:i:s",time()).'公众号支付请求XML',$data);//请求xml记录到result.txt
        $this->pay->setReqContent($this->reqHandler->getGateURL(),$data);
		
        if($this->pay->call()){
            $this->resHandler->setContent($this->pay->getResContent());
            $this->resHandler->setKey($this->reqHandler->getKey());
            $res = $this->resHandler->getAllParameters();
            //\Utils::dataRecodes(date("Y-m-d H:i:s",time()).'支付返回XML',$res);
            if($this->resHandler->isTenpaySign()){
                
                if($this->resHandler->getParameter('status') == 0 && $this->resHandler->getParameter('result_code') == 0){
                    //当返回状态与业务结果都为0时继续判断
                    //echo json_encode(array('status'=>200,'data'=>$res));
                    return $this->resHandler->getParameter('pay_info');
                    //echo json_encode(array('code_img_url'=>$this->resHandler->getParameter('code_img_url'),
                    //'pay_info'=>$this->resHandler->getParameter('pay_info')));
                    exit();
                }else{
                    echo json_encode(array('status'=>500,'msg'=>$this->resHandler->getParameter('message').$this->resHandler->getParameter('err_code').$this->resHandler->getParameter('err_msg')));
                    exit();
                }
            }
            return json_encode(array('status'=>500,'msg'=>$this->resHandler->getParameter('message')));
        }else{
            return json_encode(array('status'=>500,'msg'=>'Response Code:'.$this->pay->getResponseCode().' Error Info:'.$this->pay->getErrInfo()));
        }
    }

    /**
     * 提交订单信息
     */
    public function submitOrderInfo_app($oid){
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
        $parameter['method']='submitOrderInfo';
        $parameter['out_trade_no']=$order_ar['oid'];
        $parameter['body']=$order_ar['oid'].'公众号支付';
        $parameter['total_fee']=sprintf("%.0f",$order_ar['money']*100);
        $parameter['mch_create_ip']=ip();

        $this->reqHandler->setReqParams($parameter,array('method'));
        // $this->reqHandler->setParameter('service','unified.trade.pay');//非原生统一下单
        $this->reqHandler->setParameter('service','pay.weixin.raw.app');//原生统一下单
        $this->reqHandler->setParameter('mch_id',$this->cfg->C('mchId'));//必填项，商户号，由平台分配
        $this->reqHandler->setParameter('appid', 'wx81181ab98ff3a5b6');//如果调用原生统一下单，则此参数必填
        $this->reqHandler->setParameter('version','2.0');
		$this->reqHandler->setParameter('sub_appid', 'wx81181ab98ff3a5b6');
        $this->reqHandler->setParameter('sign_type',$this->cfg->C('sign_type'));
		//$this->reqHandler->setParameter('op_shop_id','1314');
		//$this->reqHandler->setParameter('device_info','长江');
		//$this->reqHandler->setParameter('op_device_id','东风一号');
		// $this->reqHandler->setParameter('limit_credit_pay','1');   //是否支持信用卡，1为不支持，0为支持
        //$this->reqHandler->setParameter('groupno','8111100093');
        //通知地址，必填项，接收平台通知的URL，需给绝对路径，255字符内格式如:http://wap.tenpay.com/tenpay.asp
        //$notify_url = 'http://'.$_SERVER['HTTP_HOST'];
        //$this->reqHandler->setParameter('notify_url',$notify_url.'/payInterface/request.php?method=callback');
		$this->reqHandler->setParameter('notify_url','http://'.cc('web_config','api').'/common/notify_url/fill_pay');//商户需传自己的
        $this->reqHandler->setParameter('nonce_str',mt_rand(time(),time()+rand()));//随机字符串，必填项，不长于 32 位
        $this->reqHandler->createSign();//创建签名
        
        $data = \Utils::toXml($this->reqHandler->getAllParameters());
        //var_dump($data);
        
        $this->pay->setReqContent($this->reqHandler->getGateURL(),$data);
        if($this->pay->call()){
            $this->resHandler->setContent($this->pay->getResContent());
            $this->resHandler->setKey($this->reqHandler->getKey());
            if($this->resHandler->isTenpaySign()){
                //当返回状态与业务结果都为0时才返回，其它结果请查看接口文档
                if($this->resHandler->getParameter('status') == 0 && $this->resHandler->getParameter('result_code') == 0){
                    return array('pay_info'=>$this->resHandler->getParameter('pay_info'),'out_trade_no'=>$this->resHandler->getParameter('out_trade_no'),'transaction_id'=>$this->resHandler->getParameter('transaction_id'),'service'=>'pay.weixin.raw.app');
                    exit();
                }else{
                    return json_encode(array('status'=>500,'msg'=>'Error Code:'.$this->resHandler->getParameter('err_code').' Error Message:'.$this->resHandler->getParameter('err_msg')));
                    exit();
                }
            }
            return json_encode(array('status'=>500,'msg'=>'Error Code:'.$this->resHandler->getParameter('status').' Error Message:'.$this->resHandler->getParameter('message')));
        }else{
            return json_encode(array('status'=>500,'msg'=>'Response Code:'.$this->pay->getResponseCode().' Error Info:'.$this->pay->getErrInfo()));
        }
    }

    
    /**
     * 异步通知回调
     */
    public function callback(){
        $xml = file_get_contents('php://input');
		//txt_log($xml,'pay');//检测是否执行callback方法，如果执行，会生成1.txt文件，且文件中的内容就是通知参数
        $this->resHandler->setContent($xml);
		//var_dump($this->resHandler->setContent($xml));
        $this->resHandler->setKey($this->cfg->C('key'));
        if($this->resHandler->isTenpaySign()){
            if($this->resHandler->getParameter('status') == 0 && $this->resHandler->getParameter('result_code') == 0){
				$tradeno = $this->resHandler->getParameter('out_trade_no');
				$transaction_id = $this->resHandler->getParameter('transaction_id');
				$total_fee = $this->resHandler->getParameter('total_fee');
				// 此处可以在添加相关处理业务，校验通知参数中的商户订单号out_trade_no和金额total_fee是否和商户业务系统的单号和金额是否一致，一致后方可更新数据库表中的记录。
				//更改订单状态
                //\Utils::dataRecodes('接口回调收到通知参数',$this->resHandler->getAllParameters());
                //ob_clean();
                $data['out_trade_no']=$tradeno;
                $data['transaction_id']=$transaction_id;
                $data['total_fee']=$total_fee;
                return $data;
                exit();
            }else{
                echo 'failure1';
                exit();
            }
        }else{
            echo 'failure2';
        }
    }
}