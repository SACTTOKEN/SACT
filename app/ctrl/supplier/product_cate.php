<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-19 10:14:07
 * Desc: 商品类别控制器
 */

namespace app\ctrl\supplier;

use app\model\product_cate as product_cate_Model;

class product_cate extends BaseController{
	
	public $product_cate_M;
	public function __initialize(){
		$this->product_cate_M = new product_cate_Model();
	}

	

	/*商品分类树*/
	public function lists_tree(){
		$parent_id = post('parent_id',0);
		//(new ProductCateValidate())->goCheck('scene_list');
		return $this->find_tree($parent_id);
	}


	public function find_tree($parent_id=0){
		$obj = $this->product_cate_M->tree($parent_id);
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

	




}