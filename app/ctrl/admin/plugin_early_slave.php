<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2018-12-18 13:50:53
 * Desc: 早起签到副表
 */

namespace app\ctrl\admin;

use app\model\plugin_early_lord as PluginEarlyLordModel;
use app\model\plugin_early_slave as PluginEarlySlaveModel;
use app\validate\IDMustBeRequire;

class plugin_early_slave extends BaseController{
	
	public $early_lord_M;
	public $early_slave_M;
	public function __initialize(){
		$this->early_lord_M = new PluginEarlyLordModel();
		$this->early_slave_M = new PluginEarlySlaveModel();
	}

	/*按id查找*/
    public function find(){
    	$id = post('id');
    	(new IDMustBeRequire())->goCheck();
    	$data = $this->early_slave_M->find($id);
    	empty($data) && error('数据不存在',404);    	
        return $data;      
    }	

    /*保存*/
	public function saveadd(){
		$data = post(['stage','uid','stake']);
		(new NewsValidate())->goCheck('scene_add');
		$res=$this->early_slave_M->save_by_oid($data);
		empty($res) && error('添加失败',400);	 
		admin_log('添加早起签到附表',$res);   
		return $res;
	}

	/*按id删除*/
	public function del(){
		$id = post('id');
		(new newsValidate())->goCheck('scene_find');
		$res=$this->early_slave_M->del($id);
		empty($res) && error('删除失败',400);
		admin_log('删除早起签到附表',$id);   
		return $res;
	}

	/*按id修改*/
	public function saveedit()
	{	
		$id = post('id');
    	(new newsValidate())->goCheck('scene_find');
    	$data = post(['is_end','earn']);
		$res=$this->early_slave_M->up($id,$data);
		empty($res) && error('修改失败',404);
		admin_log('修改早起签到附表',$id);   
 		return $res; 
	}

	/*查某一类*/
	public function lists()
	{
		(new \app\validate\AllsearchValidate())->goCheck();
		(new \app\validate\PageValidate())->goCheck();
		$where=[];
		$stage = post('stage');
		$username = post('username');
		$nickname = post('nickname');
		$oid = post('oid');

		if($username){
        	$user_M = new \app\model\user();
        	$where['uid'] = $user_M->find_mf_uid($username);
        }
        if($nickname){
        	$user_M = new \app\model\user();
        	$where['uid'] = $user_M->find_mf_uid_plus($nickname);
        }
		if($stage){$where['stage[~]'] = $stage;}
		if($oid){$where['oid[~]'] = $oid;}
		$page=post("page",1);
		$page_size = post("page_size",10);
		
		$data=$this->early_slave_M->lists($page,$page_size,$where);

		foreach($data as &$one){
			$users=user_info($one['uid']);
			$one['username'] = $users['username'];
			$one['nickname'] = $users['nickname'];
		}

		$count = $this->early_slave_M->new_count($where);
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data; 
        // var_dump($this->product_review_M->log());
        // exit();
        return $res; 
	}

//================= 以上是基础方法 ==================



}