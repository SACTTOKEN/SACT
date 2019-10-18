<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-05-06 20:37:37
 * Desc: 静态团队奖（无分页 其它都有）
 */

namespace app\ctrl\admin;

use app\model\user_jttdj as UserjttdjModel;
use app\ctrl\admin\BaseController;
use app\validate\IDMustBeRequire;
use app\validate\DelValidate;
use app\validate\UserjttdjValidate;

class user_jttdj extends BaseController{
	
	public $user_jttdj_M;
	public function __initialize(){
		$this->user_jttdj_M = new UserjttdjModel();
	}

	/*按id查找*/
    public function edit(){
    	$id = post('id');
    	(new IDMustBeRequire())->goCheck();
    	$data = $this->user_jttdj_M->find($id);
    	empty($data) && error('数据不存在',404);    	
        return $data;      
    }	

    /*保存*/
	public function saveadd(){
		(new UserjttdjValidate())->goCheck('scene_saveadd');
		$data = post(['zt_num','team_award']);
		$res=$this->user_jttdj_M->save($data);
		empty($res) && error('添加失败',400);	 
		admin_log('添加团队奖',$res);  
		return $res;
	}

	/*按id删除*/
	public function del(){
		$id_str = post('id_str');
		(new DelValidate())->goCheck();
		$id_ar = explode('@',$id_str);
		$res=$this->user_jttdj_M->del($id_ar);
		empty($res) && error('删除失败',400);
		admin_log('删除团队奖',$id_str);  
		return $res;
	}

	/*按id修改*/
	public function saveedit()
	{	
		(new UserjttdjValidate())->goCheck('scene_saveedit');
		$id = post('id');	
    	$data = post(['zt_num','team_award']);
		$res=$this->user_jttdj_M->up($id,$data);
		empty($res) && error('修改失败',404);
		admin_log('修改团队奖',$id);  
 		return $res; 
	}

	/*无分页列表*/
	public function lists()
	{
		$data=$this->user_jttdj_M->lists_all();
        return $data; 
	}


}