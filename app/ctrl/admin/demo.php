<?php

/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-07-27 14:51:11
 * Desc: 示例
 */

namespace app\ctrl\admin;
use \app\validate\IDMustBeRequire;
use \app\validate\DemoValidate;

class demo extends BaseController
{
    public $demo_M;
	public function __initialize()
	{
        $this->demo_M=new \app\model\demo();
	}

	/*查某一类*/
	public function lists()
	{
		(new \app\validate\AllsearchValidate())->goCheck();
		(new \app\validate\PageValidate())->goCheck();
		
		$title = post('title');
		$is_open = post('is_open');
		
		if($title){
			$where['title[~]'] = $title;
		}
		if(is_numeric($is_open)){
			$where['is_open'] = $is_open;
		}
        
        $created_time_begin=post('created_time_begin');
        $created_time_end=post('created_time_end');
		if(is_numeric($created_time_begin)){
			$created_time_end = $created_time_end ? $created_time_end : time();
			$created_time_end = $created_time_end + 3600*24;
        	$where['created_time[<>]'] = [$created_time_begin,$created_time_end]; 	
        }

		$page=post("page",1);
		$page_size = post("page_size",10);		
		$order=['id'=>'DESC'];
		$data=$this->demo_M->lists_sort($page,$page_size,$where,$order);
		$count = $this->demo_M->new_count($where);
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data; 
        return $res; 
    }
    

    /*保存*/
	public function saveadd(){
		$data = post(['price','subscriptions','progress','is_open','title']);
		(new DemoValidate())->goCheck('saveadd');
		$res=$this->demo_M->save($data);
		empty($res) && error('添加失败',400);			
		admin_log('添加示例',$res);    
		return '添加成功';
	}

	/*详情*/
	public function edit()
	{
		$id = post('id');
    	(new IDMustBeRequire())->goCheck();
    	$data = $this->demo_M->find($id);
    	empty($data) && error('数据不存在',404);    	
        return $data;    
	}

    /*保存修改*/
	public function saveedit()
	{	
		$id = post('id');
    	(new IDMustBeRequire())->goCheck();
    	(new DemoValidate())->goCheck('saveedit');
    	$data = post(['price','subscriptions','progress','is_open','title']);
		$res=$this->demo_M->up($id,$data);
		empty($res) && error('修改失败',404);		
		admin_log('修改示例',$id);   
 		return $res; 
	}
    

	/*删除*/
	public function del(){
		$id = post('id');
    	(new IDMustBeRequire())->goCheck();
		$res=$this->demo_M->del($id);
		empty($res) && error('删除失败',400);		
		admin_log('删除示例',$id);   
		return $res;
    }
    

}
