<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-02-25 11:35:40
 * Desc: 代理商控制器
 */

namespace app\ctrl\admin;

use app\model\agent as agent_Model;
use app\ctrl\admin\BaseController;
use app\validate\agentValidate;
use app\validate\IDMustBeRequire;
use app\validate\AllsearchValidate;

class agent extends PublicController{
	
	public $agent_M;
	public $user_M;
	public function __initialize(){
		$this->agent_M = new agent_Model();
		$this->user_M = new \app\model\user();
	}

	/*按id查找*/
    public function edit(){
    	$id = post('id');
    	(new IDMustBeRequire())->goCheck();
    	$data = $this->agent_M->find($id);
		$data['username'] = user_info($data['uid'],'username');
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
		$data=$this->agent_M->lists($page,$page_size,$where);
		foreach($data as &$rs){
			$users=user_info($rs['uid']);
			$rs['username'] = $users['username'];
			$rs['nickname'] = $users['nickname'];
			$rs['avatar'] = $users['avatar'];
		}
		$count = $this->agent_M->new_count($where);
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
		$check_ar=$this->agent_M->find($id);
		$data=$this->user_M->find($check_ar['uid'],['agent_province','agent_city','agent_area','agent_town']);
		if(empty($data['agent_province'])){
			$data['agent_province']=$check_ar['province'];
		}
		if(empty($data['agent_city'])){
			$data['agent_city']=$check_ar['city'];
		}
		if(empty($data['agent_area'])){
			$data['agent_area']=$check_ar['area'];
		}
		if(empty($data['agent_town'])){
			$data['agent_town']=$check_ar['town'];
		}
		$data['id']=$id;
		$data['is_check']=$check_ar['is_check'];
		$data['types']=$check_ar['types'];
        return $data; 
    }
    
    public function savecheck()
    {
		(new IDMustBeRequire())->goCheck();
        $id=post('id');
		$check_ar=$this->agent_M->find($id,['uid','is_check','types']);
        $is_check=post('is_check');
		$data['agent_province']=post('province');
		$data['agent_city']=post('city');
		$data['agent_area']=post('area');
		$data['agent_town']=post('town');
        $res=$this->agent_M->up($id,['is_check'=>$is_check]);
        $res=$this->user_M->up($check_ar['uid'],$data);
        if($is_check==1){

            $res=(new \app\model\user())->up($check_ar['uid'],['is_agent'=>$check_ar['types']]);
        }else{
            $res=(new \app\model\user())->up($check_ar['uid'],['is_agent'=>0]);
		}
        empty($res) && error("修改失败",404);
		admin_log('审核代理商申请',$id); 
        return $res; 
    }

	/*按id修改备注*/
	public function saveedit()
	{		
    	(new IDMustBeRequire())->goCheck();
    	$id = post('id');
    	$data = post(['remark']);
		$res=$this->agent_M->up($id,$data);
		empty($res) && error('修改失败',404);
		admin_log('修改代理商申请备注',$id);   
 		return $res;
	}


	/*删除*/
	public function del(){	
		(new \app\validate\DelValidate())->goCheck();
		$id_str = post('id_str');
		$id_ar = explode('@',$id_str);
		$res=$this->agent_M->del($id_ar);
		empty($res) && error('删除失败',400);
		admin_log('删除代理商申请',$id_str);   
		return $res;
	}

}