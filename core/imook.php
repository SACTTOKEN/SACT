<?php 
/**
 * Created by yaaaaa_god
 * User: yaaaaa
 * Date: 2018/12/13
 * Desc: 主
 */
namespace core;

use core\lib\Response;
use core\lib\Exception;

class imooc{
	static public $classMap=array();
	static public function run(){
		try{       
			//设备
			$extra = isset($_SERVER['HTTP_EXTRA']) ? $_SERVER['HTTP_EXTRA'] : '';
			define('EXTRA',$extra); 
			//路由规则
			$route = new \core\lib\route();
			$app=$route->app;
			$ctrlClass=$route->ctrl;
			$action=$route->action;
		
			//如果有地址则使用定制所在地址的控制器方法
			$made=c('made');
			$is_made=false;
			if(!empty($made)){
				$ctrlfile = MADE . '/' . $made . '/ctrl/'.$app.'/' . $ctrlClass . '.php';
				$cltrlClass =  '\made\\'. $made . '\ctrl\\'.$app.'\\' . $ctrlClass;
				if (is_file($ctrlfile)) {
					include $ctrlfile;
					$ctrl=new $cltrlClass();
					if(!method_exists($ctrl,$action)){
						$is_made=true;
					}
				}else{
					$is_made=true;
				}
			}else{
				$is_made=true; 
			}

			if($is_made){
				$ctrlfile=APP.'/ctrl/'.$app.'/'.$ctrlClass.'.php';
				$cltrlClass='\\'.MODULE.'\ctrl\\'.$app.'\\'.$ctrlClass;
				if(!is_file($ctrlfile)){
					throw new Exception([
						'msg' => '查找不到控制器',
						'code' => 400
					]);
				}
				include $ctrlfile;
				$ctrl=new $cltrlClass();
			}
			
			//调用控制器
			if (method_exists($ctrl, '__initialize')) {
				$ctrl->__initialize();
			}
			if(!method_exists($ctrl,$action)){
				throw new Exception([
					'msg' => '查找不到方法',
					'code' => 400
				]);
			}
			$result=$ctrl->$action();
			$data = [
				'result' => $result,
				'status' => 1,
				'code' => 200
			];
		}catch(\Exception $e){
			$Exception=new Exception();
            $data = $Exception->render($e);
        }
		
		$Response=new Response();
		$Response->create($data);
	}
	
	static public function load($class){
		if(isset($classMap[$class])){
			return true;
		}else{
			$class=str_replace('\\','/',$class);
			$file=IMOOC.'/'.$class.'.php';
			if(is_file($file)){
				include $file;
				self::$classMap[$class]=$class;
			}else{
				return false;
			}
		}
	}
	
	
	public function assign($name,$value){
		$this->assign[$name]=$value;
	}
	
	public function display($file){
		$file=APP.'/views/'.$file;
		if(is_file($file)){
			extract($this->assign);
			include $file;
		}
	}
	
}
