<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-14 11:38:07
 * Desc: 管理员角色类
 */
namespace app\ctrl\admin;
use app\validate\IDMustBeRequire;

class supplier_complaint extends PublicController{

	public $user_attach_M;
	public $supplier_complaint_M;
	public function __initialize(){
		$this->user_attach_M = new \app\model\user_attach();
		$this->supplier_complaint_M = new \app\model\supplier_complaint();
    }

    public function lists()
    {
        (new \app\validate\AllsearchValidate())->goCheck();
	    (new \app\validate\PageValidate())->goCheck();
	    $where = [];
        $username = post('username');
        $supplier = post('supplier');

        $is_check = post('is_check'); //0未审 1已审
        $created_time_begin = post('created_time_begin'); 
        $created_time_end   = post('created_time_end'); 

 
        if($username){
            $user_M = new \app\model\user();
            $uid = $user_M->find_mf_uid($username);
            $where['uid'] = $uid;
        }
        if($supplier){
        	$user_M = new \app\model\user();
            $sid = $user_M->find_mf_uid($supplier);
            $where['sid'] = $sid;
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
		$data=$this->supplier_complaint_M->lists($page,$page_size,$where);
		foreach($data as &$rs){
			$users=user_info($rs['uid']);
			$rs['username'] = $users['username'];
			$rs['nickname'] = $users['nickname'];
            $rs['avatar'] = $users['avatar'];
            
			$shop=user_info($rs['uid']);
			$rs['shop_username'] = $shop['username'];
			$rs['shop_nickname'] = $shop['nickname'];
			$rs['shop_avatar'] = $shop['avatar'];
		}
		$count = $this->supplier_complaint_M->new_count($where);
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data; 
        return $res; 
    }
    
    public function saveedit()
    {
        (new IDMustBeRequire())->goCheck();
        $id=post('id');
        $data=post(['is_check','remark']);
        $res=$this->supplier_complaint_M->up($id,$data);
        empty($res) && error("修改失败",404);
		admin_log('供应商投诉修改',$id); 
        return $res; 
    }
	
    
	/*批量删除*/
    public function del_all(){
    	(new \app\validate\DelValidate())->goCheck();
    	$id_str = post('id_str');
    	$id_ar = explode('@',$id_str);
    	$res=$this->supplier_complaint_M->del($id_ar);
    	empty($res) && error('删除失败',400);		
		admin_log('删除投诉',$id_str);   
    	return $res;
    }


    
}