<?php
/**
 * Created by yaaaaa_god
 * User: yaaaaa
 * Date: 2018/12/13
 * Desc: 路由类
 */
namespace core\lib;

use core\lib\Exception;

class route
{
	public $app;
	public $ctrl;
	public $action;
	function __construct()
	{

		if (isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] != '/') {
			$path = $_SERVER['REQUEST_URI'];
			$patharr = explode("?", $path);
			$path = $patharr[0];
			$patharr = explode('/', trim($path, '/'));

			if (!($_SERVER['REQUEST_METHOD'] == 'GET' || $_SERVER['REQUEST_METHOD'] == 'POST')) {
				throw new Exception([
					'msg' => '不支持的请求方式',
					'code' => 400
				]);
			}

			if (count($patharr) > 3) {
				throw new Exception([
					'msg' => '路由规则错误1',
					'code' => 10001
				]);
			}

			if (count($patharr) < 2 || $patharr[0] != "common" || ($patharr[0] == "common" && !in_array($patharr[1], cc('action')))) {
				if (count($_GET) > 0) {
					throw new Exception([
						'msg' => '路由规则错误2',
						'code' => 10001
					]);
				}

				//ip白名单
				if (!DEBUG) {
					if (!in_array($_SERVER['REMOTE_ADDR'],cc('ip'))){
						error("无访问权限1", 401);
					}
				}

				//ip白名单
				/* if (!DEBUG) {
					$is_ip = 0;
					if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
						$arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
						$pos    =   array_search('unknown', $arr);
						if (false !== $pos) unset($arr[$pos]);
						$ip     =   trim($arr[0]);
						if (in_array($ip, cc('ip'))) {
							$is_ip = 1;
						}
					}
					if ($is_ip == 0 && isset($_SERVER['HTTP_CLIENT_IP'])) {
						$ip     =   $_SERVER['HTTP_CLIENT_IP'];
						if (in_array($ip, cc('ip'))) {
							$is_ip = 1;
						}
					}
					if ($is_ip == 0 && isset($_SERVER['REMOTE_ADDR'])) {
						$ip     =   $_SERVER['REMOTE_ADDR'];
						if (in_array($ip, cc('ip'))) {
							$is_ip = 1;
						}
					}

					if (!$is_ip) {
						error("无访问权限1", 401);
					}
				}
				*/
				if (isset($_SERVER["HTTP_REFERER"])) {
					$strurl   = str_replace("http://", "", $_SERVER["HTTP_REFERER"]);
					$strurl   = str_replace("https://", "", $strurl);
					$strurl   = str_replace("ws://", "", $strurl);
					$strdomain = explode("/", $strurl);
					$sourcehost    = $strdomain[0];
					// if (!in_array($sourcehost, cc('web_config'))) {
					// 	error("无访问权限2", 401);
					// }
				} else {
					if (!DEBUG) {
						error("无访问权限3", 401);
					}
				} 
			}
			foreach ($patharr as $vo) {
				if (1 !== preg_match('/^[A-Za-z0-9\-\_]+$/', (string)$vo)) {
					throw new Exception([
						'msg' => '路由规则错误3',
						'code' => 10001
					]);
				}
			}

			if (count($patharr) > 0) {
				if (isset($patharr[0])) {
					$this->app = $patharr[0];
					unset($patharr[0]);
				}
				if (isset($patharr[1])) {
					$this->ctrl = $patharr[1];
					unset($patharr[1]);
				} else {
					$this->ctrl = 'index';
				}
				if (isset($patharr[2])) {
					$this->action = $patharr[2];
					unset($patharr[2]);
				} else {
					$this->action = 'index';
				}
				
				$count=count($patharr)+3;
				$i=3;
				while($i<$count){
					if(isset($patharr[$i])){
					$_GET[$patharr[$i]]=$patharr[$i+1];
					}
					$i=$i+2;
				}
				
			} else {
				throw new Exception([
					'msg' => '路由规则错误4',
					'code' => 10001
				]);
			}
		} else {
			throw new Exception([
				'msg' => '路由规则错误5',
				'code' => 10001
			]);
		}
	}
}
