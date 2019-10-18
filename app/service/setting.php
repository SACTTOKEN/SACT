<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-14 11:38:07
 * Desc: 配置类
 */
namespace app\service;

use app\model\config as ConfigModel;
use core\lib\redis;
use core\lib\model;
class setting{

	public $configM;
	public $redis;
	public function __construct(){
		$this->configM = new ConfigModel();	
		$this->redis = new redis();
		$this->model = new model();
	}
	
	/*添加配置*/
	public function index(){
		$this->config();
		$this->table();
		die('完成');
	}


	//配置
	public function C($title,$iden,$value,$types,$cate,$help,$yz=0){
		$data = [
			'title' => $title,
			'iden'  => $iden,
			'value' => $value,
			'types' => $types,
			'cate'  => $cate,
			'help'  => $help,	
			'yz'  => $yz,	
		];
		
		$res=$this->configM->is_find($data['iden']);
		if($res){
			$editdata= [
				'title' => $title,
				'types' => $types,
				'cate'  => $cate,
				'help'  => $help,	
				'yz'  => $yz,	
			];
			$res=$this->configM->up($data['iden'],$editdata);
			echo '<p>配置'.$data['iden'].'已存在</p>';
			return false;
		}else{
			$res=$this->configM->save($data);
			if(!$res){
				echo '<p>配置'.$data['iden'].'添加失败</p>';	
				return false;
			} 
			$key = 'config:'.$data['iden'];
			$val = $data['value'];
					$this->redis->set($key,$val);
			echo '<p style="color:#c00">配置'.$data['iden'].'添加成功</p>';
			return true;
		}
		
	}

	/*新增字段
	$types v:字符串  i:数字   d:浮点
	$this->add('表明','字段名','类型','默认值','长度','注释');
	*/
	public function add($table,$name,$types='v',$comment='',$default=0)
	{
		$res=$this->model::$medoo->query("select * from INFORMATION_SCHEMA.COLUMNS where table_name = '".$table."' and column_name = '".$name."'")->fetchAll();
		if($res){
			echo '<p>'.$table.'字段'.$name.'已存在</p>';	
			return false;
		}
		switch($types)
		{
		case "v":
			if($default==0){
				$default="''";
			}
			$sql="alter table ".$table." add column ".$name." VARCHAR(255) DEFAULT ".$default." not null COMMENT '".$comment."'";
			break;
		case "i":
			$sql="alter table ".$table." add column ".$name." int(11) DEFAULT ".$default." not null COMMENT '".$comment."'";
			break;
		case "s":
			$sql="alter table ".$table." add column ".$name." tinyint(1) DEFAULT ".$default." not null COMMENT '".$comment."'";
			break;
		case "d":
			$sql="alter table ".$table." add column ".$name." decimal(18,8) DEFAULT ".$default." not null COMMENT '".$comment."'";
			break;
			
		case "t":
			if($default==0){
				$default="''";
			}
			$sql="alter table ".$table." add column ".$name." text DEFAULT ".$default." not null COMMENT '".$comment."'";
			break;
		default:
			echo '<p>'.$table.'字段'.$name.'类型错误</p>';	
			return false;
		}
		$this->model::$medoo->query($sql);
		echo '<p style="color:#c00">'.$table.'字段'.$name.'成功</p>';
		return true;
	}

	/*建表
	$this->cj('表明','注释');
	*/
	public function cj($table,$comment){
		$res=$this->model::$medoo->query("SELECT table_name FROM information_schema.TABLES WHERE table_name ='".$table."'")->fetchAll();
		
		if($res){
			echo '<p>创建表'.$table.'已存在</p>';	
			return false;
		}
		$sql="CREATE TABLE `".$table."` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`created_time` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
			`update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
			PRIMARY KEY (`id`)
		  ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='".$comment."';";
		$this->model::$medoo->query($sql);
		echo '<p style="color:#c00">创建表'.$table.'成功</p>';
		return true;
	}
}