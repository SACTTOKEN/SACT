<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-07-25 09:16:58
 * Desc: 大转盘配置
 */

namespace app\ctrl\admin;

use app\model\big_wheel_config as pbw_config_Model;
use app\model\big_wheel_win as pbw_win_Model;

use app\ctrl\admin\BaseController;
use app\validate\IDMustBeRequire;
use app\validate\DelValidate;
use app\validate\BigWheelConfigValidate;

class plugin_big_config extends BaseController{
	
	public $pbw_config_M;
	public $pbw_win_M;

	public function __initialize(){
		$this->pbw_config_M = new pbw_config_Model();	
		$this->pbw_win_M = new pbw_win_Model();
	}

	//添加活动配置
	public function saveadd(){
		(new BigWheelConfigValidate())->goCheck('scene_add');
		$title = post('title');
		$begin_time = post('begin_time',0);
		$end_time = post('end_time',0);
		$readme = post('readme');
		$rating = post('rating');
		$is_only_rating = post('is_only_rating');
		$con = post('con');

		$model = new \core\lib\Model();
		$redis = new \core\lib\redis();  
		$model->action();
		$redis->multi();

		$data['title'] = $title;
		$data['begin_time'] = $begin_time;
		$data['end_time'] = $end_time;
		$data['readme'] = $readme;
		$data['rating'] = $rating;
		$data['is_only_rating'] = $is_only_rating;
		$data['con'] = $con;
		$data['not_win_say'] = post('not_win_say');
		$data['join_limit'] = post('join_limit');
		$ar = $this->pbw_config_M->save_by_oid($data);
		empty($ar) && error('添加失败1',400);


		$bid = $ar['id']; //活动ID
		$reward = post('reward');
		$reward = json_decode($reward,true);

		if($reward){
		foreach($reward as $key=>$one){
			$data_1['bid'] = $bid;
			$data_1['lv'] = $key+1;
			$data_1['rating'] = $one['rating'];
			$data_1['win_title'] = $one['win_title'];
			$data_1['score'] = $one['score'];
			$data_1['balance_type'] =  $one['balance_type'];
			$data_1['win_percent'] = $one['win_percent'];
			$data_1['win_say'] =  $one['win_say'];
			$res = $this->pbw_win_M->save($data_1);
			//cs($this->pbw_win_M->log(),1);
			empty($res) && error('添加失败2',400);
		}}
    	$model->run();
    	$redis->exec();
		return $res;
	}


	public function saveedit(){
		(new IDMustBeRequire())->goCheck();
		$id = post('id');
		$title = post('title');
		$begin_time = post('begin_time',0);
		$end_time = post('end_time',0);
		$readme = post('readme');
		$rating = post('rating');
		$is_only_rating = post('is_only_rating');
		$con = post('con');

		$model = new \core\lib\Model();
		$redis = new \core\lib\redis();  
		$model->action();
		$redis->multi();

		$data['title'] = $title;
		$data['begin_time'] = $begin_time;
		$data['end_time'] = $end_time;
		$data['readme'] = $readme;
		$data['rating'] = $rating;
		$data['is_only_rating'] = $is_only_rating;
		$data['con'] = $con;
		$data['not_win_say'] = post('not_win_say');
		$data['join_limit'] = post('join_limit');

		$ar = $this->pbw_config_M->up($id,$data);
		

		empty($ar) && error('修改失败',400);


		$reward = post('reward');

	
		
		$reward = json_decode($reward,true);

		if($reward){
		foreach($reward as $key=>$one){
		$where['bid'] = $id;
		$where['lv'] = $one['lv'];

		$row = $this->pbw_win_M->have($where);


		

		$data_1['rating'] = $one['rating'];
		$data_1['win_title'] = $one['win_title'];
		$data_1['score'] = $one['score'];
		$data_1['balance_type'] =  $one['balance_type'];
		$data_1['win_percent'] = $one['win_percent'];
		$data_1['win_say'] =  $one['win_say'];

		$res2 = $this->pbw_win_M->up($row['id'],$data_1);
		//cs($this->pbw_win_M->log(),1);
		empty($res2) && error('修改失败2',400);
		}
		}
    	$model->run();
    	$redis->exec();

		return $res2;
	}


	public function lists(){
		$page=post("page",1);
		$page_size = post("page_size",10);
		$data=$this->pbw_config_M->lists($page,$page_size,$where);
		$res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data; 
        return $res;
	}

	public function del(){
		(new \app\validate\DelValidate())->goCheck();
		$id_str = post('id_str');
        $id_ar  = explode('@',$id_str);
		$res = $this->pbw_config_M -> del($id_ar);
		empty($res) && error('删除失败',400);
		return $res;
	}

	public function edit(){
		(new IDMustBeRequire())->goCheck();
		$id = post('id');
		$ar = $this->pbw_config_M->find($id);
		$where['bid'] = $id;
		$reward = $this->pbw_win_M->lists_all($where);
		foreach($reward as &$one){
				unset($one['update_time']);
				unset($one['created_time']);
		}	
		$ar['reward'] = $reward;
		unset($ar['update_time']);		
		unset($ar['created_time']);
		return $ar;
	}












	


}