<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-25
 * Desc: 等级控制器 
 */
namespace app\ctrl\admin;

use app\model\rating as RatingModel;
use app\validate\IDMustBeRequire;
use app\validate\RatingValidate;


class rating extends BaseController{
	
	public $ratingM;
	public function __initialize(){
		$this->ratingM = new RatingModel();
	}

	/*下拉列表角色类 VUE格式*/
	public function option(){
		$data=$this->ratingM->option();
		return $data;
	}

	/*所有等级*/
	public function lists(){
		$data = $this->ratingM->lists_all();
        empty($data) && error('数据不存在',404);
		$account=(new \app\model\reward())->option2_ar();
		foreach($data as &$vo){
			$vo['level_account_cn']=$account[$vo['level_account']];
			$vo['team_account_cn']=$account[$vo['team_account']];
			$vo['dividend_account_cn']=$account[$vo['dividend_account']]; 
			if($vo['direct_rating']){
			$vo['direct_rating_cn']=$this->ratingM->find($vo['direct_rating'],'title'); 
			}
			unset($vo['piclink']);
			unset($vo['flag']);
		}
		empty($data) && error('暂无相关数据',404);
		return $data;
	}

	/*按id查找*/
    public function edit(){
    	$id = post('id');
		(new IDMustBeRequire())->goCheck();	
    	$data = $this->ratingM->find($id);
    	empty($data) && error('数据不存在',404);    	
		unset($data['piclink']);
		unset($data['flag']);
        return $data;      
    }

    /*修改保存*/
	public function saveedit(){
    	$id = post('id');
		(new IDMustBeRequire())->goCheck();	
		$ar = $this->ratingM->is_find($id);
		empty($ar) && error('数据不存在',404);
		$data=post(['title','discount','zt_num','td_num','direct_rating','direct_rating_number','shop_buy','assign_buy','recharge','td_shop_buy','td_assign_buy','level_1','level_2','level_3','level_4','level_5','level_account','team','team_same','team_account','dividend','dividend_cycle','dividend_account','dividend_types']);
		$res=$this->ratingM->up($id,$data);
		empty($res) && error('修改失败',404);	 
		admin_log('修改等级',$res);
		return $res;
	}

    /*保存*/
	public function saveadd(){
		(new RatingValidate())->goCheck('scene_add');
		$title = post('title');
		$data = $this->ratingM->find(1,['piclink','flag']);
		$data['title']=$title;
		$data['zt_num']=10000;
		$data['discount']=10;
		$res=$this->ratingM->save($data);
		empty($res) && error('添加失败',10006);	 
		admin_log('添加等级',$res);
		return $res;
	}

	/*按id删除*/
	public function del(){
		(new IDMustBeRequire())->goCheck();	
		$id = post('id');
		if($id==1){
			error('不能删除游客',400);	 
		}
		$data = $this->ratingM->is_find($id);
        empty($data) && error('数据不存在',404);
        $where['rating']=$id;
        $user_ar=(new \app\model\user())->have($where);
        $user_ar && error('有该等级的会员，请先修改会员等级');
		$res=$this->ratingM->del($id);
		empty($res) && error('删除失败',400);
		admin_log('删除等级',$id);
		return "删除成功";
	}

}