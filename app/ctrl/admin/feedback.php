<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-07-23 19:17:09
 * Desc: 反馈问题
 */
namespace app\ctrl\admin;

use app\model\feedback as FeedbackModel;
use app\ctrl\admin\BaseController;
use app\validate\FeedbackValidate;
use app\validate\IDMustBeRequire;

class feedback extends BaseController{

	public $feedback_M;
	public function __initialize(){
		$this->feedback_M = new FeedbackModel();
	}

	public function feedback_type(){
        $ar = ['商家问题','账号问题','支付问题','其他问题'];
        return $ar;
    }


	/*查某一类*/
	public function lists()
	{
		(new \app\validate\PageValidate())->goCheck();
		$types = post('types');
		$where = [];	
		$created_time_begin = post('created_time_begin');
		$created_time_end = post('created_time_end');
		if($types){
			$where['types'] = $types;
		}
		
		if(is_numeric($created_time_begin)){
			$created_time_end = $created_time_end ? $created_time_end : time();
			$created_time_end = $created_time_end + 3600*24;
        	$where['created_time[<>]'] = [$created_time_begin,$created_time_end]; 	
        }
		$page=post("page",1);
		$page_size = post("page_size",10);		
		
		$data=$this->feedback_M->lists($page,$page_size,$where);
		$count = $this->feedback_M->new_count($where);
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data; 
        return $res; 
	}


	/*显示*/
    public function edit()
    {
        $id = post('id');
        (new IDMustBeRequire())->goCheck();
        $data = $this->feedback_M->find($id);
        empty($data) && error('数据不存在',404);     
        return $data;    
    }


    /*删除*/
    public function del(){
		(new \app\validate\DelValidate())->goCheck();
		$id_str = post('id_str');
        $id_ar = explode('@',$id_str);
		$res = $this->feedback_M -> del($id_ar);
		empty($res) && error('删除失败',400);
		return $res;
	}
	

}