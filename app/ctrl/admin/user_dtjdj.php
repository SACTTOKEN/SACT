<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-05-06 20:37:37
 * Desc: 见点奖励（无分页 其它都有）
 */

namespace app\ctrl\admin;

use app\model\user_dtjdj as UserdtjdjModel;
use app\ctrl\admin\BaseController;
use app\validate\IDMustBeRequire;
use app\validate\DelValidate;
use app\validate\UserdtjdjValidate;

class user_dtjdj extends BaseController{
	
	public $user_dtjdj_M;
	public function __initialize(){
		$this->user_dtjdj_M = new UserdtjdjModel();
	}

	/*按id查找*/
    public function edit(){
    	$id = post('id');
    	(new IDMustBeRequire())->goCheck();
    	$data = $this->user_dtjdj_M->find($id);
    	empty($data) && error('数据不存在',404);    	
        return $data;      
    }	

    /*保存*/
	public function saveadd(){
		(new UserdtjdjValidate())->goCheck('scene_saveadd');
		$data = post(['zt_num','team_award']);
		$res=$this->user_dtjdj_M->save($data);
		empty($res) && error('添加失败',400);	 
		admin_log('添加见点奖',$res);  
		return $res;
	}

	/*按id删除*/
	public function del(){
		$id_str = post('id_str');
		(new DelValidate())->goCheck();
		$id_ar = explode('@',$id_str);
		$res=$this->user_dtjdj_M->del($id_ar);
		empty($res) && error('删除失败',400);
		admin_log('删除见点奖',$id_str);  
		return $res;
	}

	/*按id修改*/
	public function saveedit()
	{	
		(new UserdtjdjValidate())->goCheck('scene_saveedit');
		$id = post('id');	
    	$data = post(['zt_num','team_award']);
		$res=$this->user_dtjdj_M->up($id,$data);
		empty($res) && error('修改失败',404);
		admin_log('修改见点奖',$id);  
 		return $res; 
	}

	/*无分页列表*/
	public function lists()
	{
		$data=$this->user_dtjdj_M->lists_all();
        return $data; 
	}


}