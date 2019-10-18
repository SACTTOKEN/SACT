<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-14 11:38:07
 * Desc: 管理员角色类
 */
namespace app\ctrl\admin;
use app\validate\IDMustBeRequire;

class supplier_promotion extends PublicController{

	public $user_attach_M;
	public $supplier_promotion_M;
	public function __initialize(){
		$this->user_attach_M = new \app\model\user_attach();
		$this->supplier_promotion_M = new \app\model\supplier_promotion();
    }

    public function lists()
    {
        (new \app\validate\AllsearchValidate())->goCheck();
	    (new \app\validate\PageValidate())->goCheck();
	    $where = [];
        $username = post('username');
        $title = post('title');
       
        $is_check = post('is_check'); //0未审 1已审
        $created_time_begin = post('created_time_begin'); 
        $created_time_end   = post('created_time_end'); 

 
        if($username){
            $user_M = new \app\model\user();
            $uid = $user_M->find_mf_uid($username);
            $where['sid'] = $uid;
        }
        if($title){
        	$product_M = new \app\model\product();
            $pid = $product_M->have(['title[~]'=>$title],'id');
            $where['pid'] = $pid;
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
		$data=$this->supplier_promotion_M->lists($page,$page_size,$where);
		foreach($data as &$rs){
			$users=user_info($rs['sid']);
			$rs['username'] = $users['username'];
			$rs['nickname'] = $users['nickname'];
            $rs['avatar'] = $users['avatar'];
        	$product_M = new \app\model\product();
            $product_ar = $product_M->find($rs['pid'],['title','piclink']);
			$rs['title'] = $product_ar['title'];
            $rs['piclink'] = $product_ar['piclink'];
           
		}
        $count = $this->supplier_promotion_M->new_count($where);
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
        $res=$this->supplier_promotion_M->up($id,$data);
        empty($res) && error("修改失败",404);
		admin_log('供应商投诉修改',$id); 
        return $res; 
    }
	
    
	/*批量删除*/
    public function del_all(){
    	(new \app\validate\DelValidate())->goCheck();
    	$id_str = post('id_str');
    	$id_ar = explode('@',$id_str);
    	$res=$this->supplier_promotion_M->del($id_ar);
    	empty($res) && error('删除失败',400);		
		admin_log('删除投诉',$id_str);   
    	return $res;
    }


    
}