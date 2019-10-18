<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-16 13:51:24
 * Desc: 评价
 */
namespace app\ctrl\mobile;
use app\validate\IDMustBeRequire;

class product_review extends BaseController
{
	public $product_review_pic;
	public $product_review_M;
	public $order_product_M;
	public $order_M;
	public function __initialize(){
		$this->product_review_M = new \app\model\product_review();
		$this->product_review_pic_M = new \app\model\product_review_pic();
		$this->order_product_M = new \app\model\order_product();
		$this->order_M = new \app\model\order();
	}


	public function index(){
		(new IDMustBeRequire())->goCheck();
		$uid = $GLOBALS['user']['id'];
		$id = post('id');
		$where['uid']=$uid;
		$where['id']=$id;
		$order_product_ar=$this->order_product_M->have($where,['oid','title','piclink','pid']);
		empty($order_product_ar) && error('下单才能评论',10007);

		$order_where['uid']=$uid;
		$order_where['oid']=$order_product_ar['oid'];
		$order_where['status']='已完成';
		$order_ar=$this->order_M->have($order_where,['created_time','uid','oid']);
		empty($order_ar) && error('订单未完成',10007);
		$data['title']=$order_product_ar['title'];
		$data['piclink']=$order_product_ar['piclink'];
		$data['pid']=$order_product_ar['pid'];
		$data['created_time']=$order_ar['created_time'];
		$data['uid']=$order_ar['uid'];
		$data['oid']=$order_ar['oid'];
		$data['is_review']=0;
		$review_where['uid']=$uid;
		$review_where['order_product_id']=$id;
		$review_ar=$this->product_review_M->have($review_where);
		if($review_ar){
			$piclink=$this->product_review_pic_M->lists_all(['rid'=>$review_ar['id']]);
			if($piclink){
			$review_ar['piclink'];
			}
			$data['is_review']=1;
			$data['review']=$review_ar;
		}
		return $data;
	}


	public function saveadd(){
		$uid = $GLOBALS['user']['id'];
		$id=post('id'); // iorder_product表ID
		$data=$this->index();
		if($data['is_review']==1){
			error('已评论',404);
		}
		(new \app\validate\ProductReviewValidate())->goCheck('mobile_saveadd');
		$parameter=post(['content','star','pic']);
		$review_ar['content']=$parameter['content'];
		$review_ar['star']=$parameter['star'];
		$review_ar['uid']= $data['uid'];
		$review_ar['pid']= $data['pid'];
		$review_ar['oid']= $data['oid'];
		$review_ar['order_product_id']= $id;
		$is_pic=0;
		if(isset($parameter['pic'])){
			$pic = explode('@',$parameter['pic']);
			if($pic){
				$is_pic=1;
				$review_ar['is_pic']= 1;				
			}
		}
		$res=$this->product_review_M->save($review_ar);
		empty($res) && error('添加失败',10006);
		if($is_pic==1){
			foreach($pic as $vo){
				if($vo){
				$pic_ar['rid']=$res;
				$pic_ar['piclink']=$vo;
				$this->product_review_pic_M->save($pic_ar);
				}
			}
		}
		$this->order_product_M->up($id,['is_review'=>1]);
		$p_res=$this->order_product_M->is_have(['oid'=>$data['oid'],'is_review'=>0]);
		if(empty($p_res)){
			$this->order_M->up_all(['oid'=>$data['oid']],['is_review'=>1]);
		}

		
		if(c('is_pjycsp')==1){
		$new_duty_S = new \app\service\new_duty();
    	$new_duty_S->paid_reward($uid,'pjycsp'); //新手任务-评价一次商品
    	}


    	if(plugin_is_open('pjhb')==1){
    	$coupon_S = new \app\service\coupon();
        $coupon_S -> packet_xf_pj($uid,'pj',$data['oid'],$id); //评价红包发放END	
    	}
 	
		$where['uid'] = $uid;
		$where['oid'] = $data['oid'];
		$where['order_product_id'] = $id;
		$coupon_M = new \app\model\coupon();
		$packet_M = new \app\model\packet();
		$ar = $coupon_M->lists_all($where);
		$new_ar = [];

		foreach($ar as $key=>$one){
			$coupon_title = $packet_M->find($one['packet_id'],'title');
   			$new_ar[$key]['money'] = $one['money'];
            $new_ar[$key]['desc'] = $one['source'];
            $new_ar[$key]['xfm'] = $one['xfm'];
            $new_ar[$key]['end_time'] = $one['end_time'];  
            $new_ar[$key]['coupon_title'] = $coupon_title;
		}
    	if(empty($new_ar)){
    		return "评价成功";
    	}else{	
    		return $new_ar;
    	}
 		
	}

	public function statistics()
	{
		(new \app\validate\IDMustBeRequire())->goCheck();
		$pid = post('id');
		$where['pid'] = $pid;
		$data['all'] = $this->product_review_M->new_count($where);
		$where['is_pic']=1;
		$data['yes'] = $this->product_review_M->new_count($where);
		$where['is_pic']=0;
		$data['no'] = $this->product_review_M->new_count($where);
		return $data;
	}

	/*评论更表*/
    public function lists(){
		(new \app\validate\IDMustBeRequire())->goCheck();
		 $pid = post('id');
		 $where['pid'] = $pid;
		 $page=post("page",1);
		 $page_size = post("page_size",10); 
		 $data = $this->product_review_M->lists($page,$page_size,$where);
		 foreach($data as &$new_one){
			 $talker= user_info($new_one['uid']);
			 $new_one['avatar'] = $talker['avatar'];
			 $new_one['nickname'] = $talker['nickname'] ? $talker['nickname'] : $talker['username']; 
			 $new_one['rating_cn'] = $talker['rating_cn'];
			 $ar = $this->product_review_pic_M->lists_all(['rid'=>$new_one['id']]);
			 $new_one['piclink'] = $ar;
		 }
		 return $data;  
	 }
}
