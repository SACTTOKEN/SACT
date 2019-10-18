<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-14 17:25:08
 * Desc: 购物车控
 */
namespace app\ctrl\mobile;

use app\model\cart as CartModel;
use app\service\stock;
use app\validate\CartValidate;
use app\ctrl\mobile\BaseController;
use \app\model\product as ProductModel;

class cart extends BaseController
{
	public $cart_M;
	public $stock_S;
	public $pro_M;
	public $product_sku_M;

	public function __initialize(){
		$this->cart_M = new CartModel();
		$this->stock_S = new stock();
		$this->pro_M = new ProductModel();
		$this->product_sku_M = new \app\model\product_sku();
	}

	public function number()
	{		
        $pid=$this->pro_M->lists_all(['types'=>0],'id');
		$number=$this->cart_M->new_count(['uid'=>$GLOBALS['user']['id'],'pid'=>$pid]);
		return $number;
	}

	/*添加购物车*/
	public function add_cart(){
		if(c('reg_permission')){
			if(empty($GLOBALS['user']['tid']) || $GLOBALS['user']['tid']==''){
				error('需要推荐人才能下单',404);
			}
		}
		if(empty($GLOBALS['user']['tel'])){
			error('请先绑定手机号',404);
		}
		(new CartValidate())->gocheck('scene_add_cart');
		$data = post(['pid','sku_iden','number']);

		$where['id']=$data['pid'];
		$where['is_check']=1;
		$where['show']=1;
		$product_ar=$this->pro_M->have($where);
		empty($product_ar) && error('商品已下架',404);
		
		//活动商品
		if($product_ar['types']){
			switch ($product_ar['types'])
            {
            case 2:
				$data['number']=1;	//积分兑换
                break;
            case 3:
                //砍价
                break;
            case 4:
				//拼团
				$group_types=post('group_types',0);
				$group_id=post('group_id',0);
				if($group_types==1){
					$group_id=0;
				}
				if($group_id){
					$group_res=(new \app\service\group())->judge($group_id);
					if($group_res['status']==0){
						error($group_res['msg'],404);
					}
				}
				$car['group_types']=$group_types;
				$car['group_id']=$group_id;
                break;
            case 5:
                //众筹
                break;
            case 6:
                //预约商品
                break;
            case 7: 
				//限时特惠
				if($product_ar['real_sale']+$data['number']>$product_ar['discount_limit']){
					error('本商品活动已结束，您可以参加其他商品活动',404);
				}
				$rob_time_M = new \app\model\rob_time();
				$where_hd['id']=$product_ar['time_id'];
				$where_hd['begin_time[<]']=time();
				$where_hd['end_time[>]']=time();
				$rob_ar=$rob_time_M->have($where_hd);
				empty($rob_ar) && error('活动未开始',404);
                break;
            default:
            }
		}

		//判断库存
		$sku_iden = isset($data['sku_iden']) ? $data['sku_iden'] : '';
		$sku_id=$this->product_sku_M->have(['pid'=>$data['pid'],'iden'=>$sku_iden],'id');
		empty($sku_id) && error('请选择属性',400);
		$is_have_stock=$this->stock_S->have_stock($sku_id,$data['number']);
		empty($is_have_stock) && error('库存不足',400);
		
		//判断限购
		$this->stock_S->limit($product_ar,$sku_id,$data['number']);
		
		//入购物车
		$car_where['pid']=$data['pid'];
		$car_where['sku_id']=$sku_id;
		$car_where['uid']=$GLOBALS['user']['id'];
		$res=$this->cart_M->have($car_where,'id');
		if($res){
			$this->cart_M->up($res,['number'=>$data['number']]);
		}else{
			$iden_ar = explode('@',$data['sku_iden']);
			$sku_cn = '';                
			$pro_attr_M = new \app\model\product_attr();
			foreach($iden_ar as $attr){
				if($attr){
				$attr_ar = explode(':',$attr);               
				$sku = $pro_attr_M->findme($data['pid'],$attr_ar[0],$attr_ar[1]);                        
				$sku_cn .= $sku['parent_title'].":".$sku['sku_title']." ";  
				}                         
			}  
			$car['pid']=$data['pid'];
			$car['sku_id']=$sku_id;
			$car['uid']=$GLOBALS['user']['id'];
			$car['sid']=$product_ar['sid'];
			$car['number']=$data['number'];
			$car['sku_cn']=$sku_cn;
			$res = $this->cart_M->save($car);
		}
		return $res;
	}


	/*购物车列表*/
	public function lists(){
		$uid = $GLOBALS['user']['id'];
        $data = $this->cart_M->lists_all(['uid'=>$uid]);  
		$car_ar=[];
		foreach($data as &$rs){
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
			if($product_ar['types']!=0){
				continue;
			}
			$product_ar['price'] = $this->stock_S->price($GLOBALS['user']['rating'],$product_ar,$rs['sku_id'],0);
			if($product_ar['price']<=0){
				$this->cart_M->del($rs['id']);
				continue;
			}
			$product_ar['sum_price'] = $rs['number']*$product_ar['price'];
			if(!isset($car_ar[$rs['sid']]['info'])){
				if($rs['sid']==0){
					$shop['shop_title']=c('head');
					$shop['shop_logo']=c('logo');
				}else{
					$users=user_info($rs['sid']);
					$shop['shop_title']=$users['shop_title']?$users['shop_title']:$users['nickname'];
					$shop['shop_logo']=isset($users['shop_logo'])?$users['shop_logo']:'';
				}
				$car_ar[$rs['sid']]['info']=$shop;
			}
			$rs['pro']=$product_ar;
			$car_ar[$rs['sid']]['data'][]=$rs;
		}
		sort($car_ar);
        return $car_ar;    
	}


	/*购物车商品数量变化*/
	public function cart_num(){
		(new CartValidate())->gocheck('scene_add_cart');
		(new \app\validate\IDMustBeRequire())->goCheck();
		$number = post('number');
		$id = post('id');
		$car_where['id']=$id;
		$car_where['uid']=$GLOBALS['user']['id'];
		$data=$this->cart_M->have($car_where);
		empty($data) && error('购物车不存在',404);

		$where['id']=$data['pid'];
		$where['is_check']=1;
		$where['show']=1;
		$where['types']=0;
		$product_ar=$this->pro_M->have($where);
		empty($product_ar) && error('商品已下架',404);
		
		//判断库存
		$is_have_stock=$this->stock_S->have_stock($data['sku_id'],$number);
		empty($is_have_stock) && error('库存不足',400);
		
		//判断限购
		$this->stock_S->limit($product_ar,$data['sku_id'],$number);
		
		//入购物车
		$this->cart_M->up($id,['number'=>$number]);
		return $id;
	}

	/*批量删除*/
	public function del_all(){
		(new CartValidate())->goCheck('scene_checkID');
		$id_str = post('id_str');
		$id_ar = explode('@',$id_str);
		$where['id']=$id_ar;
		$where['uid']=$GLOBALS['user']['id'];
		$res=$this->cart_M->del_all($where);
	
		empty($res) && error('删除失败',400);		
		return $res;
	}


}