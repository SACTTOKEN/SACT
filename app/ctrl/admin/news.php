<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-18 13:50:53
 * Desc: 文章控制器
 */

namespace app\ctrl\admin;

use app\model\news as NewsModel;
use app\validate\NewsValidate;
use app\validate\IDMustBeRequire;
use app\ctrl\admin\BaseController;
use app\service\create_html;

use app\validate\AllsearchValidate;

class news extends BaseController{
	
	public $newsM;
	public $create;
	public function __initialize(){
		$this->newsM  = new NewsModel();
		$this->create = new create_html();
	}

	

    /*保存*/
	public function saveadd(){
		$data = post(['title','cate_id','piclink','description','content','show','sort','author','hit']);
		(new NewsValidate())->goCheck('scene_add');
		$res=$this->newsM->save($data);
		empty($res) && error('添加失败',400);			
		admin_log('添加新闻',$res);    
		return $res;
	}

	/*按id删除*/
	public function del(){
		$id = post('id');
		(new newsValidate())->goCheck('scene_find');

		$res=$this->newsM->del($id);
		empty($res) && error('删除失败',400);		
		admin_log('删除新闻',$id);   
		return $res;
	}

	/*修改*/
	public function edit()
	{
		$id = post('id');
    	(new IDMustBeRequire())->goCheck();
    	$data = $this->newsM->find($id);
    	if($data['content']){$data['content'] = str_replace('@link=@','src=',$data['content']);}
    	empty($data) && error('数据不存在',404);    	
        return $data;    
	}

	/*按id修改*/
	public function saveedit()
	{	
		$id = post('id');
    	(new newsValidate())->goCheck('scene_find');
    	$data = post(['title','cate_id','piclink','description','content','show','sort','is_top','author','hit']);

		$res=$this->newsM->up($id,$data);
		empty($res) && error('修改失败',404);		
		admin_log('保存新闻',$id);   
 		return $res; 
	}

	/*查某一类*/
	public function lists()
	{
		(new AllsearchValidate())->goCheck();
		(new \app\validate\PageValidate())->goCheck();
		$where = ['id[!]'=>100000];	

		$title = post('title');
		$cate_id = post('cate_id');
		$show = post('show');
		$is_top = post('is_top');
		$created_time_begin = post('created_time_begin');
		$created_time_end = post('created_time_end');

		if($title){
			$where['title[~]'] = $title;
		}
		if($cate_id){
			$where['cate_id'] = $cate_id;
		}
		if($show){
			$where['show'] = $show;
		}
		if($is_top){
			$where['is_top'] = $is_top;
		}
		if(is_numeric($created_time_begin)){
			$created_time_end = $created_time_end ? $created_time_end : time();
			$created_time_end = $created_time_end + 3600*24;
        	$where['created_time[<>]'] = [$created_time_begin,$created_time_end]; 	
        }

		$page=post("page",1);
		$page_size = post("page_size",10);		
		$order=['is_top'=>'DESC','sort'=>'DESC','id'=>'DESC'];
		$data=$this->newsM->lists_sort($page,$page_size,$where,$order);
		// var_dump($this->newsM->log());
		// exit();
		$count = $this->newsM->new_count($where);
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data; 
        return $res; 
	}

	/*批量删除*/
    public function del_all(){
    	(new NewsValidate())->goCheck('scene_checkID');
    	$id_str = post('id_str');
    	$id_ar = explode('@',$id_str);
    	
    	$res=$this->newsM->del($id_ar);
    	empty($res) && error('删除失败',400);		
		admin_log('删除新闻',$id_str);   
    	return $res;
    }




}