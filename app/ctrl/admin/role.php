<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-14 11:38:07
 * Desc: 管理员角色类
 */
namespace app\ctrl\admin;

use app\validate\IDMustBeRequire;
use app\model\role as RoleModel;
use app\validate\RoleValidate;

use app\ctrl\admin\BaseController;


class role extends BaseController{

	public function lists()
	{
		$roleM = new RoleModel();
		$data=$roleM->lists_all(['role_con[!]'=>'god']);
        return $data; 
	}


	/*下拉列表角色类 VUE格式*/
	public function option(){
		$roleM = new RoleModel();
		$data=$roleM->option();
		return $data;

	}

	/*所有*/
	public function lists_all()
	{
		$roleM = new RoleModel();
		$data=$roleM->lists_all(['role_con[!]'=>'god']);
        return $data; 
	}
	
	/*角色保存*/
	public function saveadd(){
		(new RoleValidate())->goCheck();
		$roleM = new RoleModel();
		$data = post(['role_name','role_con','role_desc','dis_show_con','dis_hanld_con']);
		$data['created_time'] = time();
		$data['update_time'] = time();
		if(is_array($data['role_con'])){
			$data['role_con'] = implode(',',$data['role_con']);
		}
		$res=$roleM->save($data);
		$new_id = $roleM->id();
		empty($res) && error('添加失败',400);
		admin_log('添加角色',$new_id);  
		return $res;
	}	
	
	/*修改角色*/
	public function edit(){
		(new IDMustBeRequire())->goCheck();
		
		$roleM = new RoleModel();
		$id=post("id");
		$res=$roleM->is_find($id);
		empty($res) && error('数据不存在',400); 
		
		$res=$roleM->find($id);
		empty($res) && error('查找失败',404);  
		return $res;
	}
	
	/*保存修改，同时修改admin表相同role_id对应的role_con*/
	public function saveedit(){
		(new IDMustBeRequire())->goCheck();
		$roleM = new RoleModel();
		$id=post("id");
		$res=$roleM->is_find($id);
		empty($res) && error('数据不存在',400); 
		
		$data = post(['role_name','role_con','role_desc','dis_show_con','dis_hanld_con']);
		$data['update_time'] = time();
		if(is_array($data['role_con'])){
			$data['role_con'] = implode(',',$data['role_con']);
		}
		$res=$roleM->up($id,$data);
		$admin_M = new \app\model\admin();
		if($data['role_con']=='kind'){
			$admin_M->up_role_con($id,'kind');
		}else{
			$ar = explode(',',$data['role_con']);
			$admin_M->up_role_con($id,$ar);
		}
		

		empty($res) && error('修改失败',404);
		admin_log('修改角色',$id); 
 		return $res; 
	}
	
	/*删除*/
	public function del(){
		(new IDMustBeRequire())->goCheck();
		
		$roleM = new RoleModel();
		$id=post("id");
		$res=$roleM->is_find($id);
		empty($res) && error('数据不存在',400);

		$adminM = new \app\model\admin();

		$is_have = $adminM-> is_have(["AND"=>['role_id'=>$id]]); //是否有相应角色的管理员
		if($is_have){
			error('请先删除属于该角色的管理员',400);
		}
	
		$res=$roleM->del($id);
		empty($res) && error('删除失败',404);
		admin_log('删除角色',$id);   
 		return $res; 
	}


	/*查找下级栏目ID*/
	public function findmenuid(){
		(new IDMustBeRequire())->goCheck();
		$id=post("id");
		$menu_M = new \app\model\menu();
		$res = $menu_M->tree_down_id($id);
		// var_dump($menu_M->log());
		// exit();
		return $res;

	}







}