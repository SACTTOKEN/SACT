<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-18 13:50:53
 * Desc: 支付接口控制器
 */

namespace app\ctrl\admin;

use app\validate\IDMustBeRequire;
use app\model\pay as PayModel;
use app\ctrl\admin\BaseController;
use app\validate\PayValidate;

class pay extends BaseController{
	
	public $payM;
	public function __initialize(){
		$this->payM = new PayModel();
	}

	/*按id查找*/
    public function edit(){
    	$id = post('id');
    	(new IDMustBeRequire())->goCheck();
    	$data = $this->payM->find($id);
    	empty($data) && error('数据不存在',404);    	
        return $data;      
    }

	/*按title查找*/
    public function find_by_title(){
    	$title = post('title');
    	(new PayValidate())->goCheck('scene_find');
    	$data = $this->payM->find($title);
    	empty($data) && error('数据不存在',404);    	
        return $data;      
    }	

    /*保存*/
	// public function saveadd(){
	// 	$data = post(['title','username','key','app_id','app_secret','piclink','link','content','show','sort']);
	// 	(new PayValidate())->goCheck('scene_add');
	// 	$res=$this->payM->save($data);
	// 	empty($res) && error('添加失败',400);
	// 	return $res;
	// }

	/*按id修改 不接受title,title不能修改*/
	public function saveedit()
	{	
		$id = post('id');
    	(new PayValidate())->goCheck('scene_edit');
    	$data = post(['title','username','key','app_id','app_secret','piclink','link','content','show','sort']);
		$res=$this->payM->up($id,$data);
		empty($res) && error('修改失败',404);
		admin_log('修改支付配置',$id);   
 		return $res; 
	}

	/*配置列表 无分页*/
	public function lists()
	{				
		$data=$this->payM->lists_all();
        return $data; 
	}

	/*是否开通*/
	public function show()
	{
		(new IDMustBeRequire())->goCheck();	
		$id=post("id");
		$data['show'] = post('show');
		$res=$this->payM->up($id,$data);
		empty($res) && error('修改失败',404);
		$title = $this->payM->find($id,'title');
		if($data['show']!=1){
		admin_log('禁用',$title);  
		}else{
		admin_log('启用',$title); 	
		}
 		return $res; 
	}

//================= 以上是基础方法 ==================


}