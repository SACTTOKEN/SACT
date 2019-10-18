<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-01-31 11:18:00
 * Desc: 奖励名称控制器
 */

namespace app\ctrl\admin;

use app\model\reward as RewardModel;
use app\validate\RewardValidate;
use app\validate\IDMustBeRequire;

class reward extends BaseController{
	
	public $rewardM;
	public function __initialize(){
		$this->rewardM  = new RewardModel();
	}

	/*按id查找*/
    public function edit(){
    	(new IDMustBeRequire())->goCheck();
    	$id = post('id');
    	$data = $this->rewardM->find($id);
    	empty($data) && error('数据不存在',404);    	
        return $data;      
    }	

    /*保存*/
	public function saveadd(){
		(new RewardValidate())->goCheck('scene_add');
		$data = post(['iden','title','types','show']);
		$res=$this->rewardM->save($data);
		empty($res) && error('添加失败',400);
		admin_log('添加奖励',$res);	 
		return $res;
	}

	/*按id删除*/
	public function del(){
		(new IDMustBeRequire())->goCheck();
		$id = post('id');
		$data = $this->rewardM->find($id);

		$redis = new \core\lib\redis();
		$key = 'reward:'.$data['iden'];
		$redis->del($key);

		$res=$this->rewardM->del($id);
		empty($res) && error('删除失败',400);
		admin_log('删除奖励',$id);


		return $res;
	}

	/*按id修改*/
	public function saveedit()
	{	
		(new IDMustBeRequire())->goCheck();
    	(new RewardValidate())->goCheck('scene_find');
		$id = post('id');
		$reward_ar=$this->rewardM->find($id);
    	$data = post(['iden','title','types','show']);		
		$res=$this->rewardM->up($id,$data);
		empty($res) && error('修改失败',404);
		if($reward_ar['title']!=$data['title']){
			$rating_M=new \app\model\rating();
			$rating_ar=$rating_M->lists_all();
			foreach($rating_ar as $vo){
				if(strstr($vo['flag'],'":"'.$reward_ar['title'].'"')){
					$flag=str_replace('":"'.$reward_ar['title'].'"','":"'.$data['title'].'"',$vo['flag']);
					$rating_M->up($vo['id'],['flag'=>$flag]);
				}
			}
		}

		$title = $this->rewardM->find($id,'title');
		$redis = new \core\lib\redis();
		$key = 'reward:'.$data['iden'];
		$redis->set($key,$title);
		
		admin_log('修改奖励',$id);
 		return $res; 
	}

	/*查列表*/
	public function lists()
	{	
		$data=$this->rewardM->lists_all();
        return $data; 
	}

	/*是否显示*/
	public function show()
	{
		(new IDMustBeRequire())->goCheck();	
		(new RewardValidate())->goCheck('scene_change');
		$id=post("id");
		$data['show'] = post('show');
		$res=$this->rewardM->up($id,$data);
		empty($res) && error('修改失败',404);

		$title = $this->rewardM->find($id,'title');
		if($data['show']!=1){
		admin_log('禁用奖励',$id);  
		}else{
		admin_log('启用奖励',$id); 	
		}
 		return $res; 
	}

	/*是否显示*/
	public function sms()
	{
		(new IDMustBeRequire())->goCheck();	
		$id=post("id");
		$data['is_sms'] = post('is_sms');
		$res=$this->rewardM->up($id,$data);
		empty($res) && error('修改失败',404);

		if($data['is_sms']!=1){
		admin_log('禁用奖励发消息',$id);  
		}else{
		admin_log('启用奖励发消息',$id); 	
		}
 		return $res; 
	}

	/*奖励类型*/
	public function option(){
		$data=$this->rewardM->option();
		return $data;
	}


	/*根据iden查奖励中文*/
	public function find_iden(){
		$iden = post('iden');
		$iden = $iden ? $iden : 'integral';
		$res = $this->rewardM->find_redis($iden);
		return $res;
	}


	/*奖励类型之金额类型*/
	public function option2(){
		$data=$this->rewardM->option2();
		return $data;
	}




}