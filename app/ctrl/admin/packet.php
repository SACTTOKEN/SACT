<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-04-02 15:44:16
 * Desc: 红包发布
 */

namespace app\ctrl\admin;

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
    	$data = $this->packet_M->find($id);
    	empty($data) && error('数据不存在',404);    	
        return $data;      
    }	

    /*保存*/
	public function saveadd(){
		(new PacketValidate())->goCheck('scene_add');
		$data = post(['title','money','cdn_xfm','cdn_pid','cdn_sid','lifetime','limit_num','jf_change','limit_lv','page_get','full_num']);
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
                $new_ar[] = $one;
            }
        }
        $res=$this->packet_M->del($new_ar);
        empty($res) && error('删除失败',400);
		admin_log('删除红包',$id_str);   
        return $res;
    }

	/*按id修改*/
	public function saveedit()
	{	
		$id = post('id');
    	(new PacketValidate())->goCheck('scene_saveedit');
    	$data = post(['title','money','cdn_xfm','cdn_pid','cdn_sid','lifetime','limit_num','jf_change','limit_lv','page_get','full_num']);
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
		$data=$this->packet_M->lists($page,$page_size);
		$count = $this->packet_M->new_count();
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data; 
        return $res; 
	}

	/*红包选项*/
	public function coupon_option()
	{
		$res = $this->packet_M->coupon_option();
		return $res;
	}

	/*（新手，消费, 评价）红包修改 type(new/xf/pj)*/
	public function new_packet(){
		(new IDMustBeRequire())->goCheck();
		$type = post('type');
		empty($type) && error('类型丢失',400);
		$packet_id = post('id');
		$desc = post('desc');
		$desc = $desc ? $desc : '';
		$rating = post('rating');
		$data = [];
		switch($type) {
			case 'new':
				$data['is_new'] = 1;
				$data['new_desc'] = $desc;
				$data['new_rating'] = $rating;
				break;
			case 'xf':	
				$data['is_xf'] = 1;
				$data['xf_desc'] = $desc;
				$data['xf_rating'] = $rating;
				break;
			case 'pj':
				$data['is_pj'] = 1;
				$data['pj_desc'] = $desc;
				$data['pj_rating'] = $rating;
				break;	
		}
		$res = $this->packet_M->up($packet_id,$data);
		empty($res) && error('修改失败',400);
		return $res;
	}


	/*（新手，消费, 评价）红包取消 type(new/xf/pj)*/
	public function cancel_new_packet(){
		(new IDMustBeRequire())->goCheck();
		$type = post('type');
		empty($type) && error('类型丢失',400);
		$packet_id = post('id');
		$data = [];
		$desc = '';
		switch($type) {
			case 'new':
				$data['is_new'] = 0;
				$data['new_desc'] = $desc;
				$data['new_rating']  = 0;
				break;
			case 'xf':	
				$data['is_xf'] = 0;
				$data['xf_desc'] = $desc;
				$data['new_rating'] = 0;
				break;
			case 'pj':
				$data['is_pj'] = 0;
				$data['pj_desc'] = $desc;
				$data['pj_rating'] = 0;
				break;	
		}
		$res = $this->packet_M->up($packet_id,$data);
		empty($res) && error('修改失败',400);
		return $res;
	}


	/*我的（新手，消费, 评价）红包 type(new/xf/pj)*/
	public function my_new_packet(){
		$type = post('type');
		empty($type) && error('类型丢失',400);
		switch($type) {
			case 'new':
				$where['is_new'] = 1;		
				break;
			case 'xf':	
				$where['is_xf'] = 1;		
				break;
			case 'pj':
				$where['is_pj'] = 1;
				break;	
		}
		$where['ORDER'] = ['id'=>'ASC'];
		$data = $this->packet_M->lists_all($where);

		foreach($data as &$one){
			switch($type) {
			case 'xf':	
				$one['new_desc'] = $one['xf_desc'];;		
				break;
			case 'pj':
				$one['new_desc'] = $one['pj_desc'];;
				break;	
			}
		}


		$rating  = 0;
		$is_open = 0;
		
		switch($type) {
		case 'new':
			$rating = $data[0]['new_rating'];	
			$is_open = renew_c('is_open_new');
			break;
		case 'xf':	
			$rating = $data[0]['xf_rating'];
			$is_open = renew_c('is_open_xf');	
			break;
		case 'pj':
			$rating = $data[0]['pj_rating'];
			$is_open = renew_c('is_open_pj');
			break;	
		}
		
		$res['data'] = $data;
		$res['other'] = ['is_open'=>$is_open,'rating'=>$rating];
		return $res;
	}


	/*等级限制 type(new/xf/pj)*/
	public function lock_rating(){
		$type = post('type');
		$rating = post('rating');
		empty($type) && error('类型丢失',400);
		switch($type) {
			case 'new':
				$where['is_new'] = 1;
				$data['new_rating'] = $rating;
				break;
			case 'xf':	
				$where['is_xf'] = 1;
				$data['xf_rating'] = $rating;
				break;
			case 'pj':
				$where['is_pj'] = 1;
				$data['pj_rating'] = $rating;
				break;	
			}

		$res = $this->packet_M->up_all($where,$data);
		empty($res) && error('修改失败',404);
		return $res;
	}


	/*是否开放 type(new/xf/pj)*/
	public function lock_open(){
		$type = post('type');
		$is_open = post('is_open');
		empty($type) && error('类型丢失',400);
		switch($type) {
			case 'new':
				$iden = 'is_open_new';
				$data['value'] = $is_open;
				break;
			case 'xf':	
				$iden = 'is_open_xf';
				$data['value'] = $is_open;
				break;
			case 'pj':
				$iden = 'is_open_pj';
				$data['value'] = $is_open;
				break;	
			}
		$configM = new \app\model\config();
		$res = $configM->up($iden,$data);
		empty($res) && error('修改失败',404);
		return $res;
	}




	
}