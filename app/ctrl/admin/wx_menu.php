<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-02 09:52:31
 * Desc: 微信菜单控制器
 */

namespace app\ctrl\admin;

use app\model\wx_menu as wx_menu_Model;
use app\validate\WxMenuValidate;
use app\ctrl\admin\BaseController;
use app\validate\IDMustBeRequire;


class wx_menu extends BaseController{
	
	public $wx_menu_M;
	public function __initialize(){
		$this->wx_menu_M = new wx_menu_Model();	
	}

    /*按id查找*/
    public function edit(){
        $id = post('id');
        (new IDMustBeRequire())->goCheck();
        $data = $this->wx_menu_M->find($id);
        empty($data) && error('数据不存在',404);     
        return $data;      
    }   

	//添加
    public function saveadd(){
        (new WxMenuValidate())->goCheck('scene_add');
        $big_menu = $this->wx_menu_M->list_cate(0);        
        $data = post(['title','types','value','parent_id','show','sort','material']); 
        if($data['parent_id']>0){
            $small_menu = $this->wx_menu_M->list_cate($data['parent_id']);
            if(count($small_menu)>=5){
                error('二级菜单最多只能有五个',400);
            }
        }else{ 
            if(count($big_menu)>=3){
            error('一级菜单最多只能有三个',400);
            }  
        }
        
        if(empty($data['value'])){$data['value'] = 'meun_'.rand(0,1000);}
        $res=$this->wx_menu_M->save($data);
        empty($res) && error('添加失败',400);   
        admin_log('添加微信菜单',$res); 
        return $res;
    }

    //删除
    public function del(){       
        (new WxMenuValidate())->goCheck('scene_del');
        $id = post('id');
        $rs = $this->wx_menu_M->find($id);
        empty($rs) && error('数据不存在',400);
        $res= $this->wx_menu_M->del($id);
        empty($res) && error('删除失败',400);
        admin_log('删除微信菜单',$id); 
        return $res;
    }

    //修改
    public function saveedit(){       
        (new WxMenuValidate())->goCheck('scene_edit');       
        $id = post('id');
        $data = post(['title','types','value','parent_id','show','sort','material','appid','pagepath']); 
        $res = $this->wx_menu_M->up($id,$data);
        empty($res) && error('修改失败',400);
        admin_log('修改微信菜单',$id); 
        return $res;
    }


    /*栏目树*/
    public function list_tree(){
        $parent_id = post('parent_id',0);
        return $this->find_tree($parent_id);
    }


    public function find_tree($parent_id=0){
        $obj = $this->wx_menu_M->tree($parent_id);
        if(!empty($obj)){

        foreach($obj as $rs){
            $res = $this->find_tree($rs['id']);
            if($res){
                $rs['z'] =$res; 
            }
            $ar[] = $rs;
        }
        return $ar;
        }
    }


    /*栏目排序*/
    public function menu_sort(){
        $sort_str = post('sort_str');
        $parent_id = post('parent_id');

        $ar = [];
        if(!empty($sort_str)){
            $ar = explode('@',$sort_str);
        }
        empty($ar) && error('排序失败',400);
        
        $ar = array_reverse($ar);
        $res = $this->wx_menu_M->sort($ar,$parent_id);
        empty($res) && error('排序失败',400);
        admin_log('修改微信菜单排序');
        return $res;
    }



    /*发布菜单*/
    public function fabu_menu(){
        $wechatObj = new \app\service\wechat();
        return $wechatObj->pubMenu();
    }



}