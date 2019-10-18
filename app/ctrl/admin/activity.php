<?php

/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-07-27 14:51:11
 * Desc: 限时抢购
 */

namespace app\ctrl\admin;

use app\model\rob_time as RobTimeModel;
use app\model\product as productModel;
use app\validate\IDMustBeRequire;
use app\validate\ActivityValidate;

class activity extends BaseController
{

	public $rob_time_M;
	public $productM;
	public $product_S;
	public function __initialize()
	{
		$this->rob_time_M = new RobTimeModel();
		$this->productM = new ProductModel();
		$this->product_S = new \app\service\product();
	}

    
	/*选择活动 商品类型 （限时抢购,积分兑换*/
	public function product_type()
	{
		$product_M = new \app\model\product();
		$ar = $product_M->types();
		$ar = array_diff($ar, ['普通商品', '会员商品']);
		return $ar;
	}
	
	/*设定该商品抢购价格,并复制一个全新的商品出来*/
	public function index()
	{
		(new IDMustBeRequire())->goCheck();
		$id = post('id');
        $types = post('types');
		$product_M = new \app\model\product();
		$ar = $product_M->types($types);
        empty($ar) && error('插件未购买',404);
        $new_id=$this->product_S->cope($id);
        empty($new_id) && error('添加失败', 404);
        switch ($types)
        {
        case 2:
            $data=$this->redeem(); //积分兑换
            break;
        case 3:
            //砍价
            break;
        case 4:
            //拼团
            $data=$this->group();
            break;
        case 5:
            //众筹
            break;
        case 6:
            //预约商品
            break;
        case 7: 
            $data=$this->limited_time();    //限时特惠
            break;
        default:
            error('商品活动类型错误',404);
        }
        
        $data['types']=$types;
        $res=$this->productM->up($new_id,$data);
        empty($res) && error('添加失败',404);
        return '添加成功';
	}


    //拼团
    public function group()
    {
        (new ActivityValidate())->goCheck('group');
        $data=post(['group_people','group_discount','group_time','group_face']);
        return $data;
    }

    public function edit_group()
    {
        $id = post('id');
		(new IDMustBeRequire())->goCheck();
        (new ActivityValidate())->goCheck('group');
        $xs=$this->productM->have(['id'=>$id,'types'=>4]);
        empty($xs) && error('商品不存在',404);
        $data=post(['group_people','group_discount','group_time','group_face']);
        $res=$this->productM->up($id,$data);
        empty($res) && error('修改失败',404);
        return '修改成功';
    }


    //限时特惠
    public function limited_time()
    {
		(new ActivityValidate())->goCheck('limited_time');
        $data=post(['time_id','discount_limit','discount_rob']);
        return $data;
    }

    //编辑限时抢购
    public function edit_limited_time()
    {
        $id = post('id');
		(new IDMustBeRequire())->goCheck();
        (new ActivityValidate())->goCheck('limited_time');
        $xs=$this->productM->have(['id'=>$id,'types'=>7]);
        empty($xs) && error('商品不存在',404);
        $data=post(['time_id','discount_limit','discount_rob']);
        $res=$this->productM->up($id,$data);
        empty($res) && error('修改失败',404);
        return '修改成功';
    }

    //积分兑换
	public function redeem()
	{
		(new ActivityValidate())->goCheck('redeem');
		$data = post(['score_rob']);
        return $data;
    }
    
    //编辑积分兑换
    public function edit_redeem()
    {
        $id = post('id');
		(new IDMustBeRequire())->goCheck();
        (new ActivityValidate())->goCheck('redeem');
        $xs=$this->productM->have(['id'=>$id,'types'=>2]);
        empty($xs) && error('商品不存在',404);
        $data=post(['score_rob']);
        $res=$this->productM->up($id,$data);
        empty($res) && error('修改失败',404);
        return '修改成功';
    }
}
