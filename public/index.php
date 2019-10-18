<?php
/**
 * Created by yaaaaa_god
 * User: yaaaaa
 * Date: 2018/12/13
 * Desc: 入口文件
 */
define('IMOOC',dirname(dirname(realpath(__FILE__)))."/");   //框架所在目录
define('CORE',IMOOC.'/core');   //核心文件
define('APP',IMOOC.'/app');	//项目
define('MADE',IMOOC.'/made');	//项目
define('MODULE','app');
define('DEBUG',TRUE);  //调试模式TRUE  FALSE
define('TIMESTAMP',time());

      
include CORE.'/common/function.php';
include CORE.'/lib/medoo.php';
include CORE.'/imook.php';
spl_autoload_register('\core\imooc::load');

if(DEBUG){
	ini_set('display_errors','on');
}else{
	ini_set('display_errors','on');
}
\core\imooc::run()->send();
?>
