<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-14 17:25:08
 * Desc: 订单
 */
namespace app\ctrl\mobile;

use app\model\order as OrderModel;
use app\model\cart as CartModel;
use app\service\stock;
use \app\model\product as ProductModel;
use app\validate\OrderValidate;
use app\model\order_product as OrderProductModel;
use app\service\money;

class order extends PublicController
{
	public $orderM;
	public $cart_M;
	public $stock_S;
	public $pro_M;
	public $product_sku_M;
    public $OrderProductM;
    public $money_S;
    public $order_pro_M;

	public function __initialize(){
		$this->orderM = new OrderModel();
		$this->cart_M = new CartModel();
		$this->stock_S = new stock();
		$this->pro_M = new ProductModel();
		$this->product_sku_M = new \app\model\product_sku();
		$this->OrderProductM = new OrderProductModel();
		$this->money_S = new money;
		$this->order_pro_M = new \app\model\order_product();
	}

	//确认订单号
	public function confirm_order()
	{
		(new \app\validate\CartValidate())->goCheck('scene_checkID');
		$types=0; 
		$is_virtual=1;
		$sum_price=0;
		$send_score=0;
		$sum_mail=0;
		$sum_integral=0;
		$sum_cost=0;
		$sum_made_01=0;
		$sum_made_02=0;
		$sum_made_03=0;
		$id_ar=post('id_ar');		
		$id_str = post('id_str');
		$id_ar = explode('@',$id_str);
		$user = $GLOBALS['user'];
        $cart = $this->cart_M->lists_have(['uid'=>$user['id'],'id'=>$id_ar]);  
		empty($cart) && error('购物车为空',10007);
		$car_ar=[];
		foreach($cart as &$rs){
			$where=[];
			$where['id']=$rs['pid'];
			$where['is_check']=1;
			$where['show']=1;
			$product_ar=$this->pro_M->have($where);
			if(empty($product_ar)){
				$this->cart_M->del($rs['id']);
				continue;
			}
			$product_ar=array_diff_key($product_ar, ['content'=>'','attr'=>'','sku_json'=>'']);

			if(!isset($car_ar[$rs['sid']]['info'])){
				if($rs['sid']==0){
					$shop['shop_title']=c('head');
					$shop['shop_logo']=c('logo');
					$shop['sid']=0;
				}else{
					$users=user_info($rs['sid']);
					$shop['shop_title']=$users['shop_title']?$users['shop_title']:$users['nickname'];
					$shop['shop_logo']=isset($users['shop_logo'])?$users['shop_logo']:'';
					$shop['sid']=$rs['sid'];
				}
				$shop['sum_price']=0;
				$car_ar[$rs['sid']]['info']=$shop;
			}
			
			if($product_ar['types']!=0){
				if(count($cart)>1){
					error('活动区商品',10007);
				}
				$types=$product_ar['types'];
			}
			$is_have_stock=$this->stock_S->have_stock($rs['sku_id'],$rs['number']);
			empty($is_have_stock) && error($product_ar['title'].'库存不足',10007);
			if($product_ar['is_virtual']==0){
				$is_virtual=0;
			}

			$number=0;
			$number_where['uid']=$user['id'];
			$number_where['id']=$id_ar;
			$number_where['pid']=$rs['pid'];
			$number=$this->cart_M->find_sum('number',$number_where);
			$product_ar['price'] = $this->stock_S->price($user['rating'],$product_ar,$rs['sku_id'],$number);
			$product_ar['cost'] = $this->stock_S->cost($rs['sku_id']);

			//活动商品
			if($product_ar['types']){
				switch ($product_ar['types'])
				{
				case 2:
					//积分兑换
					$product_ar['price']=0;	
					$product_ar['cost']=0;
					$data['score_rob']=$product_ar['score_rob'];
					break;
				case 3:
					//砍价
					break;
				case 4:
					//拼团
					if($rs['group_types']==1){
						$types=0;
						$product_ar['types']=0;
					}else{
						if($rs['group_id']){
							$group_res=(new \app\service\group())->judge($rs['group_id']);
							if($group_res['status']==0){
								$this->cart_M->del($rs['id']);
								error($group_res['msg'],10007);
							}
						}
						$product_ar['price']=$product_ar['price']*$product_ar['group_discount']/10;
						if($product_ar['cost']>$product_ar['price']){
							$product_ar['price']=$product_ar['cost'];
						}
					}
					break;
				case 5:
					//众筹
					break;
				case 6:
					//预约商品
					break;
				case 7: 
					//限时特惠
					if($product_ar['real_sale']+$number>$product_ar['discount_limit']){
						error('本商品活动已结束，您可以参加其他商品活动',10007);
					}
					$rob_time_M = new \app\model\rob_time();
					$rob_where['id']=$product_ar['time_id'];
					$rob_where['begin_time[<]']=time();
					$rob_where['end_time[>]']=time();
					$rob_ar=$rob_time_M->have($rob_where);
					empty($rob_ar) && error('活动未开始',10007);
					$product_ar['price']=$product_ar['price']*$product_ar['discount_rob']/10;
					if($product_ar['cost']>$product_ar['price']){
						$product_ar['price']=$product_ar['cost'];
					}
					break;
				}
			}
		
			if($product_ar['types']!=2){
				if($product_ar['price']<=0){
					error('价格错误',10007);
				}
			}

			//定制下单
			$made=c('made');
			if($made){
				$ctrlfile=MADE.'/'.$made.'/service/order.php';
				if(is_file($ctrlfile)){
				$cltrlClass='\made\\'.$made.'\service\order';
				$made_S=new $cltrlClass();
				$product_ar=$made_S->index($product_ar,$rs);
				}
			}

			$product_ar['sum_price'] = $rs['number']*$product_ar['price'];
			//判断积分抵用
			if($product_ar['integral_dk_per']>0){
				if($product_ar['integral_dk_per']>$product_ar['price']*c('jfdhbl')){
					$product_ar['integral_dk_per']=$product_ar['price']*c('jfdhbl');
				}
			}
			//总
			$car_ar[$rs['sid']]['info']['sum_price']=$car_ar[$rs['sid']]['info']['sum_price']+$product_ar['sum_price'];
			$sum_price=$sum_price+$product_ar['sum_price'];
			$send_score=$send_score+$rs['number']*$product_ar['send_score'];
			$sum_integral=$sum_integral+$rs['number']*$product_ar['integral_dk_per'];
			$sum_cost=  $sum_cost+$rs['number']*$product_ar['cost'];
			$sum_made_01=$sum_made_01+$rs['number']*$product_ar['made_01'];
			$sum_made_02=$sum_made_02+$rs['number']*$product_ar['made_02'];
			$sum_made_03=$sum_made_03+$rs['number']*$product_ar['made_03'];

			$rs['pro']=$product_ar;
			$car_ar[$rs['sid']]['data'][]=$rs;
		}
		sort($car_ar);
		
		if($is_virtual==0){
			//收货地址
			$address_where['uid']=$user['id'];
			$address_where['ORDER']=['is_show'=>'DESC'];
			$address=(new \app\model\user_address())->have($address_where);
			if(isset($address)){
				$data['address']=$address;
				$data['address_id']=$address['id'];

				//计算邮费
				foreach($car_ar as &$vo){
					$vo['info']['mail']=$this->stock_S->get_mail($vo,$data['address']['province']);
					$sum_mail=$sum_mail+$vo['info']['mail'];
				}
			}
		}

		if($product_ar['types']==2 || $product_ar['types']==4){
			$integral=[];
			$data['red']=[];
		}else{
			//获得可用红包
			$data['red']=(new \app\service\coupon())->available($car_ar);

			//积分抵用
			$integral['user_integral']=$user['integral'];
			if($sum_integral>$integral['user_integral']){
				$sum_integral=$integral['user_integral'];
			}
			$integral['integral_dk_per']=$sum_integral;
			$integral['integral_dk_money']=bcdiv($sum_integral,10,2);
		}

		$data['integral']=$integral;
		$data['cart']=$car_ar;
		$data['sum_mail']=floatval(sprintf('%.2f',$sum_mail));
		$data['sum_cost']=floatval(sprintf('%.2f',$sum_cost));
		$data['sum_price']=  floatval(sprintf('%.2f',$sum_price));
		$data['send_score']= $send_score;
		$data['is_virtual']=$is_virtual;		
		$data['sum_made_01']= floatval(sprintf('%.2f',$sum_made_01));		
		$data['sum_made_02']=floatval(sprintf('%.2f',$sum_made_02));		
		$data['sum_made_03']=floatval(sprintf('%.2f',$sum_made_03));		 
		$data['types']=$types;
		$data['types_cn']=$this->pro_M->types($types);

		
        return $data; 
	}

