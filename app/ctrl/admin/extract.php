<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-02 09:52:31
 * Desc: 自提
 */

namespace app\ctrl\admin;

use app\model\extract as extractM;
use app\validate\ExtractValidate;
use app\ctrl\admin\BaseController;
use app\validate\IDMustBeRequire;


class extract extends BaseController{
	
	public $extractM;
	public function __initialize(){
		$this->extractM = new extractM();		
	}

    //分页列表
    public function lists()
    {
        (new \app\validate\AllsearchValidate())->goCheck();
        (new \app\validate\PageValidate())->goCheck();
        $page=post("page",1);
        $page_size = post("page_size",10);      
        $data=$this->extractM->lists($page,$page_size);
        $count = $this->extractM->new_count();
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data; 
        return $res; 
    }

	//添加
    public function saveadd(){
        (new ExtractValidate())->goCheck('scene_add');
        $data = post(['name','tel','title','piclink','province','city','area','town','add','longitude','latitude']);    
        $res=$this->extractM->save($data);
        empty($res) && error('添加失败',400);    
		admin_log('添加自提',$res);  
        return $res;
    }

    //删除
    public function del(){       
        (new ExtractValidate())->goCheck('scene_del');
        $id_str = post('id_str');
        $id_ar = explode('@',$id_str);
        $res=$this->extractM->del($id_ar,0);
        empty($res) && error('删除失败',400);
        
		admin_log('删除自提',$id_str);  
        return $res;
    }

    /*修改*/
	public function edit()
	{
		(new IDMustBeRequire())->goCheck();
		$id=post("id");
		$res=$this->extractM->find($id,0);
		empty($res) && error('数据不存在',400); 
		return $res;
	}

    //保存修改
    public function saveedit(){       
        (new IDMustBeRequire())->goCheck();
        (new ExtractValidate())->goCheck('scene_add');     
        $id = post('id');
        $data = post(['name','tel','title','piclink','province','city','area','town','add','longitude','latitude']);  
        $res = $this->extractM->up($id,0,$data);
        empty($res) && error('修改失败',400);
		admin_log('编辑修改自提',$id);  
        return $res;
    }



}