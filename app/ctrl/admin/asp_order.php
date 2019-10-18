<?php
/**
 * Created by yaaaaa__god 
 * User: yaaaaa
 * Date: 2018-12-18 13:50:53
 * Desc: 订单控制器
 */

namespace app\ctrl\admin;

use app\model\order as OrderModel;
use app\ctrl\admin\BaseController;
use app\validate\IDMustBeRequire;

class order extends BaseController{
	
	public $orderM;
	public function __initialize(){
		$this->orderM = new OrderModel();
	}

	/*订单列表*/
	public function lists()
	{	
		(new \app\validate\AllsearchValidate())->goCheck();
		(new \app\validate\PageValidate())->goCheck();
		$where = [];
		$uid = post('uid','');
		if($uid){
			$where['uid'] = $uid;
		}		
		$page=post("page",1);
		$page_size = post("page_size",10);		
		$data=$this->orderM->lists($where,$page,$page_size);
		$count = $this->orderM->new_count($where);
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data; 

		//var_dump($this->orderM->log());	

       	return $res; 
	}

	/*订单详情*/
	public function order_info(){
		$id = post('id');
		(new IDMustBeRequire())->goCheck();
		$order_pro_M = new \app\model\order_product();
		$base=$this->orderM->find($id);

 		$base['uid_cn'] = user_info($base['uid'],'username');
        $product =  $order_pro_M->find_by_oid($base['oid']);   
        $product_M = new \app\model\product();

        if(!empty($product)){
            foreach($product as $k=>$v){
               $product[$k]['sku_cn']= explode('@',$v['sku_cn']);
               $product[$k]['num_money'] = $v['money'] * $v['number'];
               $product[$k]['hid'] = $product_M->findme($v['pid'],'hid');
            }
        }

        $other = [
        	'operator' => $GLOBALS['admin']['username'],
        	'print_time' => date('Y-m-d H:i:s'),
			'com_add'   => C('add'),
			'com_tel'	=> C('tel'),
        ]; 

		$info = [
			'base' => $base,
			'product_ar' => $product,
			'other' => $other,
			
		];
		return $info;
	}

	/*修改订单详情*/
	public function edit_order_info(){
		$id = post('id');
		(new IDMustBeRequire())->goCheck();
		$data = post(['is_pay','pay_time','mail_time','complete_time','remark','admin_remark']);

		$info = $this->orderM->find($id);

		if($data['is_pay']==1 && $info['status'] == '未支付'){
			$data['is_pay'] = 1;
			$data['status'] = '已支付';
		}else{
			unset($data['is_pay']);
		}

		$res=$this->orderM->up($id,$data);
		empty($res) && error('修改失败',404);
        admin_log('修改订单',$id);  
 		return $res;
	}


	/*修改收货信息*/
	public function edit_mail()
	{
		$id = post('id');
    	(new IDMustBeRequire())->goCheck();
    	$data = post(['mail_name','mail_tel','mail_province','mail_city','mail_area','mail_address','admin_remark']);
    	$res=$this->orderM->up($id,$data);
		empty($res) && error('修改失败',404);
 		return $res; 
	}


	/*修改支付状态*/
	public function edit_pay()
	{
		$id = post('id');
    	(new IDMustBeRequire())->goCheck();
    	$data = post(['pay','is_pay','status','pay_time']);
    	$res=$this->orderM->up($id,$data);
		empty($res) && error('修改失败',404);
        admin_log('修改订单支付状态',$id);  
 		return $res;
	}


