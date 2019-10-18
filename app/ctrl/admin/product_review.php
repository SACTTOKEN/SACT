<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-04 09:09:22
 * Desc: 商品评论
 */

namespace app\ctrl\admin;

use app\model\product_review as ProductReviewModel;
use app\ctrl\admin\BaseController;
use app\validate\IDMustBeRequire;
use app\validate\ProductReviewValidate;
use app\validate\AllsearchValidate;

class product_review extends BaseController{
	
	public $product_review_M;
	public function __initialize(){
		$this->product_review_M = new ProductReviewModel();
	}

	/*按id查找*/
    public function edit(){
    	$id = post('id');
    	(new IDMustBeRequire())->goCheck();
    	$data = $this->product_review_M->find($id);
		$review_pic_M = new \app\model\product_review_pic();
		$data['pic_ar']= $review_pic_M->find($id);
		$data['uid_cn']= user_info($data['uid'],'username');
		$product_M = new \app\model\product();
		$data['pid_cn']= $product_M->findme($data['pid'],'title');
    	empty($data) && error('数据不存在',404);    	
        return $data;
    }	

    /*保存*/
	public function addsave(){
		$data = post(['uid_cn','pid_cn','content','star','update_time','sort']);
		//查用户名和商品名是否存在，求出相应ID
		$user_M = new \app\model\user();
		$uid = $user_M->find_uid($data['uid_cn']);
		empty($uid) && error('用户不存在',400);	 

		$product_M = new \app\model\product();
		$pid = $product_M->find_pid($data['pid_cn']);
		empty($pid) && error('商品不存在',400);

		$data['uid'] = $uid;
		$data['pid'] = $pid;	  
		unset($data['uid_cn']);
		unset($data['pid_cn']);

		$res=$this->product_review_M->save($data);
		$new_id = $this->product_review_M->id(); //新生成的ID
		empty($res) && error('添加失败',400);		 
		admin_log('添加商品评论',$res);
		return $new_id;
	}

	/*按id删除*/
	public function del(){
		(new ProductReviewValidate())->goCheck('scene_del');
		$id_str = post('id_str');
		$id_ar = explode('@',$id_str);
		$res=$this->product_review_M->del($id_ar);
		empty($res) && error('删除失败',400);
		admin_log('删除商品评论',$id_str);
		return $res;
	}

	/*按id修改*/
	public function editsave()
	{	
		$id = post('id');
    	(new ProductReviewValidate())->goCheck('scene_find');
    	$data = post(['uid','pid','star','content','update_time','sort','check']);
		$res=$this->product_review_M->up($id,$data);
		empty($res) && error('修改失败',404);
		admin_log('修改商品评论',$id);
 		return $res; 
	}

	/*查某一类*/
	public function lists()
	{
		
	 	(new AllsearchValidate())->goCheck();
	 	(new \app\validate\PageValidate())->goCheck();
	    $where = [];
        $username = post('username');
        $uid   = post('uid');
		$pid_cn   = post('pid_cn');
		$pid   = post('pid');
		$content = post('content');

        $is_check = post('is_check'); //0未审 1已审
        $created_time_begin = post('created_time_begin'); 
        $created_time_end   = post('created_time_end'); 

   
        if($uid){
            $user_M = new \app\model\user();
            $uid = $user_M->find_mf_uid($uid);
            $where['uid'] = $uid;
        }
        if($username){
            $user_M = new \app\model\user();
            $uid = $user_M->find_mf_uid_plus($username);
            $where['uid'] = $uid;
        }
        if($pid_cn){
            $product_M = new \app\model\product();
            $pid = $product_M->find_mf_pid($pid_cn);
            $where['pid'] = $pid;
        }
        if($pid){
        	$where['pid'] = $pid;
        	$where['is_check'] = 1;
        }
        if(is_numeric($is_check)){
            $where['is_check'] = $is_check;
        }
        if($created_time_begin>0){
			$created_time_end = $created_time_end ? $created_time_end : time();
			$created_time_end = $created_time_end + 3600*24;
            $where['created_time[<>]'] = [$created_time_begin,$created_time_end];
        }
        if($content){
        	$where['content[~]'] = $content;
        }

		$page=post("page",1);
		$page_size = post("page_size",10);
		$data=$this->product_review_M->lists($page,$page_size,$where);

		//cs($this->product_review_M,1);

		$product_M = new \app\model\product();
		foreach($data as &$rs){
			$user=user_info($rs['uid']);
			$rs['uid'] = $user['username'];
			$rs['uid_cn'] = $user['nickname'];
			$rs['pid_cn'] = $product_M->findme($rs['pid'],'title');
			$rs['pid_pic'] = $product_M->findme($rs['pid'],'piclink');
			$rs['avatar'] = $user['avatar']; 
			$rs['rating_cn'] = $user['rating_cn']; 
		}

		$count = $this->product_review_M->new_count($where);
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data; 
        return $res;
	}

	/*审核评论*/
	public function check_review(){
		$id = post('id','');
		$check = post('check',0);
		$data['check'] = $check;
		(new IDMustBeRequire())->goCheck();
		$res = $this->product_review_M->up($id,$data);
		empty($res) && error('审核失败',404);
		admin_log('审核商品评论',$res);
		return $res;
	}

	/*更新评论图片,只传没有ID的新增进数据库,相当没有编辑图片，只有增与删*/
	public function up_review_pic(){
		$review_pic_M = new \app\model\product_review_pic();
		$rid = post('rid');
		$img_json = post('img_json');
		$ar = json_decode($img_json,true);
			foreach($ar as $one){
				$data['rid'] = $rid;
				$data['piclink'] = $one;		
		    	$res = $review_pic_M->save($data);
		    }
		empty($res) && error('修改失败',404);
		admin_log('修改商品评论图片',$rid);
 		return $res; 
	}

	/*按评论ID查找相关图片*/
	public function find_review_pic(){
		$review_pic_M = new \app\model\product_review_pic();
		$rid = post('rid');	
		$data= $review_pic_M->find($rid);
		empty($data) && error('数据不存在',404);    
		return $data;
	}

	/*删除评论图片*/
	public function del_review_pic(){
		$review_pic_M = new \app\model\product_review_pic();
		$id = post('id');
		$res = $review_pic_M->del($id);
		empty($res) && error('删除失败',404);
		admin_log('商品评论图片',$id);
 		return $res; 
	}



}