	/*下单*/
	public function save()
	{
		(new OrderValidate())->goCheck('mobile_order_save');
		$user=$GLOBALS['user'];
		$data=$this->confirm_order();
		$querys=post(['is_integral','red_id','address_id']);
		$is_invoice=post('is_invoice',0);
		//收货地址
		if($data['is_virtual']==0){
			!isset($querys['address_id']) && error('请提交收货地址',404);
			$address=(new \app\model\user_address())->have(['id'=>$querys['address_id'],'uid'=>$user['id']]);
			empty($address) && error('收货地址不存在',404);
			$data['address']=$address;
		}

		//活动商品
		if($data['types']){
			switch ($data['types'])
			{
			case 2:
				//积分兑换
				if($data['score_rob']<=0){
					error('商品已下架',404);
				}
				$user_M = new \app\model\user();
				$integral = $user_M->find($user['id'],'integral');
				if($integral-$data['score_rob']<0){
					error('积分不足',10003);
				}else{
					$order['score_rob']=$data['score_rob'];
				}
				break;
			case 3:
				//砍价
				break;
			case 4:
				//拼团
				break;
			case 5:
				//众筹
				break;
			case 6:
				//预约商品
				break;
			case 7: 
				//限时特惠
				break;
			default:
			}
		}

		//积分抵用
		$data['integral']['is_integral']=0;
		if(isset($querys['is_integral']) && $querys['is_integral']=='1'){
			$user_M = new \app\model\user();
			$integral = $user_M->find($user['id'],'integral');
			if($integral-$data['integral']['integral_dk_per']<0){
				error('积分不足',10003);
			}else{
				$data['integral']['is_integral']=1;
			}
		}
		//红包
		$red=$data['red'];
		unset($data['red']);
		if(isset($querys['red_id']) && $querys['red_id']!=''){
			$red=array_column($red,null, 'id');
			if(!isset($red[$querys['red_id']])){
				error('红包不存在',10003);
			}
			if($red[$querys['red_id']]['is_cat']==0){
				error('红包无法使用',10003);
			}
			$data['red']=$red[$querys['red_id']];
		}
		$made=c('made');
		flash_god($user['id']);
		$redis = new \core\lib\redis();
		$Model = new \core\lib\Model();
		$Model->action();
		$redis->multi();
		//写入订单
		$order['integral_dk_money']=0;
		$order['integral_dk_per']=0;
		$order['red_money']=0;
		$order['uid']=$user['id'];
		$order['sum_price']=$data['sum_price'];
		$order['sum_mail']=$data['sum_mail'];
		$order['is_integral']=$data['integral']['is_integral'];
		//积分抵用
		if($data['integral']['is_integral']==1){
			$order['integral_dk_per']=$data['integral']['integral_dk_per'];
			$order['integral_dk_money']=$data['integral']['integral_dk_money'];
		}
		//红包抵扣
		if(isset($data['red'])){
			$order['is_red']=1;
			$order['red_id']=$data['red']['id'];
			$order['red_pid']=$data['red']['pid'];
			$order['red_money']=$data['red']['money'];
		}
		//预约商品
		if($data['types']==6){
			(new OrderValidate())->goCheck('types_6');
			$order['reserve_time']=post('reserve_time');
		}
        $order['sid']=$data['cart'][0]['info']['sid'];
        $order['is_virtual']=$data['is_virtual'];
        $order['send_score']=$data['send_score'];
        $order['is_invoice']=$is_invoice;
        $order['types']=$data['types'];
		$order['types_cn']=$data['types_cn'];
		if(isset($data['address'])){
        $order['mail_name']=$data['address']['name'];
        $order['mail_tel']=$data['address']['tel'];
        $order['mail_province']=$data['address']['province'];
        $order['mail_city']=$data['address']['city'];
        $order['mail_area']=$data['address']['area'];
        $order['mail_town']=$data['address']['town'];
        $order['mail_address']=$data['address']['address'];
		}
        $order['cost']=$data['sum_cost'];
        $order['made_01']=$data['sum_made_01'];
        $order['made_02']=$data['sum_made_02'];
        $order['made_03']=$data['sum_made_03'];
        $order['cid']=$data['cart'][0]['data'][0]['pro']['id'];
		$money=$order['sum_price']+$order['sum_mail']-$order['integral_dk_money']-$order['red_money'];
		$order['money']=$money;
		$order['status']='未支付';
		$or_res=$this->orderM->save_by_oid($order);
		empty($or_res) && error('写入订单失败',400);   
		
		foreach($data['cart'] as $vos){
			foreach($vos['data'] as $vo){
				$product=[];
				$product['oid']=$or_res['oid'];
				$product['pid']=$vo['pro']['id'];
				$product['title']=$vo['pro']['title'];
				$product['send_score']=$vo['pro']['send_score'];
				$product['piclink']=$vo['pro']['piclink'];
				$product['sku_id']=$vo['sku_id'];
				$product['sku_cn']=$vo['sku_cn'];
				$product['cost']=$vo['pro']['cost'];
				$product['uid']=$user['id'];
				$product['sid']=$vos['info']['sid'];
				if(isset($vos['info']['mail'])){
				$product['mail']=$vos['info']['mail'];
				}
				$product['number']=$vo['number'];
				$product['money']=$vo['pro']['sum_price'];
				$product['is_virtual']=$vo['pro']['is_virtual'];
				$product['types']=$vo['pro']['types'];
				$product['price']=$vo['pro']['price'];
				$product['score_rob']=$vo['pro']['score_rob'];
				$product['integral_dk_per']=$vo['pro']['integral_dk_per'];
				$product['made_01']=$vo['pro']['made_01'];
				$product['made_02']=$vo['pro']['made_02'];
				$product['made_03']=$vo['pro']['made_03'];
				$res=$this->OrderProductM->save($product);
				empty($res) && error('写入订单商品失败',400);    
			}
		}

		//定制下单
		if($made){
			$ctrlfile=MADE.'/'.$made.'/service/order.php';
			if(is_file($ctrlfile)){
			$cltrlClass='\made\\'.$made.'\service\order';
			$made_S=new $cltrlClass();
			$made_S->save($or_res);
			}
		}

		//活动商品
		if($data['types']){
			switch ($data['types'])
			{
			case 2:
				//积分兑换
				if($data['score_rob']>0){
					$this->money_S->minus($user['id'],$data['score_rob'],'integral','integral_dk',$or_res['oid'],$user['id'],'积分兑换');
				}
				break;
			case 3:
				//砍价
				break;
			case 4:
				//拼团
				$group_ar['uid']=$user['id'];
				$group_ar['pid']=$or_res['cid'];
				$group_ar['oid']=$or_res['id'];
				if($data['cart'][0]['data'][0]['group_id']){
					$group_res=(new \app\model\groups())->have(['id'=>$data['cart'][0]['data'][0]['group_id'],'status'=>0]);
					empty($group_res) && error('拼团已满',10007);
					$group_ar['head_oid']=$group_res['head_oid'];
					$group_ar['group_people']=$group_res['group_people'];
					$group_ar['group_time']=$group_res['group_time'];
					$group_ar['group_discount']=$group_res['group_discount'];
					$group_ar['group_face']=$group_res['group_face'];
					$group_ar['end_time']=$group_res['end_time'];
				}else{
					$product_ar=$data['cart'][0]['data'][0]['pro'];
					$group_ar['head_oid']=$or_res['id'];
					$group_ar['group_people']=$product_ar['group_people'];
					$group_ar['group_time']=time();
					$group_ar['group_discount']=$product_ar['group_discount'];
					$group_ar['group_face']=$product_ar['group_face'];
					$group_ar['end_time']=time()+$product_ar['group_time']*3600;
				}
				(new \app\model\groups())->save($group_ar);
				break;
			case 5:
				//众筹
				break;
			case 6:
				//预约商品
				break;
			case 7: 
				//限时特惠
				break;
			default:
			}
		}

	
		//积分抵扣
		if($data['integral']['is_integral']==1 && $data['integral']['integral_dk_money']>0){
            $this->money_S->minus($user['id'],$data['integral']['integral_dk_money'],'integral','integral_dk',$or_res['oid'],$user['id'],'积分抵用');
		}
		//红包
		if(isset($data['red'])){
			(new \app\model\coupon())->up($querys['red_id'],['is_use'=>1,'oid'=>$or_res['oid'],'use_time'=>time()]);
		}
		//扣库存
		$this->stock_S->buckle_inventory($data['cart']);
		//加销量
		$this->stock_S->increase_sales($data['cart']);
		//清空购物车
		$id_str = post('id_str');
		$id_ar = explode('@',$id_str);
		$where['id']=$id_ar;
		$where['uid']=$GLOBALS['user']['id'];
		$res=$this->cart_M->del_all($where);
		empty($res) && error('删除购物车失败',400);		
		
		$Model->run();
		$redis->exec();
		return $or_res['id'];
	}


