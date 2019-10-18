<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-02 09:52:31
 * Desc: 模板消息控制器
 */

namespace app\ctrl\admin;

use app\model\wx_sms as wx_sms_Model;
use app\validate\WxSmsValidate;
use app\ctrl\admin\BaseController;
use app\validate\IDMustBeRequire;
use app\model\wx_sms_templet as wx_sms_templet_Model;

class wx_sms extends BaseController{
	
	public $wx_sms_M;
	public function __initialize(){
		$this->wx_sms_M = new wx_sms_Model();		
	}

    //SMS查某类
    public function lists(){    
        (new WxSmsValidate())->goCheck('scene_list');
        $tid = post('tid');              
        
        $where = ['tid'=>$tid];
        $where['ORDER']=["sort"=>"DESC"];
        $res=$this->wx_sms_M->lists_all($where);

		$wx_sms_templet_M = new wx_sms_templet_Model();	
        foreach($res as &$vo){
            $vo['tid_cn']=$wx_sms_templet_M->find($vo['tid'],'title');
        }
        return $res;
    }

	//SMS添加
    public function saveadd(){
        (new WxSmsValidate())->goCheck('scene_add');
        $data = post(['title','tid','op']);    
		$wx_sms_templet_M = new wx_sms_templet_Model();	
        $data['templet_id']=$wx_sms_templet_M->find($data['tid'],'templet_id');
        $res=$this->wx_sms_M->save($data);
        empty($res) && error('添加失败',400); 
        admin_log('添加模板消息',$res);   
        return $res;
    }

    //SMS删除
    public function del(){       
        (new IDMustBeRequire())->goCheck();
        $id = post('id');      
        $res= $this->wx_sms_M->del($id);
        empty($res) && error('删除失败',400);
        admin_log('删除模板消息',$id);
        return $res;
    }

    /*修改*/
	public function edit()
	{
		(new IDMustBeRequire())->goCheck();
		$id=post("id");
		$res=$this->wx_sms_M->find($id);
        empty($res) && error('数据不存在',400); 
        
        //上级栏目ID串
		$wx_sms_templet_M = new wx_sms_templet_Model();	
		$ar = $wx_sms_templet_M->lists_all();
		$res['up_id'] = $ar;
		return $res;
	}

    //保存修改
    public function saveedit(){       
        (new IDMustBeRequire())->goCheck();
        (new WxSmsValidate())->goCheck('scene_edit');       
        $id = post('id');
        $data = post(['content','bottom','wx_show','web_show','app_show','tel_show']);
        $data['content'] = strip_tags($data['content']);
        $res = $this->wx_sms_M->up($id,$data);
        empty($res) && error('修改失败',400);
        admin_log('修改模板消息',$id);
        return $res;
    }

    //保存修改
    public function saveedit_title(){       
        (new IDMustBeRequire())->goCheck();
        (new WxSmsValidate())->goCheck('scene_saveedit_title');       
        $id = post('id');
        $data = post(['title','tid','op']); 
        $res = $this->wx_sms_M->up($id,$data);
        empty($res) && error('修改失败',400);
        admin_log('修改模板消息标题',$id);
        return $res;
    }


    
    /*排序*/
	public function sort(){
		$sort_str = post('sort_str');
		$ar = [];
		if(!empty($sort_str)){
			$ar = explode('@',$sort_str);
		}
		empty($ar) && error('排序失败',400);
		
		$ar = array_reverse($ar);
		$res = $this->wx_sms_M->sort($ar);
		empty($res) && error('排序失败',400);
        admin_log('模板消息排序');
		return $res;
    }
    

    //SMS查某类
    public function lists_tid(){    
        (new WxSmsValidate())->goCheck('scene_list');
        $tid = post('tid');              
        $res=$this->wx_sms_M->lists_tid($tid);
        return $res;
    }

    //保存修改2
    public function saveedit_tid(){       
        (new IDMustBeRequire())->goCheck();
        (new WxSmsValidate())->goCheck('scene_edit');       
        $id = post('id');
        $data = post(['content','wx_show','web_show','app_show','bottom']); 
        $res = $this->wx_sms_M->up($id,$data);
        empty($res) && error('修改失败',400);
        admin_log('修改模板消息',$id);
        return $res;
    }

}