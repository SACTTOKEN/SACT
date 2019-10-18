<?php

/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-07-27 14:51:11
 * Desc: dapp产品
 */

namespace app\ctrl\admin;
use \app\validate\IDMustBeRequire;
use \app\validate\DappProductValidate;

class dapp_product extends BaseController
{
    public $dapp_product_M;
	public function __initialize()
	{
        $this->dapp_product_M=new \app\model\dapp_product();
	}

	/*查某一类*/
	public function lists()
	{
		$data=$this->dapp_product_M->lists_all();	
        return $data; 
    }
    

    /*保存*/
	public function saveadd(){
		$data = post(['title','day','price','income']);
		(new DappProductValidate())->goCheck('saveadd');
		$res=$this->dapp_product_M->save($data);
		empty($res) && error('添加失败',400);			
		admin_log('添加dapp产品',$res);    
		return '添加成功';
	}

	/*详情*/
	public function edit()
	{
		$id = post('id');
    	(new IDMustBeRequire())->goCheck();
    	$data = $this->dapp_product_M->find($id);
    	empty($data) && error('数据不存在',404);    	
        return $data;    
	}

    /*保存修改*/
	public function saveedit()
	{	
		$id = post('id');
    	(new IDMustBeRequire())->goCheck();
    	(new DappProductValidate())->goCheck('saveedit');
		$data = post(['title','day','price','income']);
		$res=$this->dapp_product_M->up($id,$data);
		empty($res) && error('修改失败',404);		
		admin_log('修改dapp产品',$id);   
 		return $res; 
	}
    

	/*删除*/
	public function del(){
		$id = post('id');
    	(new IDMustBeRequire())->goCheck();
		$res=$this->dapp_product_M->del($id);
		empty($res) && error('删除失败',400);		
		admin_log('删除dapp产品',$id);   
		return $res;
    }
    

}
