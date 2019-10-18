<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-18 13:50:53
 * Desc: 图片附件控制器
 */

namespace app\ctrl\supplier;

use app\model\image as ImageModel;
use app\validate\IDMustBeRequire;
use app\validate\ImageValidate;

class image extends BaseController{
	
	public $image_M;
	public function __initialize(){
		$this->image_M = new ImageModel();
	}

	

    /*保存*/
	public function saveadd(){
		$data = post(['aid','cate','piclink']);
		(new ImageValidate())->goCheck('scene_add');
		$res=$this->image_M->save($data);
		empty($res) && error('添加失败',400);
		admin_log('添加图片',$res);  
		return $res;
	}

	/*按id删除*/
	public function del(){
		$id = post('id');
		(new ImageValidate())->goCheck('scene_find');
		$res=$this->image_M->del($id);
		empty($res) && error('删除失败',400);
		admin_log('删除图片',$id);  
		return $res;
	}

	/*修改*/
	public function edit()
	{
		(new IDMustBeRequire())->goCheck();
		$id=post("id");
		$res=$this->image_M->find($id);
		empty($res) && error('数据不存在',404);
		return $res;
	}

	/*按id修改*/
	public function saveedit()
	{	
		$id = post('id');
    	(new ImageValidate())->goCheck('scene_find');
    	$data = post(['aid','cate','piclink']);
		$res=$this->image_M->up($id,$data);
		empty($res) && error('修改失败',404);
		admin_log('修改图片',$id);   
 		return $res; 
	}

	/*查某一类*/
	public function lists()
	{
		(new \app\validate\AllsearchValidate())->goCheck();
		(new ImageValidate())->goCheck('scene_lists');	
		$aid = post('aid','');
		$cate = post('cate','');
		$data=$this->image_M->list_cate($cate,$aid);
        return $data; 
	}

}