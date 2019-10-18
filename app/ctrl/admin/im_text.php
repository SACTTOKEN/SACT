<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-02 09:52:31
 * Desc: 自动回复控制器
 */

namespace app\ctrl\admin;

use app\model\im_text as im_text_Model;
use app\validate\IDMustBeRequire;
use app\validate\ImTextValidate;

class im_text extends PublicController{
	
	public $im_text_M;
	public function __initialize(){
		$this->im_text_M = new im_text_Model();
	}

    //查找
    public function lists(){
        (new \app\validate\AllsearchValidate())->goCheck();
        (new \app\validate\PageValidate())->goCheck();
        $page=post("page",1);
		$page_size = post('page_size',10);    
        $content = post('content');
        $keyword = post('keyword');
        $where = [];
        if($content){
        $where['content[~]'] = $content;
        }
        if($keyword){
        $where['keyword[~]'] = $keyword;
        }
		$order=['id'=>'DESC'];
		$data=$this->im_text_M->lists_sort($page,$page_size,$where,$order);
		$count = $this->im_text_M->new_count($where);
		$res['data'] = $data;
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page_size'] = $page_size;
        $res['page'] = $page;        
        return $res; 
    }

	//添加
    public function saveadd(){
        (new ImTextValidate())->goCheck('scene_saveedit');
		$data = post(['keyword','content','is_like']);    
		$im_res=$this->im_text_M->have(['keyword'=>$data['keyword']]);
		if($im_res){
			error('关键词已存在',404);
		}
        $res=$this->im_text_M->save($data);
        empty($res) && error('添加失败',400);   
        admin_log('添加IM自动回复',$res);  
        return '添加成功';
    }

    /*修改*/
	public function edit()
	{
		(new IDMustBeRequire())->goCheck();
		$id=post("id");
		$res=$this->im_text_M->find($id);
		empty($res) && error('数据不存在',404); 
		return $res;
	}

	
	/*保存修改*/
	public function saveedit()
	{
		(new IDMustBeRequire())->goCheck();	
		$id=post("id");
		(new ImTextValidate())->goCheck('scene_saveedit');
		$data = post(['keyword','content','is_like']);    
		$im_res=$this->im_text_M->have(['keyword'=>$data['keyword'],'id[!]'=>$id]);
		if($im_res){
			error('关键词已存在',404);
		}
		$res=$this->im_text_M->up($id,$data);
		empty($res) && error('修改失败',404);
		admin_log('修改IM自动回复',$id);  
 		return '修改成功'; 
    }
    

    //批量删除、
    public function del_all(){
    	(new \app\validate\DelValidate())->goCheck();
    	$id_str = post('id_str');
    	$id_ar = explode('@',$id_str);
    	
    	$res=$this->im_text_M->del($id_ar);
    	empty($res) && error('删除失败',400);		
		admin_log('删除IM自动回复',$id_str);   
    	return '删除成功';
    }


}