	/*订单列表*/
	public function lists(){
		(new \app\validate\AllsearchValidate())->goCheck();	
		(new \app\validate\PageValidate())->goCheck();
		$oid    			= post('oid');    
		$status          	= post('status');  
		$where['uid'] = $GLOBALS['user']['id'];
		if($oid){
			$where['OR']['oid[~]'] = $oid;
			$where['OR']['oid']=$this->order_pro_M->lists_all(['title[~]'=>$oid],'oid');
		}
		if($status){
			if($status=='退货中' || $status=='已退单'){
				$where['is_return'] = 1;
			}elseif($status=='待评价'){
				$where['status'] = ['已完成'];
				$where['is_review']=0;
			}elseif($status=='已支付'){
				$where['status'] = ['已支付','配货中'];
			}elseif($status=='未支付'){
				$where['is_pay'] = 0;
			}else{
				$where['status'] = $status;
			}
		}
		$where['AND']['status[!]']='已关闭';
		$page=post("page",1);
		$page_size = post("page_size",10);		
		$data=$this->orderM->lists($page,$page_size,$where);
       	return $data; 
	}


	/*订单详情*/
	public function detail(){
        (new \app\validate\IDMustBeRequire())->goCheck();
		$id=post("id");
		$where['id']=$id;
		$where['uid']=$GLOBALS['user']['id'];
		$data = $this->orderM->have($where);
		empty($data) && error('订单不存在',10007);
		if($data['types']==4){
			//拼团
			$data['groups']=(new \app\model\groups())->have(['oid'=>$id]);
			$data['groups']['lists']=(new \app\model\groups())->lists_all(['head_oid'=>$data['groups']['head_oid'],'is_pay'=>1]);
			foreach($data['groups']['lists'] as &$vo){
				$users=user_info($vo['uid']);
				$vo['nickname']=$users['nickname']?$users['nickname']:$users['username'];
				$vo['avatar']=$users['avatar'];
				$vo['end_time_second']=$vo['end_time']-time();
				$vo['difference']=$vo['group_people']-$vo['now_people'];
			}
		}
        $data['mail'] =  (new \app\service\kdn_inquire())->index($data);
		$product =  $this->order_pro_M->find_by_oid($data['oid']);   
		foreach($product as &$rs){
			if(!($data['status']!='已发货' && $data['status']!='已支付' && $data['status']!='配货中')){
				switch ($rs['status'])
				{
				case 0:
					if(!($data['types']==1 && c('fksfjfl')==1) && $data['is_settle']==0){
						$rs['order_return']='申请退货';
					}
					break;  
				case 1:
					$rs['order_return']='申请退货中';
					break;
				case 2:
					$rs['order_return']='提交运单';
					break;
				case 3:
					$rs['order_return']='待退款';
					break;
				case 4:
					$rs['order_return']='退货成功';
					break;
				default:
				}
			}
			if($data['types']==2){
				$rs['order_return']='';
			}
			if($data['types']==4 && $data['status']=='已支付' && $data['groups']['status']==0){
				$rs['order_return']='';
			}
		}
		
		$data['product'] =  $product;
		if($data['sid'] == 0 ){
			$data['sid_cn'] = c('head').'自营';
		}else{
			$data['sid_cn'] = user_info($rs['sid'],'shop_title'); 
		}
		return $data;
	}

