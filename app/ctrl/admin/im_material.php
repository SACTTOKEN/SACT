<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-02 09:52:31
 * Desc: 自动回复控制器
 */

namespace app\ctrl\admin;

use app\model\im_material as im_material_Model;
use app\validate\IDMustBeRequire;
use app\validate\ImMaterialValidate;

class im_material extends BaseController{
	
	public $im_material_M;
	public function __initialize(){
		$this->im_material_M = new im_material_Model();
	}

    //查找
    public function lists(){
        (new \app\validate\AllsearchValidate())->goCheck();
        (new \app\validate\PageValidate())->goCheck();
        $page=post("page",1);
		$page_size = post('page_size',10);    
        $title = post('title');
        $types = post('types');
        $where = [];
        if($title){
        $where['content[~]'] = $title;
        }
        if($types){
        $where['types'] = $types;
        }
		$order=['sort'=>'DESC','id'=>'DESC'];
		$data=$this->im_material_M->lists_sort($page,$page_size,$where,$order);
		$count = $this->im_material_M->new_count($where);
		$res['data'] = $data;
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page_size'] = $page_size;
        $res['page'] = $page;        
        return $res; 
    }

	//添加
    public function saveadd(){
        (new ImMaterialValidate())->goCheck('scene_saveedit');
        $data = post(['types','content']);    
        $res=$this->im_material_M->save($data);
        empty($res) && error('添加失败',400);   
        admin_log('添加IM素材',$res);  
        return $res;
    }

    /*修改*/
	public function edit()
	{
		(new IDMustBeRequire())->goCheck();
		$id=post("id");
		$res=$this->im_material_M->find($id);
		empty($res) && error('数据不存在',404); 
		return $res;
	}

	
	/*保存修改*/
	public function saveedit()
	{
		(new IDMustBeRequire())->goCheck();	
		$id=post("id");
		(new ImMaterialValidate())->goCheck('scene_saveedit');
        $data = post(['types','content']);    
		$res=$this->im_material_M->up($id,$data);
		empty($res) && error('修改失败',404);
		admin_log('修改IM素材',$id);  
 		return $res; 
    }
    
    /*输入数字排序*/
	public function sort_by_number(){
		$sort = post('sort');
		$id = post('id');	
		$data['sort'] = $sort;
		(new IDMustBeRequire())->goCheck();	
		(new ImMaterialValidate())->goCheck('sort');
		$res = $this->im_material_M->up($id,$data);
		empty($res) && error('排序失败',400);		
		admin_log('IM素材排序',$id);    
		return $res;
	}

    //删除
    public function del(){       
        (new IDMustBeRequire())->goCheck();
        $id = post('id');      
        $res=$this->im_material_M->del($id);
        empty($res) && error('删除失败',400);
        admin_log('删除IM素材',$id);  
        return $res;
    }


    //批量删除、
    public function del_all(){
    	(new ImMaterialValidate())->goCheck('scene_checkID');
    	$id = post('del_id');
    	$id_ar = explode('@',$id);
    	$new_ar = [];
    	foreach($id_ar as $one){
    		if($one){
    			$new_ar[] = $one;
    		}
    	}
    	$res=$this->im_material_M->del($new_ar);
    	empty($res) && error('删除失败',400);
        admin_log('删除IM素材',$id);  
    	return $res;
    }

	public function welcome()
	{
		$welcome=post('welcome');
		$id = $GLOBALS['admin']['id'];
		$res=(new \app\model\admin())->find($id,['welcome']);
		empty($res) && error('数据不存在',400); 
		if($welcome){
        $res = (new \app\model\admin())->up($id,['welcome'=>$welcome]);
        empty($res) && error('修改失败',400);
		admin_log('修改欢迎语',$id);  
		}else{
			$welcome=$res['welcome'];
		}
        return $welcome;
	}
}