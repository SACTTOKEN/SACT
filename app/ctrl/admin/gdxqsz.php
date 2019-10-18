<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-05-06 20:37:37
 * Desc: 攻打星球设置（无分页 其它都有）
 */

namespace app\ctrl\admin;

use app\model\gdxqsz as GdxqszModel;
use app\ctrl\admin\BaseController;
use app\validate\IDMustBeRequire;
use app\validate\DelValidate;
use app\validate\GdxqszValidate;

class gdxqsz extends BaseController{
	
	public $gdxqsz_M;
	public function __initialize(){
		$this->gdxqsz_M = new GdxqszModel();
	}

	/*按id查找*/
    public function edit(){
    	$id = post('id');
    	(new IDMustBeRequire())->goCheck();
    	$data = $this->gdxqsz_M->find($id);
    	empty($data) && error('数据不存在',404);    	
        return $data;      
    }	

    /*保存*/
	public function saveadd(){
		(new GdxqszValidate())->goCheck('scene_saveadd');
		$data = post(['title','piclink']);
		$res=$this->gdxqsz_M->save($data);
		empty($res) && error('添加失败',400);	 
		admin_log('添加攻打星球设置',$res);  
		return $res;
	}

	/*按id删除*/
	public function del(){
		$id_str = post('id_str');
		(new DelValidate())->goCheck();
		$id_ar = explode('@',$id_str);
		$res=$this->gdxqsz_M->del($id_ar);
		empty($res) && error('删除失败',400);
		admin_log('删除攻打星球设置',$id_str);  
		return $res;
	}

	/*按id修改*/
	public function saveedit()
	{	
		(new GdxqszValidate())->goCheck('scene_saveedit');
		$id = post('id');	
    	$data = post(['title','piclink']);
		$res=$this->gdxqsz_M->up($id,$data);
		empty($res) && error('修改失败',404);
		admin_log('修改攻打星球设置',$id);  
 		return $res; 
	}

	/*无分页列表*/
	public function lists()
	{
		$data=$this->gdxqsz_M->lists_all();
        return $data; 
	}


}