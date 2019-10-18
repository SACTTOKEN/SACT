<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-02 09:52:31
 * Desc: 自动回复控制器
 */

namespace app\ctrl\admin;

use app\model\wx_text as wx_text_Model;
use app\ctrl\admin\BaseController;
use app\validate\WxTextValidate;
use app\validate\IDMustBeRequire;

class wx_text extends BaseController{
    
    public $wx_text_M;
    public function __initialize(){
        $this->wx_text_M = new wx_text_Model();
    }

    //回复添加
    public function saveadd(){
        (new WxTextValidate())->goCheck('scene_add');
        $data = post(['keyword','is_like','types','content','material']); 
        $data['content'] = strip_tags($data['content']);    
        $res=$this->wx_text_M->save($data);
        empty($res) && error('添加失败',400); 
        admin_log('添加微信回复',$res);  
        return $res;
    }

    //回复删除
    public function del(){       
        (new IDMustBeRequire())->goCheck();
        $id = post('id');      
        $res=$this->wx_text_M->del($id);
        empty($res) && error('删除失败',400);
        admin_log('删除微信回复',$id);
        return $res;
    }

    //修改
    public function edit(){       
        (new IDMustBeRequire())->goCheck();      
        $id = post('id');
        $res=$this->wx_sms_templet_M->find($id);
        empty($res) && error('数据不存在',404); 
        return $res;
    }


    //回复修改
    public function saveedit(){       
        (new IDMustBeRequire())->goCheck();
        (new WxTextValidate())->goCheck('scene_edit');
        $id = post('id');
        $data = post(['keyword','is_like','types','content','material']); 
        $data['content'] = strip_tags($data['content']); 
        $res = $this->wx_text_M->up($id,$data);
        empty($res) && error('修改失败',400);
        admin_log('修改微信回复',$id);

        return $res;
    }

    //查找
    public function lists(){ 
        (new \app\validate\PageValidate())->goCheck();     
        $page=post("page",1);
        $page_size = post('page_size',10);
        $data  = $this->wx_text_M->lists($page,$page_size);

            //vue初始化显示子菜单里的素材
            $wx_material_M = new \app\model\wx_material();
            foreach($data as $key=>$one){
                if(intval($one['material'])>0){
                    $material_ar = $wx_material_M->find($one['material']);
                    $material_ar = $material_ar ? $material_ar : [];
                    $data[$key]['material_ar'] = $material_ar;
                }
            }

        $count = $this->wx_text_M->new_count();
        $res['data'] = $data;
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page_size'] = $page_size;
        $res['page'] = $page;        
        return $res; 
    }
        
}