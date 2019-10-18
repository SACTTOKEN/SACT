<?php

namespace extend\wechat_cash;
/*微信红包*/
class wx_redpack
{
	//发起支付请求
	function wxpay($url, $obj, $mch_secret)
	{
	 $cert = true;
	 if(empty($mch_secret)){error('请到商户平台API安全里设置密钥key');}			
	 $obj['nonce_str'] = $this->create_noncestr();
	 $stringA = $this->formatQueryParaMap($obj, false);
	 $stringSignTemp = $stringA . "&key=".$mch_secret;//注：key为商户平台API安全里设置的密钥key 123456789asdfghjkl12345678912345
	 $sign = strtoupper(md5($stringSignTemp));
	 $obj['sign'] = $sign;
	 $postXml = $this->arrayToXml($obj);
	 // echo "<pre>";
	 // var_dump($postXml);
	 // exit();
	 $responseXml = $this->http_request($url, $postXml, $cert);
	 return $responseXml;
	}

	//随机字符串
	function create_noncestr($length = 32)
	{
	 $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	 $str = "";
	 for ( $i = 0; $i < $length; $i++ )  {
	     $str.= substr($chars, mt_rand(0, strlen($chars)-1), 1);
	 }
	 return $str;
	}

	//格式化字符串
	function formatQueryParaMap($paraMap, $urlencode)
	{
	 $buff = "";
	 ksort($paraMap);
	 foreach ($paraMap as $k => $v){
	     if (null != $v && "null" != $v && "sign" != $k) {
	         if($urlencode){
	            $v = urlencode($v);
	         }
	         $buff .= $k . "=" . $v . "&";
	     }
	 }
	 $reqPar;
	 if (strlen($buff) > 0) {
	     $reqPar = substr($buff, 0, strlen($buff)-1);
	 }
	 return $reqPar;
	}

	//数组转XML
	function arrayToXml($arr)
	{
	 $xml = "<xml>";
	 foreach ($arr as $key=>$val)
	 {
	     if (is_numeric($val)){
	         $xml.="<".$key.">".$val."</".$key.">";
	     }else{
	          $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
	     }
	 }
	 $xml.="</xml>";
	 return $xml;
	}

	//将XML转为array
	function xmlToArray($xml)
	{    
	 //禁止引用外部xml实体
	 libxml_disable_entity_loader(true);
	 $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);        
	 return $values;
	}

	//带证书的post请求
	function http_request($url, $fields = null, $cert = true)
	{
	 $ch = curl_init();
	 curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	 curl_setopt($ch, CURLOPT_URL, $url);
	 curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
	 curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);
	 curl_setopt($ch, CURLOPT_SSLCERT, IMOOC.'public/resource/cert/apiclient_cert.pem');
	 curl_setopt($ch, CURLOPT_SSLKEY,  IMOOC.'public/resource/cert/apiclient_key.pem');
	 curl_setopt($ch, CURLOPT_CAINFO,  IMOOC.'public/resource/cert/rootca.pem'); //DIRECTORY_SEPARATOR
	 if (!empty($fields)){ 
	     curl_setopt($ch, CURLOPT_POST, 1);
		 curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
	 }
	 $data = curl_exec($ch);

	 if(!$data){echo "CURL ErrorCode: ".curl_errno($ch);}
	 curl_close($ch);
	 return $data;
	}
 }