<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-24 13:48:22
 * Desc: 用户留言控制器
 */

namespace app\ctrl\admin;

use app\model\user_msg as user_msg_Model;
use app\ctrl\admin\BaseController;
use app\validate\UserMsgValidate;
use app\validate\IDMustBeRequire;
use app\validate\AllsearchValidate;

class user_msg extends BaseController{
	
	public $user_attach_M;
	public function __initialize(){
		$this->user_msg_M = new user_msg_Model();
	}

	/*按id查找*/
    public function edit(){
    	$id = post('id');
    	(new UserMsgValidate())->goCheck('scene_find');
    	$data = $this->user_msg_M->find($id);
    	$data['username'] = user_info($data['uid'],'username');
    	empty($data) && error('数据不存在',404);    	
        return $data;      
    }	

	/*查用户所有留言*/
	public function lists()
	{
		(new AllsearchValidate())->goCheck();
		(new \app\validate\PageValidate())->goCheck();
        $where = [];

        $uid = post('uid');
        $username = post('username');
        $nickname = post('nickname');
        $content = post('content');
        $created_time_begin = post('created_time_begin');
        $created_time_end = post('created_time_end');

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

        if($content){
            $where['content[~]'] = $content;
        }

        if(is_numeric($created_time_begin)){
        	$created_time_end = $created_time_end ? $created_time_end : time();
			$created_time_end = $created_time_end + 3600*24;
            $where['created_time[<>]'] = [$created_time_begin,$created_time_end];   
        }

		$page=post("page",1);
		$page_size = post("page_size",10);		
		$data=$this->user_msg_M->lists($page,$page_size,$where);

		foreach($data as $key=>$rs){
			$users=user_info($rs['uid']);
			$data[$key]['username']  = $users['username']; //会员账号
			$data[$key]['avatar']  =  $users['avatar']; 
			$data[$key]['rating_cn'] = $users['rating_cn'];
			$data[$key]['nickname'] =  $users['nickname'];
		}

		$count = $this->user_msg_M->new_count($where);
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data; 
        return $res; 
	}

	/*审核留言*/
	public function check(){
		(new UserMsgValidate())->goCheck('scene_find');
		$id = post("id",'');
		$is_check = post('is_check',1);
		$res = $this->user_msg_M->check_msg($id,$is_check);
		admin_log('审核用户留言',$id);    
		return $res;
	}

	/*按id修改备注*/
	public function saveedit()
	{		
    	(new IDMustBeRequire())->goCheck();
    	$id = post('id');
    	$data = post(['remark']);
		$res=$this->user_msg_M->up($id,$data);
		empty($res) && error('修改失败',404);
		admin_log('修改用户留言备注',$id);    
 		return $res;
	}


	/*批量删除*/
	public function del(){	
		(new UserMsgValidate())->goCheck('scene_del');
		$id_str = post('id_str');
		$id_ar = explode('@',$id_str);
		$res=$this->user_msg_M->del($id_ar);
		empty($res) && error('删除失败',400);
		admin_log('删除用户留言',$id_str);    
		return $res;
	}
		
//================= 以上是基础方法 ==================

	

}