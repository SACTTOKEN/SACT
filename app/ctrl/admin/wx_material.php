<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-02 09:52:31
 * Desc: 自动回复控制器
 */

namespace app\ctrl\admin;

use app\model\wx_material as wx_material_Model;
use app\validate\WxMaterialValidate;
use app\validate\IDMustBeRequire;


class wx_material extends BaseController{
	
	public $wx_material_M;
	public function __initialize(){
		$this->wx_material_M = new wx_material_Model();
	}

    public function find(){   
        (new WxMaterialValidate())->goCheck('scene_find'); 
        $id_str = post("id_str");
        $id_ar = explode('@',$id_str);
        foreach($id_ar as $id_one){
            $new[] = $this->wx_material_M->find($id_one);
        }
        return $new;
    }

    //查找
    public function lists(){
        (new \app\validate\AllsearchValidate())->goCheck();
        (new \app\validate\PageValidate())->goCheck();
        $page=post("page",1);
		$page_size = post('page_size',10);    
        $title = post('title');
        $where = [];
        if($title){
        $where['title[~]'] = $title;
        }
		$data  = $this->wx_material_M->lists($page,$page_size,$where);
		$count = $this->wx_material_M->new_count($where);
		$res['data'] = $data;
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page_size'] = $page_size;
        $res['page'] = $page;        
        return $res; 
    }

	//添加
    public function saveadd(){
        (new WxMaterialValidate())->goCheck('scene_saveedit');
        $data = post(['title','piclink','links','content']);    
        $res=$this->wx_material_M->save($data);
        empty($res) && error('添加失败',400);   
        admin_log('添加微信素材',$res);  
        return $res;
    }

    /*修改*/
	public function edit()
	{
		(new IDMustBeRequire())->goCheck();
		$id=post("id");
		$res=$this->wx_material_M->find($id);
		empty($res) && error('数据不存在',404); 
		return $res;
	}

	
	/*保存修改*/
	public function saveedit()
	{
		(new IDMustBeRequire())->goCheck();	
		$id=post("id");
		(new WxMaterialValidate())->goCheck('scene_saveedit');
        $data = post(['title','piclink','links','content']);    
		$res=$this->wx_material_M->up($id,$data);
		empty($res) && error('修改失败',404);
		admin_log('修改微信素材',$id);  
 		return $res; 
    }
    

    //删除
    public function del(){       
        (new IDMustBeRequire())->goCheck();
        $id = post('id');      
        $res=$this->wx_material_M->del($id);
        empty($res) && error('删除失败',400);
        admin_log('删除微信素材',$id);  
        return $res;
    }


    //批量删除、
    public function del_all(){
    	(new WxMaterialValidate())->goCheck('scene_checkID');
    	$id = post('del_id');
    	$id_ar = explode('@',$id);
    	$new_ar = [];
    	foreach($id_ar as $one){
    		if($one){
    			$new_ar[] = $one;
    		}
    	}
    	$res=$this->wx_material_M->del($new_ar);
    	empty($res) && error('删除失败',400);
        admin_log('删除微信素材',$id);  
    	return $res;

    }

	
}