<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-05-06 20:37:37
 * Desc: 星球攻打结果（无分页 其它都有）
 */

namespace app\ctrl\admin;

use app\model\xqgdjg as XqgdjgModel;
use app\ctrl\admin\BaseController;
use app\validate\IDMustBeRequire;
use app\validate\DelValidate;
use app\validate\XqgdjgValidate;

class xqgdjg extends BaseController{
	
	public $xqgdjg_M;
	public function __initialize(){
		$this->xqgdjg_M = new XqgdjgModel();
	}

	/*按id查找*/
    public function edit(){
    	$id = post('id');
    	(new IDMustBeRequire())->goCheck();
    	$data = $this->xqgdjg_M->find($id);
    	empty($data) && error('数据不存在',404);    	
        return $data;      
    }	

    /*保存*/
	public function saveadd(){
		(new XqgdjgValidate())->goCheck('scene_saveadd');
		$data = post(['cdate','jcjg']);
		$res=$this->xqgdjg_M->save($data);
		empty($res) && error('添加失败',400);	 
		admin_log('添加星球攻打结果',$res);  
		return $res;
	}

	/*按id删除*/
	public function del(){
		$id_str = post('id_str');
		(new DelValidate())->goCheck();
		$id_ar = explode('@',$id_str);
		$res=$this->xqgdjg_M->del($id_ar);
		empty($res) && error('删除失败',400);
		admin_log('删除星球攻打结果',$id_str);  
		return $res;
	}

	/*按id修改*/
	public function saveedit()
	{	
		(new XqgdjgValidate())->goCheck('scene_saveedit');
		$id = post('id');	
    	$data = post(['cdate','jcjg']);
		$res=$this->xqgdjg_M->up($id,$data);
		empty($res) && error('修改失败',404);
		admin_log('修改星球攻打结果',$id);  
 		return $res; 
	}

	/*无分页列表*/
	public function lists()
	{
		
		
        (new \app\validate\AllsearchValidate())->goCheck();
		(new \app\validate\PageValidate())->goCheck();
		//$data=$this->xqgdjg_M->lists_all();
		
		$where = [];
		$created_time_begin = post('created_time_begin');
		$created_time_end = post('created_time_end');
		$created_time_end = intval($created_time_end);
		
		if($created_time_begin){
  			$created_time_end = $created_time_end + 3600*24;
			$where['cdate[<>]'] = [$created_time_begin,$created_time_end];
		}
		
		$page=post("page",1);
		$page_size = post("page_size",10);
		//$data=$this->xqgdjg_M->lists($page,$page_size,$where);
		$data=$this->xqgdjg_M->lists_sort($page,$page_size,$where,$order);
		foreach($data as &$one){
			
			$gdxqsz_M = new \app\model\gdxqsz();
			$bid=$gdxqsz_M->find($one['jcjg'],'title');
			if(empty($bid)){
				$one['m_title'] ="不存在";
			}
			else{
				$one['m_title'] =$bid;
		     }
		}	
		
		$count = $this->xqgdjg_M->new_count($where);
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data;  
		
        return $res; 
	}


}