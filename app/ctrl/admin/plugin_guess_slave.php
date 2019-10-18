<?php
namespace app\ctrl\admin;

use app\model\plugin_guess_slave as PluginGuessSlaveModel;
use app\validate\IDMustBeRequire;

class plugin_guess_slave extends BaseController{
	
	public $guess_slave_M;
	public function __initialize(){
		$this->guess_slave_M = new PluginGuessSlaveModel();
	}

	/*按id查找*/
    public function find(){
    	$id = post('id');
    	(new IDMustBeRequire())->goCheck();
    	$data = $this->guess_slave_M->find($id);
    	empty($data) && error('数据不存在',404);    	
        return $data;      
    }	

  

	/*按id删除*/
	public function del(){
		$id = post('id');
		(new newsValidate())->goCheck('scene_find');
		$res=$this->guess_slave_M->del($id);
		empty($res) && error('删除失败',400);
		admin_log('删除猜猜乐流水',$id);    
		return $res;
	}

	/*按id修改*/
	public function saveedit()
	{	
		$id = post('id');
    	(new newsValidate())->goCheck('scene_find');
    	$data = post(['is_end','earn']);
		$res=$this->guess_slave_M->up($id,$data);
		empty($res) && error('修改失败',404);
		admin_log('修改猜猜乐流水',$id);    
 		return $res; 
	}

	/*查某一类*/
	public function lists()
	{
		(new \app\validate\AllsearchValidate())->goCheck();
		(new \app\validate\PageValidate())->goCheck();
		$where=[];
		$page=post("page",1);
		$page_size = post("page_size",10);

		$stage = post('stage','');
		$username = post('username','');
		$nickname = post('nickname','');

		if($stage){
			$where['stage[~]'] = $stage;
		}
		if($username){
        	$user_M = new \app\model\user();
        	$where['uid'] = $user_M->find_mf_uid($username);
        }
        if($nickname){
        	$user_M = new \app\model\user();
        	$where['uid'] = $user_M->find_mf_uid_plus($nickname);
        }

		$data=$this->guess_slave_M->lists($page,$page_size,$where);

		foreach($data as &$rs){
			$users=user_info($rs['uid']);
			$rs['username'] = $users['username'];		
			$rs['nickname'] = $users['nickname'];
		}

		$count = $this->guess_slave_M->new_count($where);
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