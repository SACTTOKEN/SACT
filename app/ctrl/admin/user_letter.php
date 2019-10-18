<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-02 09:52:31
 * Desc: 站内信控制器
 */

namespace app\ctrl\admin;

use app\model\user_letter as user_letter_Model;
use app\validate\UserLetterValidate;
use app\ctrl\admin\BaseController;
use app\validate\IDMustBeRequire;
use app\validate\AllsearchValidate;
use app\validate\DelValidate;


class user_letter extends BaseController{
	
	public $user_letter_M;
	public function __initialize(){
		$this->user_letter_M = new user_letter_Model();	
	}

    /*按id查找*/
    public function edit(){
        $id = post('id');
        (new IDMustBeRequire())->goCheck();
        $data = $this->user_letter_M->find($id);
        $data['username'] = user_info($data['uid'],'username');
        empty($data) && error('数据不存在',404);     
        return $data;      
    }   

	//添加 这里的uid 要靠会员账号求出来
    public function saveadd(){
        (new UserLetterValidate())->goCheck('scene_add');
        $username = post('username');
        $user_M = new \app\model\user();
        $uid = $user_M->find_uid($username);
        empty($uid) && error('该用户不存在',400);
        $data = post(['content','links']);   
        $data['uid'] = $uid; 
        $res=$this->user_letter_M->save($data);
        empty($res) && error('添加失败',400);    
		admin_log('发送站内信',$res);    
        return $res;
    }

    /*按id删除*/
    public function del(){
        $id_str = post('id_str');
        (new \app\validate\DelValidate())->goCheck();
        $id_ar = explode('@',$id_str);
        $res=$this->user_letter_M->del($id_ar);
        empty($res) && error('删除失败',400);
        admin_log('删除站内信',$id_str);   
        return $res;
    }

    
    //修改
    public function saveedit(){       
        (new UserLetterValidate())->goCheck('scene_edit');       
        $id = post('id');
        $data = post(['uid','content','links']); 
        $res = $this->user_letter_M->up($id,$data);
        empty($res) && error('修改失败',400);
		admin_log('修改站内信',$id);    
        return $res;
    }

    //



    //按用户查
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
        $data=$this->user_letter_M->lists($page,$page_size,$where);
        foreach($data as &$rs){
            $users=user_info($rs['uid']);
            $rs['username'] = $users['username'];
            $rs['nickname'] = $users['nickname'];
        }
        $count = $this->user_letter_M->new_count($where);
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data; 
        return $res; 
    }

	
//================= 以上是基础方法 ==================

}