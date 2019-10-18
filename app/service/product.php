<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-04-11 13:40:31
 * Desc: 商品
 */
namespace app\service;
use app\model\product as productModel;

class product{

	public $productM;
    public function __construct()
    {
		$this->productM = new ProductModel();
    }
  
	public function find_tree($parent_id=0)
	{
		$obj = (new \app\model\product_cate())->tree($parent_id);
		if(!empty($obj)){
		foreach($obj as $rs){
			$res = $this->find_tree($rs['id']);
			if($res){
				$rs['z'] =$res; 
			}
			$ar[] = $rs;
		}
		return $ar;
		}
    }
	
	public function find_tree_id($parent_id=0)
	{
		$obj = (new \app\model\product_cate())->tree($parent_id,'id');
		if($obj){
			foreach($obj as $rs){
              	if($rs!=$parent_id){
				$res = $this->find_tree_id($rs);
                }
				if($res){
					//$obj=array_merge($parent_id,$obj);
					$obj=array_merge($obj,$res);
				}
			}
			return $obj;
		}
	}

	public function cope($id)
	{
		$ar = $this->productM->find($id);
		unset($ar['id']);
		$ar['real_sale']=0;
		$new_id = $this->productM->save($ar);
		empty($new_id) && error('修改失败', 404);
		admin_log('复制商品', $new_id);

		//复制SKU  product_attr product_sku product_price images
		$product_attr_M = new \app\model\product_attr();
		$product_sku_M  = new \app\model\product_sku();
		$product_price_M = new \app\model\product_price();
		$image_M = new \app\model\image(); //aid,cate=product

		$where['pid'] = $id;
		$where_plus['aid'] = $id;
		$where_plus['cate'] = 'product';
		$ar_1 = $product_attr_M->lists_all($where);
		if ($ar_1) {
			foreach ($ar_1 as $one) {
				unset($one['id']);
				$one['pid'] = $new_id;
				$product_attr_M->save($one);
			}
		}
		$ar_2 = $product_sku_M->lists_all($where);
		if ($ar_2) {
			foreach ($ar_2 as $one) {
				$old_sku_id = $one['id'];
				unset($one['id']);
				unset($one['hid']);
				$one['pid'] = $new_id;
				$new_sku_id = $product_sku_M->save_back_id($one);
				$sku_change[$old_sku_id] = $new_sku_id; //product_price表的sku_id是product_sku表的id
				//echo $old_sku_id."==".$new_sku_id;
			}
		}


		$ar_3 = $product_price_M->lists_all($where);
		if ($ar_3) {
			foreach ($ar_3 as $one) {
				unset($one['id']);
				$one['pid'] = $new_id;
				$one['sku_id'] = $sku_change[$one['sku_id']];
				$product_price_M->save($one);
			}
		}

		$ar_4 = $image_M->lists_all($where_plus);
		if ($ar_4) {
			foreach ($ar_4 as $one) {
				unset($one['id']);
				$one['aid'] = $new_id;
				$image_M->save($one);
			}
		}
		return $new_id;
	}

}