	//物流
	public function mail()
	{
		(new \app\validate\IDMustBeRequire())->goCheck();
		$id=post("id");
		$where['id']=$id;
		$where['uid']=$GLOBALS['user']['id'];
		$data = $this->orderM->have($where);
		empty($data) && error('订单不存在',10007);
        $data['mail'] =  (new \app\service\kdn_inquire())->index($data);
		return $data;
	}

	//确认收货
	public function confirm()
	{
		(new \app\validate\IDMustBeRequire())->goCheck();
		$id=post("id");
		$where['id']=$id;
		$where['uid']=$GLOBALS['user']['id'];
		$where['status']='已发货';
		$where['is_pay']=1;
		$where['is_pay']=1;
		$order = $this->orderM->have($where,['is_return']);
		empty($order) && error('订单不存在',10007);
        if($order['is_return']==1){
			error('退货中',404);
		}
		flash_god($GLOBALS['user']['id']);
		$redis = new \core\lib\redis();
        $Model = new \core\lib\Model();
        $Model->action();
		$redis->multi();
		$this->orderM->up($id,['status'=>'已完成','complete_time'=>time()]);
		$res=(new \app\service\order_complete())->complete($id);
		if($res!==true){
            error('结算错误'.$res,404);
		}
		//cs($this->orderM->log(),1);
		$Model->run();
        $redis->exec();
		return "确认完成";
	}

	
	/*选择活动 商品类型 （限时抢购,积分兑换*/
	public function order_type()
	{
		$product_M = new \app\model\product();
		$ar = $product_M->types();
		return $ar;
	}
	
}