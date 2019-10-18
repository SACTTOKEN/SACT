<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-04-02 15:44:16
 * Desc: 红包发布
 */

namespace app\ctrl\supplier;

use app\model\packet as PacketModel;
use app\validate\IDMustBeRequire;
use app\validate\PacketValidate;

class packet extends BaseController{
	
	public $packet_M;
	public function __initialize(){
		$this->packet_M = new PacketModel();
	}

	/*按id查找*/
    public function edit(){
    	$id = post('id');
    	(new IDMustBeRequire())->goCheck();
    	$data = $this->packet_M->have(['id'=>$id,'cdn_sid'=>$GLOBALS['user']['id']]);
    	empty($data) && error('数据不存在',404);    	
        return $data;      
    }	

    /*保存*/
	public function saveadd(){
		(new PacketValidate())->goCheck('scene_add');
		$data = post(['title','money','cdn_xfm','cdn_pid','lifetime','limit_num','jf_change','limit_lv','page_get','full_num']);
		$data['cdn_sid']=$GLOBALS['user']['id'];
		$res=$this->packet_M->save($data);
		empty($res) && error('添加失败',400);	
		admin_log('添加红包',$res);    
		return $res;
	}

    /*批量删除*/
    public function del_all(){
        (new PacketValidate())->goCheck('scene_checkID');
        $id_str = post('id_str');
        $id_ar = explode('@',$id_str);
        $new_ar = [];
        foreach($id_ar as $one){
            if($one){
				$data = $this->packet_M->have(['id'=>$one,'cdn_sid'=>$GLOBALS['user']['id']]);
				empty($data) && error('数据不存在',404);    	
				$res=$this->packet_M->del($one);
        		empty($res) && error('删除失败',400);
            }
        }
        
		admin_log('删除红包',$id_str);   
        return $res;
    }

	/*按id修改*/
	public function saveedit()
	{	
		$id = post('id');
		$data = $this->packet_M->have(['id'=>$id,'cdn_sid'=>$GLOBALS['user']['id']]);
		empty($data) && error('数据不存在',404);    

    	(new PacketValidate())->goCheck('scene_saveedit');
    	$data = post(['title','money','cdn_xfm','cdn_pid','lifetime','limit_num','jf_change','limit_lv','page_get','full_num']);
		$res=$this->packet_M->up($id,$data);
		empty($res) && error('修改失败',404);
		admin_log('修改红包',$id);   
 		return $res; 
	}

	/*查某一类*/
	public function lists()
	{
		(new \app\validate\PageValidate())->goCheck();
		$page=post("page",1);
		$page_size = post("page_size",10);
		$where['cdn_sid']=$GLOBALS['user']['id'];
		$data=$this->packet_M->lists($page,$page_size,$where);
		$count = $this->packet_M->new_count();
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data; 
        return $res; 
	}

	public function coupon_option()
	{
		$res = $this->packet_M->coupon_option();
		return $res;
	}

	
}