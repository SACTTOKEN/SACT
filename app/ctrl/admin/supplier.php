<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-02-25 11:35:40
 * Desc: 供应商控制器
 */

namespace app\ctrl\admin;

use app\model\supplier as supplier_Model;
use app\ctrl\admin\BaseController;
use app\validate\SupplierValidate;
use app\validate\IDMustBeRequire;
use app\validate\AllsearchValidate;

class supplier extends PublicController{
	
	public $supplier_M;
	public $user_attach_M;
	public function __initialize(){
		$this->supplier_M = new supplier_Model();
		$this->user_attach_M = new \app\model\user_attach();
	}

	/*按id查找*/
    public function edit(){
    	$id = post('id');
    	(new SupplierValidate())->goCheck('scene_find');
    	$data = $this->supplier_M->find($id);
		$data['username'] = user_info($data['uid'],'username');
		$data['image']=(new \app\model\image())->list_cate('supplier',$data['id']);
    	empty($data) && error('数据不存在',404);    	
        return $data;      
    }	

	/*查所有*/
	public function lists()
	{
	    (new AllsearchValidate())->goCheck();
	    (new \app\validate\PageValidate())->goCheck();
	    $where = [];
        $username = post('username');
        $nickname = post('nickname');

        $uid   = post('uid');
        $is_check = post('is_check'); //0未审 1已审
        $created_time_begin = post('created_time_begin'); 
        $created_time_end   = post('created_time_end'); 

 
        if(is_numeric($uid)){
            $where['uid'] = $uid;
        }
        if($username){
            $user_M = new \app\model\user();
            $uid = $user_M->find_mf_uid($username);
            $where['uid'] = $uid;
        }
        if($nickname){
        	$user_M = new \app\model\user();
            $uid = $user_M->find_mf_uid_plus($nickname);
            $where['uid'] = $uid;
        }

        if(is_numeric($is_check)){
            $where['is_check'] = $is_check;
        }

        if($created_time_begin>0){
			$created_time_end = $created_time_end ? $created_time_end : time();
			$created_time_end = $created_time_end + 3600*24;
            $where['created_time[<>]'] = [$created_time_begin,$created_time_end];
        }

		$page=post("page",1);
		$page_size = post("page_size",10);		
		$data=$this->supplier_M->lists($page,$page_size,$where);
		foreach($data as &$rs){
			$users=user_info($rs['uid']);
			$rs['username'] = $users['username'];
			$rs['nickname'] = $users['nickname'];
			$rs['avatar'] = $users['avatar'];
		}
		$count = $this->supplier_M->new_count($where);
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data; 
        return $res; 
	}


	public function check()
	{
        (new IDMustBeRequire())->goCheck();
		$id=post('id');
		$check_ar=$this->supplier_M->find($id);
		$data=$this->user_attach_M->find($check_ar['uid'],['shop_title','shop_logo','shop_tel','shop_province','shop_city','shop_area','shop_town','shop_address','shop_cate','shop_longitude','shop_latitude','shop_fee','shop_referrer']);
		if(empty($data['shop_province'])){
			$data['shop_province']=$check_ar['province'];
		}
		if(empty($data['shop_city'])){
			$data['shop_city']=$check_ar['city'];
		}
		if(empty($data['shop_area'])){
			$data['shop_area']=$check_ar['area'];
		}
		if(empty($data['shop_town'])){
			$data['shop_town']=$check_ar['town'];
		}
		if(empty($data['shop_address'])){
			$data['shop_address']=$check_ar['add'];
		}
		$data['id']=$id;
		$data['is_check']=$check_ar['is_check'];
        return $data; 
    }
    
    public function savecheck()
    {
		(new IDMustBeRequire())->goCheck();
		(new \app\validate\UserAttachValidate())->goCheck('supplier');
        $id=post('id');
		$check_ar=$this->supplier_M->find($id,['uid','is_check']);
        $is_check=post('is_check');
        $data=post(['shop_title','shop_logo','shop_tel','shop_province','shop_city','shop_area','shop_town','shop_address','shop_cate','shop_longitude','shop_latitude','shop_fee','shop_referrer']);
        $res=$this->supplier_M->up($id,['is_check'=>$is_check]);
        $res=$this->user_attach_M->up($check_ar['uid'],$data);
		$res=(new \app\model\user())->up($check_ar['uid'],['is_supplier'=>$is_check]);
        empty($res) && error("修改失败",404);
		admin_log('审核供应商申请',$id); 
        return $res; 
    }

	/*按id修改备注*/
	public function saveedit()
	{		
    	(new IDMustBeRequire())->goCheck();
    	$id = post('id');
    	$data = post(['remark']);
		$res=$this->supplier_M->up($id,$data);
		empty($res) && error('修改失败',404);
		admin_log('修改供应商申请备注',$id);   
 		return $res;
	}


	/*删除*/
	public function del(){	
		(new SupplierValidate())->goCheck('scene_del');
		$id_str = post('id_str');
		$id_ar = explode('@',$id_str);
		$res=$this->supplier_M->del($id_ar);
		empty($res) && error('删除失败',400);
		admin_log('删除供应商申请',$id_str);   
		return $res;
	}

}