<?php
/**
 * @Author:      GOD
 * @DateTime:    2018-12-13 10:22:23
 * @Description: RSA实用工具 
 */
namespace core\lib;

use core\lib\Config;

class RSAUtils
{
	/**
	 * 私钥解密
	 * 
	 * $data  加密字符串，应该是一个经过base64编码的字符串
	 * 
	 * $keyorpath 如果不想使用默认key，请传递此参数指明新的key位置或传递不包含头尾的单行的key
	 * 
	 * 成功返回明文，失败返回null
	 */
	public static function decrypt($data,$keyorpath='')
	{
		
		if (!is_string($data)) {
			return null;
		}
		
		$pi_key =  self::getPrivateKey($keyorpath);
		
		return openssl_private_decrypt(base64_decode($data),$decrypted,$pi_key) ? $decrypted : null;
	}
	
	/**
	 * 公钥加密
	 * 
	 * @param $data 明文字符串
	 * 
	 * $keyorpath 如果不想使用默认key，请传递此参数指明新的key位置或传递不包含头尾的单行的key
	 * 
	 * @return string 成功返回加密字符串，失败返回null
	 */
	public static function encrypt($data,$keyorpath='')
	{
		
		if (!is_string($data)) {
			return null;
		}
		
		$pu_key = self::getPublicKey($keyorpath);
		
		return openssl_public_encrypt($data,$encrypted,$pu_key) ? $encrypted : null;
		
	}
	
	/**
	 * 获取私钥
	 *
	 *$keyorpath 如果不想使用默认key，请传递此参数指明新的key位置或传递不包含头尾的单行的key
	 *
	 * @return resource
	 */
	public static function getPrivateKey($keyorpath='')
	{
		
		if(!empty($keyorpath))
		{
			if(is_file($keyorpath))
			{
				$private_key= $path;
			}else
			{
				$res = "-----BEGIN RSA PRIVATE KEY-----\n" .
						wordwrap($keyorpath, 64, "\n", true) .
						"\n-----END RSA PRIVATE KEY-----";
						return $res;
			}
			
		}else 
		{

			//$private_key = require ROOT_PATH.'/public/static/rsa/private_key.php';
			$private_key = config::all("private_key");
		}
		
		return openssl_pkey_get_private($private_key);
	}
	
	/**
	 * 获取公钥
	 * 
	 * $keyorpath 如果不想使用默认key，请传递此参数指明新的key位置或传递不包含头尾的单行的key
	 * 
	 * @return resource
	 */
	public static function getPublicKey($keyorpath='')
	{
		
		if(!empty($keyorpath))
		{
			
			if(is_file($keyorpath))
			{
				$public_key = $path;
			}else 
			{
				$res = "-----BEGIN RSA PRIVATE KEY-----\n" .
						wordwrap($keyorpath, 64, "\n", true) .
						"\n-----END RSA PRIVATE KEY-----";
				return $res;
			}
			
		}else
		{
			//$public_key = require ROOT_PATH.'/public/static/rsa/public_key.php';
			$public_key = config::all("public_key");

		}
		
		return openssl_pkey_get_public($public_key);
	}
	
	/**
	 * 创建基于RSA2的签名,注意RSA密钥长度最少应为2048
	 * 
	 * $data 字符串
	 * 
	 * $keyorpath 如果不想使用默认key，请传递此参数指明新的key位置或传递不包含头尾的单行的key
	 * 
	 * 成功返回签名信息，失败返回null
	 */
	public static function RSA2_CreateSign($data,$keyorpath='')
	{
		
		if (!is_string($data)) {
			return null;
		}
		
		return openssl_sign($data, $sign, self::getPrivateKey($keyorpath),OPENSSL_ALGO_SHA256 ) ? base64_encode($sign) : null;
		
	}
	
	/**
	 * 验证签名,基于RSA2的签名
	 * 
	 * $data 数据，字符串
	 * 
	 * $sign 签名，字符串
	 * 
	 * $keyorpath 如果不想使用默认key，请传递此参数指明新的key位置或传递不包含头尾的单行的key
	 * 
	 * 成功返回true，失败返回false
	 */
	public static function RSA2_verifySign($data = '', $sign = '',$keyorpath = '')
	{
		if (!is_string($sign) || !is_string($sign)) {
			return false;
		}
		return (bool)openssl_verify($data,base64_decode($sign),self::getPublicKey($keyorpath),OPENSSL_ALGO_SHA256);
	}
	
}