	/*修改发货信息*/
	public function edit_send()
	{
		$id = post('id');
    	//(new IDMustBeRequire())->goCheck();
    	$mail_type = post('mail_type'); //发货方式  0：常规  1：电子面单

    	if($mail_type == 0){
			$data = post(['mail_courier','mail_oid','mail_time','remark','complete_time']);
    		$res=$this->orderM->up($id,$data);
			empty($res) && error('修改失败',400);
    	}else{
    		//组织面单数据源
    		$kdn_S = new \app\service\kdn();

    		//根据定单ID 查该订单的商户sid.再去mail表查商户的物流信息
    		$ar = $this->orderM->find($id);
    		$sid = $ar['sid'];
    		$mail_M = new \app\model\mail();
    		$mail_ar = $mail_M->find_by_sid($sid);

    		$data['title_en'] = $mail_ar['title_en']; //合作快递编码 如:CF
    		$data['oid'] = $ar['oid'];

    		$data['sender_name'] =  $mail_ar['sender_name'];
    		$data['sender_mobile'] =  $mail_ar['sender_mobile'];

    		$data['mail_name'] = $ar['mail_name'];
    		$data['mail_tel'] = $ar['mail_tel'];
    		$data['mail_province'] = $ar['mail_province'];
    		$data['mail_city'] = $ar['mail_city'];
    		$data['mail_area'] = $ar['mail_area'];
    		$data['mail_address'] = $ar['mail_address'];


    		$order_product_M = new \app\model\order_product();
    		$op_ar = $order_product_M->find_by_oid($ar['oid']);
    		$i=1;
    		foreach($op_ar as $one){
    			if($i==1){$data['goods_name'] = $one['title'];} //多个商品只填一个
    			$i++;
    		}	


    		$back =	$kdn_S->kdn_order($data);

    		if(!empty($back)){
    			$data_up['mail_oid'] = $back['mail_oid'];
    			$data_up['kdn_order_code'] = $back['kdn_order_code'];
    			$data_up['print_template'] = $back['print_template'];
    			$res=$this->orderM->up($id,$data_up);
    		}	

    		empty($res) && error('面单失败',400);
    	}
    	
        admin_log('订单发货',$id);  
 		return $data_up;
	}


	/*退换货列表,参数$status  0正常，1申请退货，2允许退货，3已退货待退款 4退货成功 @404代表非0 */
	public function reback_lists(){
		(new \app\validate\PageValidate())->goCheck();
		$where = [];
		$status = post('status',404);
		if($status!=404){
            $where['status'] = $status;
        }else{
            $where['status'] = [1,2,3,4];
        }
		$page=post("page",1);
		$page_size = post("page_size",10);	
		$goods_M = new \app\model\order_product();	
		$data=$goods_M->return_goods($page,$page_size,$where);
		$count = $goods_M->return_count($where);
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data;
        return $res;
	}


	/*退换货*/
	public function reback_goods(){
		$goods_id = post('goods_id',0);
		$status = post('status',0);
		(new \app\validate\OrderProductValidate())->goCheck('scene_edit');
		$goods_M = new \app\model\order_product();
		$data['status']	=$status;
		$data['admin_remark'] = post('admin_remark','');
		$res=$goods_M->up($goods_id,$data);
		empty($res) && error('修改失败',400);
		
        admin_log('修改退换货',$goods_id);  
		return $res;
	}


	/*订单奖金审核*/
	public function order_money_list(){
		$money_M = new \app\model\money();
		$oid = post('oid');
		$data = $money_M->lists_by_oid($oid);
		foreach($data as $key=>$rs){
			$users=user_info($rs['uid']);
			//会员账号 与 昵称
			$data[$key]['username']  = $users['username'];
			$data[$key]['nickname']  =	$users['nickname'];

       		//加减符号 1加2减   
       		if($rs['types'] == 2){
       			$data[$key]['money'] = "-".$data[$key]['money'];
       		}else{
       			$data[$key]['money'] = "+".$data[$key]['money'];
       		}

       		//奖励类型 到reward中去查
       		$data[$key]['style_cn'] = find_reward_redis($rs['cate']); 
       		//来源 
       		$data[$key]['ly_name'] =  user_info($rs['ly_id'],'username'); 
       					
		}
		return $data; 
	}

}