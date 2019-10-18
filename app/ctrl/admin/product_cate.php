<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-19 10:14:07
 * Desc: 商品类别控制器
 */

namespace app\ctrl\admin;

use app\model\product_cate as product_cate_Model;
use app\validate\ProductCateValidate;
use app\validate\IDMustBeRequire;
use app\ctrl\admin\BaseController;

class product_cate extends BaseController{
	
	public $product_cate_M;
	public function __initialize(){
		$this->product_cate_M = new product_cate_Model();
	}

	

    /*保存*/
	public function saveadd(){
		$data = post(['title','piclink','description','parent_id','sku_id','show','sort']);
		(new ProductCateValidate())->goCheck('scene_add');

		$cate_id = $data['parent_id'];
 		if($cate_id>0){
 			$ar = $this->product_cate_M->find_father($cate_id,[$cate_id]);
 			$num = count($ar);	
 			if($num >2){
 				error('添加级别不能超过三级',400);
 			}
 		}


		$res=$this->product_cate_M->save($data);
		empty($res) && error('添加失败',400);	 
		admin_log('添加产品分类',$res);
		return $res;
	}

	/*按id删除*/
	public function del(){
		$id = post('id');
		(new ProductCateValidate())->goCheck('scene_find');
		$is_have_son = $this->product_cate_M->find_cate_son($id);
		!empty($is_have_son) && error('请先删除小类',400);
		$productM = new \app\model\product();
		$is_have = $productM->is_have_cate($id);
		!empty($is_have) && error('请先删除该分类信息',400);

		$res=$this->product_cate_M->del($id);
		empty($res) && error('删除失败',400);
		admin_log('删除产品分类',$id);
		return $res;
	}

	/*按id查找*/
    public function edit(){
    	$id = post('id');
    	(new IDMustBeRequire())->goCheck();
    	$data = $this->product_cate_M->find($id);
    	empty($data) && error('数据不存在',404);    	

		//上级栏目ID串
		$menu_id =$id;
		$ar = $this->find_up2($menu_id);
		array_push($ar,$menu_id);
		$str = implode('@',$ar);
		$data['up_id'] = $str;

		empty($data) && error('添加失败',404);  
        return $data;      
    }	

    public function find_up2($menu_id,$ar=[]){
        $parent_id  = $this->product_cate_M->up_id($menu_id);
        if($parent_id==0){
            return array_reverse($ar);
        }else{
            $ar[] = $parent_id;
            return $this->find_up2($parent_id,$ar);
        }
    }


	/*按id修改*/
	public function saveedit()
	{	
		$id = post('id');
		$parent_id = post('parent_id');
    	(new ProductCateValidate())->goCheck('scene_find');

    	$one = $this->product_cate_M->find($id);

    	$data = post(['title','piclink','description','parent_id','sku_id','show','sort']);
		
		if($parent_id!=$one['parent_id']){
			if($parent_id == $id){
    		error('不能移到自已下面',400);
    		}

	   		if($one['parent_id']==0 && $parent_id>0){
    		error('顶级栏目不可接到其它级下面',400);
    		}

			//step_1:该栏目下面没有子级才能移动
	   		$is_have = $this->product_cate_M->find_cate_son($id);
	   		if($is_have){
	   			error('该栏目下有子类,暂不能移动',400);
	   		}

	   		//step_2: 栏目只能三级 即post('parent_id') 上面不能超过或等于三级
	   		$ar = $this->product_cate_M->find_father($parent_id);
	   		$ar_num = count($ar);
	   		if($ar_num>=2){
	   			error('移动到的栏目不能超过三级',400);
	   		}

		}
    	

		$res=$this->product_cate_M->up($id,$data);
		empty($res) && error('修改失败',404);
		admin_log('修改商品分类',$id);
 		return $res; 
	}

	/*查子类*/
	public function lists_son()
	{
		$parent_id = post('parent_id');
		(new ProductCateValidate())->goCheck('scene_list');		
		$data=$this->product_cate_M->lists_all($parent_id);
        return $data; 
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

	/*栏目排序 sort_str 用@分隔的id字符串*/
	public function menu_sort(){
		$sort_str = post('sort_str');
		$parent_id = post('parent_id');

		$ar = [];
		if(!empty($sort_str)){
			$ar = explode('@',$sort_str);
		}
		empty($ar) && error('排序失败',400);
		
		$ar = array_reverse($ar);
		$res = $this->product_cate_M->sort($ar,$parent_id);
		empty($res) && error('排序失败',400);
		admin_log('修改分类排序');
		return $res;
	}



	/*输入数字排序*/
	public function sort_by_number(){
		$sort = post('sort');
		$id = post('id');	
		$data['sort'] = $sort;
		$res = $this->product_cate_M->up($id,$data);
		empty($res) && error('排序失败',400);
		admin_log('修改分类排序',$id);
		return $res;
	}








}