<?php

/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-07-27 14:51:11
 * Desc: 公排层级奖励
 */

namespace app\ctrl\admin;
use \app\validate\IDMustBeRequire;
use \app\validate\PlatoonTeamValidate;

class platoon_team extends BaseController
{
    public $platoon_team_M;
	public function __initialize()
	{
        $this->platoon_team_M=new \app\model\platoon_team();
	}

	/*查某一类*/
	public function lists()
	{
        $data=$this->platoon_team_M->lists_all();      
        return $data; 
    }
    

    /*保存*/
	public function saveadd(){
		$data = post(['reward','fee']);
		(new PlatoonTeamValidate())->goCheck();
		$res=$this->platoon_team_M->save($data);
		empty($res) && error('添加失败',400);			
		admin_log('添加公排层级奖励',$res);    
		return '添加成功';
	}

	/*详情*/
	public function edit()
	{
		$id = post('id');
    	(new IDMustBeRequire())->goCheck();
    	$data = $this->platoon_team_M->find($id);
    	empty($data) && error('数据不存在',404);    	
        return $data;    
	}

    /*保存修改*/
	public function saveedit()
	{	
		$id = post('id');
    	(new IDMustBeRequire())->goCheck();
    	(new PlatoonTeamValidate())->goCheck();
    	$data = post(['reward','fee']);
		$res=$this->platoon_team_M->up($id,$data);
		empty($res) && error('修改失败',404);		
		admin_log('修改公排层级奖励',$id);   
 		return $res; 
	}
    

	/*删除*/
	public function del(){
		$id = post('id');
    	(new IDMustBeRequire())->goCheck();
		$res=$this->platoon_team_M->del($id);
		empty($res) && error('删除失败',400);		
		admin_log('删除公排层级奖励',$id);   
		return $res;
    }
    

}
