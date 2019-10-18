<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-02 09:52:31
 * Desc: 微信模板控制器
 */

namespace app\ctrl\admin;
use app\validate\IDMustBeRequire;
use app\validate\WxSmsTempletValidate;
use app\model\wx_sms_templet as wx_sms_templet_Model;
use app\ctrl\admin\BaseController;

class wx_sms_templet extends BaseController{
	
	public $wx_sms_templet_M;
	public $wx_sms_M;
	public function __initialize(){
		$this->wx_sms_templet_M = new wx_sms_templet_Model();	
	}

    //列表
    public function lists(){
        $where['ORDER'] = ['sort'=>'DESC'];
        $data = $this->wx_sms_templet_M->lists_all($where);
        empty($data) && error('数据不存在',404);     
        return $data;      
    }   

	//添加
    public function saveadd(){
        (new WxSmsTempletValidate())->goCheck('saveadd');
        $data = post(['title','templet_id','templet']);    
        $res=$this->wx_sms_templet_M->save($data);
        empty($res) && error('添加失败',400);  
        admin_log('添加微信模板',$res);   
        return $res;
    }

    //删除
    public function del(){       
        (new IDMustBeRequire())->goCheck();     
        $id = post('id');      
        $res= $this->wx_sms_templet_M->del($id);
        empty($res) && error('删除失败',400);
        admin_log('删除微信模板',$id);
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


    //保存修改
    public function saveedit(){       
        (new IDMustBeRequire())->goCheck();       
        (new WxSmsTempletValidate())->goCheck('scene_saveedit');
        $id = post('id');
        $data = post(['title','templet_id','templet']); 
        $res = $this->wx_sms_templet_M->up($id,$data);
        (new \app\model\wx_sms())->up_all(['tid'=>$id],['templet_id'=>$data['templet_id']]);
        empty($res) && error('修改失败',400);
        admin_log('修改微信模板',$id);
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
		$res = $this->wx_sms_templet_M->sort($ar);
		empty($res) && error('排序失败',400);
        admin_log('微信模板排序');
		return $res;
    }
}