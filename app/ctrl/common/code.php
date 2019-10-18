<?php
/**
 * Created by yaaaaa_god
 * User: yaaaaa
 * Date: 2018/12/13
 * Desc: 二维码
 */
namespace app\ctrl\common;
use core\lib\Code as codes;

class code {
  
    public function index()
    {
        $codes = new codes();
        $unicode = get('unicode'); //唯一码由前端生成请求
        empty($unicode) && error('非法请求!',400);
        $codes->doimg($unicode);  
        exit();
    }
  
  }
   
