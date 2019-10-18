<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-04-26 15:26:35
 * Desc: 矿机控制器
 */

namespace app\ctrl\admin;

use app\model\coin_machine as CoinMachineModel;
use app\ctrl\admin\BaseController;
use app\validate\IDMustBeRequire;
use app\validate\DelValidate;
use app\validate\CoinMachineValidate;

class coin_machine extends BaseController{
	
	public $coin_m_M;
	public function __initialize(){
		$this->coin_m_M = new CoinMachineModel();
	}

	/*按id查找*/
    public function edit(){
    	$id = post('id');
    	(new IDMustBeRequire())->goCheck();
    	$data = $this->coin_m_M->find($id);
    	empty($data) && error('数据不存在',404);    	
        return $data;      
    }	

    /*保存*/
	public function saveadd(){
		$data = post(['m_title','m_pic','m_money','m_day_production','m_life','m_block','m_res','is_show','z_money','purchase_limit']);
		(new CoinMachineValidate())->goCheck('scene_saveadd');
		$res=$this->coin_m_M->save($data);
		empty($res) && error('添加失败',400);	
		admin_log('添加矿机产品',$res);   
		return $res;
	}

	/*按id删除*/
	public function del(){
		$id_str = post('id_str');
		(new DelValidate())->goCheck();
		$id_ar = explode('@',$id_str);
		$res=$this->coin_m_M->del($id_ar);
		empty($res) && error('删除失败',400);
		admin_log('删除矿机产品',$id_str);   
		return $res;
	}

	/*按id修改*/
	public function saveedit()
	{	
		$id = post('id');
    	(new CoinMachineValidate())->goCheck('scene_saveedit');
    	$data = post(['m_title','m_pic','m_money','m_day_production','m_life','m_block','m_res','is_show','z_money','purchase_limit']);
		$res=$this->coin_m_M->up($id,$data);
		empty($res) && error('修改失败',404);
		admin_log('修改矿机产品',$id);   
 		return $res; 
	}

	/*查某一类*/
	public function lists()
	{
		(new \app\validate\AllsearchValidate())->goCheck();
		(new \app\validate\PageValidate())->goCheck();
		$where = [];
		$m_title = post('m_title');
		if($m_title){
			$where['m_title[~]'] = $m_title;
		}
		$page=post("page",1);
		$page_size = post("page_size",10);
		$data=$this->coin_m_M->lists($page,$page_size,$where);
		$count = $this->coin_m_M->new_count($where);
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data; 
        return $res; 
	}

	/*显隐*/
	public function check_show(){
		(new IDMustBeRequire())->goCheck();
		$id = post('id','');
		$is_show = post('is_show',0);
		$data['is_show'] = $is_show;
		$res = $this->coin_m_M->up($id,$data);
		empty($res) && error('操作失败',404);
		return $res;
	}



}