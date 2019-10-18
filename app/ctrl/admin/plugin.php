<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-04-01 14:50:15
 * Desc: 插件大市场
 */
namespace app\ctrl\admin;

use app\model\plugin as PluginModel;
use app\ctrl\admin\BaseController;
use app\validate\IDMustBeRequire;
use app\validate\PluginValidate;

class plugin extends BaseController{
	public $plugin_M;
	public function __initialize(){
		$this->plugin_M = new PluginModel();
	}

	/*修改*/
	public function edit()
	{
		(new IDMustBeRequire())->goCheck();
		$id=post("id");
		$res=$this->plugin_M->find($id);
		empty($res) && error('数据不存在',404); 
		return $res;
	}
	

	/*按id删除*/
	public function del(){
		//error('如需删除,请联系管理员',400);
		$id = post('id');
		(new PluginValidate())->goCheck('scene_find');

		
		$iden=$this->plugin_M->find($id,'iden');
		$redis = new \core\lib\redis();
		$key = 'plugin:'.$iden;
		$redis->del($key);

		$res=$this->plugin_M->del($id);
		empty($res) && error('删除失败',400);
		admin_log('删除插件',$id);    
		return $res;
	}
	
	/*按id修改*/
	public function saveedit()
	{
		$id = post('id');
    	(new PluginValidate())->goCheck('scene_find');
    	$data = post(['title','cate','desc','price','pic','is_open','service','sort','url','links','video']);
		$res=$this->plugin_M->up($id,$data);
		//cs($this->plugin_M->log(),1);
		empty($res) && error('修改失败',404);
		admin_log('修改插件',$id);    
 		return $res; 
	}

	/*我的模块列表用*/
	public function lists()
	{
		$where = [];	
		$cate = post('cate');
		if($cate){
			$where['cate']= $cate;
		}else{
			$where['is_open']=1;
		}
		$data=$this->plugin_M->lists_all($where);
        return $data; 
	}

	/*后台插件列表用*/
	public function lists_plus(){
		$where = [];	
		$cate = post('cate');
		if($cate){
			$where['cate']= $cate;
		}
		$data=$this->plugin_M->lists_all($where);
        return $data;
	}


	/*是否开启*/
	public function open(){
		(new IDMustBeRequire())->goCheck();
		$id = post('id');
		$data['is_open'] = post('is_open');
		$res=$this->plugin_M->up($id,$data);
		if($data['is_open']==1){
		admin_log('开启插件',$id);
		}else{
		admin_log('关闭插件',$id);
		}    
		$iden=$this->plugin_M->find($id,'iden');
		$redis = new \core\lib\redis();
		$key = 'plugin:'.$iden;
		$redis->set($key,$data['is_open']);
		return $res;
	}



//================= 以上是基础方法 ==================

	/*插件分类*/
	public function plugin_cate(){
		return array(
			'1' => '拉新模块',
			'2' => '娱乐模块',
			'3' => '促销模块',
			'4' => '分销模块',
			'5' => '运营模块',
			'7' => '第三方接口',
			'8' => '拓展电商',
		);
	}


	public function cate_option(){
		$data = array(
			'1' => '拉新模块',
			'2' => '娱乐模块',
			'3' => '促销模块',
			'4' => '分销模块',
			'5' => '运营模块',
			'7' => '第三方接口',
			'8' => '拓展电商',
		);
		foreach($data as $key => $val){
			$option[$key]['value'] = $val;
			$option[$key]['label'] = $val;
		}

		$option = array_values($option);
		return $option;
	}


	public function test(){
		$iden = post('iden');
		$res = plugin_is_open($iden);
		return $res;
	}
	

}