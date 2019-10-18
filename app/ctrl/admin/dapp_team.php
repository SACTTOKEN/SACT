<?php

/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-07-27 14:51:11
 * Desc: DAPP团队奖
 */

namespace app\ctrl\admin;
use \app\validate\IDMustBeRequire;
use \app\validate\DappTeamValidate;

class dapp_team extends BaseController
{
    public $dapp_team_M;
	public function __initialize()
	{
        $this->dapp_team_M=new \app\model\dapp_team();
	}

	/*查某一类*/
	public function lists()
	{	
        $data=$this->dapp_team_M->lists_all(['ORDER'=>['rid'=>'ASC','team_level'=>'ASC']]);	
        $dapp_rating_M=new \app\model\dapp_rating();
        foreach($data as &$vo){
            $vo['rid_cn']=$dapp_rating_M->find($vo['rid'],'title');
        }
        return $data; 
    }
    

    /*保存*/
	public function saveadd(){
		$data = post(['rid','team_level','team_award']);
        (new DappTeamValidate())->goCheck('saveadd');
        $res1=$this->dapp_team_M->is_have(['rid'=>$data['rid'],'team_level'=>$data['team_level']]);
        if($res1){
            error('等级层数已存在',404);
        }
		$res=$this->dapp_team_M->save($data);
		empty($res) && error('添加失败',400);			
		admin_log('添加DAPP团队奖',$res);    
		return '添加成功';
	}

	/*详情*/
	public function edit()
	{
		$id = post('id');
    	(new IDMustBeRequire())->goCheck();
    	$data = $this->dapp_team_M->find($id);
    	empty($data) && error('数据不存在',404);    	
        return $data;    
	}

    /*保存修改*/
	public function saveedit()
	{	
		$id = post('id');
    	(new IDMustBeRequire())->goCheck();
    	(new DappTeamValidate())->goCheck('saveedit');
        $data = post(['rid','team_level','team_award']);
        $res1=$this->dapp_team_M->is_have(['id[!]'=>$id,'rid'=>$data['rid'],'team_level'=>$data['team_level']]);
        if($res1){
            error('等级层数已存在',404);
        }
		$res=$this->dapp_team_M->up($id,$data);
		empty($res) && error('修改失败',404);		
		admin_log('修改DAPP团队奖',$id);   
 		return $res; 
	}
    

	/*删除*/
	public function del(){
		$id = post('id');
    	(new IDMustBeRequire())->goCheck();
		$res=$this->dapp_team_M->del($id);
		empty($res) && error('删除失败',400);		
		admin_log('删除DAPP团队奖',$id);   
		return $res;
    }
    

}
