<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-05-06 20:37:37
 * Desc: M星球设置（无分页 其它都有）
 */

namespace app\ctrl\admin;

use app\model\mxqsz as MxqszModel;
use app\ctrl\admin\BaseController;
use app\validate\IDMustBeRequire;
use app\validate\DelValidate;
use app\validate\MxqszValidate;

class mxqsz extends BaseController{
	
	public $mxqsz_M;
	public function __initialize(){
		$this->mxqsz_M = new MxqszModel();
	}

	/*按id查找*/
    public function edit(){
    	$id = post('id');
    	(new IDMustBeRequire())->goCheck();
    	$data = $this->mxqsz_M->find($id);
    	empty($data) && error('数据不存在',404);    	
        return $data;      
    }	

    /*保存*/
	public function saveadd(){
		(new MxqszValidate())->goCheck('scene_saveadd');
		$data = post(['title','ljrd','mtsc','gmid','piclink']);
		$res=$this->mxqsz_M->save($data);
		empty($res) && error('添加失败',400);	 
		admin_log('添加M星球设置',$res);  
		return $res;
	}

	/*按id删除*/
	public function del(){
		$id_str = post('id_str');
		(new DelValidate())->goCheck();
		$id_ar = explode('@',$id_str);
		$res=$this->mxqsz_M->del($id_ar);
		empty($res) && error('删除失败',400);
		admin_log('删除M星球设置',$id_str);  
		return $res;
	}

	/*按id修改*/
	public function saveedit()
	{	
		(new MxqszValidate())->goCheck('scene_saveedit');
		$id = post('id');	
    	$data = post(['title','ljrd','mtsc','gmid','piclink']);
		$res=$this->mxqsz_M->up($id,$data);
		empty($res) && error('修改失败',404);
		admin_log('修改M星球设置',$id);  
 		return $res; 
	}

	/*无分页列表*/
	public function lists()
	{
		$data=$this->mxqsz_M->lists_all();
		
		foreach($data as &$one){
			
			$vip_rating_M = new \app\model\vip_rating();
			$bid=$vip_rating_M->find($one['gmid'],'title');
			if(empty($bid)){
				$one['m_title'] ="不存在";
			}
			else{
				$one['m_title'] =$bid;
		     }
		}	 
        return $data; 
	}


}