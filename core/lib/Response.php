<?php
/**
 * Created by yaaaaa_god
 * User: yaaaaa
 * Date: 2018/12/13
 * Desc: 输出类
 */
namespace core\lib;
use core\lib\Config;

class Response
{

	public function create($data)
	{
		header('Content-Type:application/json; charset=utf-8');
		$data['code_desc'] = config::get("error",$data['code']);
		exit(@json_encode($data,JSON_UNESCAPED_UNICODE));
	}
}
