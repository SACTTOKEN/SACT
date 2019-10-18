<?php
/**
 * Created by yaaaaa_god
 * User: yaaaaa
 * Date: 2018/12/13
 * Desc: 配置类
 */
namespace core\lib;
class Config{

    static public $conf = array();

    static public function get($file,$name){
        /**
         * 1. 判断配置文件是否存在
         * 2. 判断对应配置是否存在
         * 3. 缓存配置
         */
        if(isset(self::$conf[$file])){
            return self::$conf[$file][$name];
        }else{
            $path = IMOOC.'/config/'.$file.'.php';
            if(is_file($path)){
                $conf = include $path;
                if(isset($conf[$name])){
                    self::$conf[$file] = $conf;
                    return $conf[$name];
                }else{
					error('没有这个配置项',404);
                }
            }else{
				error('找不到配置文件',404);
            }
        }
    }

   static public function all($file){
        if(isset(self::$conf[$file])){
            return self::$conf[$file];
        }else{
            $path = IMOOC.'/config/'.$file.'.php';
            if(is_file($path)){
                $conf = include $path;
                self::$conf[$file] = $conf;
                return $conf;    
            }else{
				throw new Exception([
					'msg' => '找不到配置文件',
					'code' => 404
				]);
            }
        }
    }
}