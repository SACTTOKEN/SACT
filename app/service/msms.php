<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-19 10:19:13
 * Desc: 手机验证码 注：本类需要在config表配置C位的参数
 */
namespace app\service;

class msms
{
	public $sms_M;

	public function __construct(){
		$this->sms_M = new \app\model\sms();
	}

	//发送验证码
	public function send($tel,$quhao='86',$uid=0){
		$ip=ip();
		$where['tel']=$tel;
		$where['created_time[>]']=time()-10;
		$ar=$this->sms_M->is_have($where);
		$ar && error('10秒只能发送一次',400); 
		
		$ip_where['OR']['ip']=$ip;
		$ip_where['OR']['tel']=$tel;
		$ip_where['created_time[>]']=strtotime(date('Y-m-d'));
		$num=$this->sms_M->new_count($ip_where);
		
		if($num>=c('smsipdxnum')){
			error('发送短信太频繁',400);
		}

		$number=$this->sms_M->new_count();
		if($number>=c("duanxinsl")){
			error('短信条数不足',400);
		}

		$code = rand('1000','9999');
		$redis = new \core\lib\redis();
		$uni_code = uniqid(); //防验证码不是从请求来源过来的
		$value = $code."@".$uni_code;
		$redis->set("sms:".$tel,$value);
		$redis->expire("sms:".$tel,900);

		$btdx = plugin_is_open('btdx');
		$gjdx = plugin_is_open('gjdx'); //是否开放国际短信

		if(($btdx==1 || $gjdx==1) && $quhao=='86'){
			$back = $this->btdx($code,$tel);
		}elseif($quhao!='86' && $gjdx==1){
			$back = $this->gjdx($code,$tel,$quhao);
		}else{
        	$back="短信未开通";
        }
		if($back=='ok'){
			$sms_ar['tel']=$tel;
			$sms_ar['uid']=$uid;
			$sms_ar['ip']=$ip;
			$sms_ar['code']=$code;
			$sms_ar['uni_code']=$uni_code;
			$this->sms_M->save($sms_ar);

			$data['status']=1;
			$data['info']=$uni_code;
			return $data;
		}else{
			$data['status']=0;
			$data['info']=$back;
		}
		return $data;
	}

	//C2C匹配成功短信通知
	public function c2c($tel,$quhao='86',$uid=0)
	{
		$code = '您好，您的交易信息已变更，请登录平台查看';
		$btdx = plugin_is_open('btdx');
		$gjdx = plugin_is_open('gjdx'); //是否开放国际短信

		if(($btdx==1 || $gjdx==1) && $quhao=='86'){
			$back = $this->btdx($code,$tel,'c2c_sms_templateId');
		}elseif($quhao!='86' && $gjdx==1){
			$back = $this->gjdx($code,$tel,$quhao,'c2c_sms_templateId');
		}else{
        	$back="短信未开通";
		}
		
		if($back=='ok'){
			$sms_ar['tel']=$tel;
			$sms_ar['uid']=$uid;
			$sms_ar['ip']=ip();
			$sms_ar['code']='';
			$sms_ar['uni_code']='';
			$this->sms_M->save($sms_ar);
		}
		return true;
	}

	public function btdx($code,$tel,$sms_templateId='sms_templateId'){
		$account=cc('account','sms');
		$data['Account'] = $account['sms_account'];
		$data['Pwd'] 	 = $account['sms_pwd'];
		$data['Content'] = $code;
		$data['Mobile']	 = $tel;
		$data['TemplateId']	 = $account[$sms_templateId]; //"您的验证码@，在五分钟内有效。千万不可以告诉别人哟！";
		$data['SignId']	 = c('sms_signId');
		$url="http://api.feige.ee/SmsService/Template";
		$back = $this->feige_post($url,$data);  //{"SendId":"","InvalidCount":0,"SuccessCount":0,"BlackCount":0,"Code":10018,"Message":"签名Id有误"} 可通过sendID去后台查记录
		$ar = json_decode($back,true);
		if($ar['Code']!=0){
			return $ar['Message'];
		}
		return 'ok';

	}

	public function gjdx($code,$tel,$quhao,$sms_templateId='sms_templateId'){
		$account=cc('account','gjsms');
		$data['Account'] 	 = $account['sms_account'];
		$data['Pwd'] 	 	 = $account['sms_pwd'];
		$data['Content'] 	 = $code;
		$data['Mobile']	 	 = $quhao.$tel;  //需要带上国际代码 例如 8613812345678
		$data['TemplateId']	 = $account[$sms_templateId];
		$data['SignId']	 	 = c('sms_gj_signId');
		$url="http://api.feige.ee/SmsService/Inter";
		$back = $this->feige_post($url,$data);
		$ar = json_decode($back,true);
		if($ar['Code']!=0){
			return $ar['Message'];
		}
		return 'ok';
	}

	public function feige_post($url, $data, $proxy = null, $timeout = 20) {
	$curl = curl_init();  
	curl_setopt($curl, CURLOPT_URL, $url);    
	curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); //在HTTP请求中包含一个"User-Agent: "头的字符串。        
	curl_setopt($curl, CURLOPT_HEADER, 0); //启用时会将头文件的信息作为数据流输出。   
	curl_setopt($curl, CURLOPT_POST, true); //发送一个常规的Post请求  
	curl_setopt($curl,  CURLOPT_POSTFIELDS, $data);//Post提交的数据包  
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); //启用时会将服务器服务器返回的"Location: "放在header中递归的返回给服务器，使用CURLOPT_MAXREDIRS可以限定递归返回的数量。     
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); //文件流形式         
	curl_setopt($curl, CURLOPT_TIMEOUT, $timeout); //设置cURL允许执行的最长秒数。   
	$content = curl_exec($curl);  
	curl_close($curl);  
	unset($curl);
	return $content;  
	} 

	
}