<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-20 14:44:12
 * Desc: 商品SKU属性控制器
 */

namespace app\ctrl\admin;

use app\model\sku as SkuModel;
use app\validate\SkuValidate;
use app\validate\IDMustBeRequire;

class sku extends BaseController{
	
	public $sku_M;
	public function __initialize(){
		$this->sku_M = new SkuModel();
	}

	/*按id查找*/
    public function edit(){
    	$id = post('id');
    	(new IDMustBeRequire())->goCheck();
    	$data = $this->sku_M->find($id);
		empty($data) && error('数据不存在',404);    	
		//上级栏目ID串
		$menu_id =$id;
		$ar = $this->find_up2($menu_id);
		array_push($ar,$menu_id);
		$str = implode('@',$ar);
		$data['up_id'] = $str;
        return $data;      
    }	


	public function find_up2($menu_id,$ar=[]){
        $parent_id  = $this->sku_M->up_id($menu_id);
        if($parent_id==0){
            return array_reverse($ar);
        }else{
            $ar[] = $parent_id;
            return $this->find_up2($parent_id,$ar);
        }
    }


    /*保存*/
	public function saveadd(){
		(new SkuValidate())->goCheck('scene_add');
		$data = post(['title','description','parent_id','show','sort','is_pic']);		
		$data['parent_title']=$this->sku_M->find($data['parent_id'],'title');
 		$cate_id = $data['parent_id'];
 		if($cate_id>0){
 			$ar = $this->sku_M->find_father($cate_id,[$cate_id]);
 			$num = count($ar);	
 			if($num >2){
 				error('添加级别不能超过三级',400);
 			}
 		}

		$res=$this->sku_M->save($data);
		empty($res) && error('添加失败',400);	
		$this->redis_sku();
		admin_log('保存sku',$res);   
		return $res;
	}

	/*按id删除*/
	public function del(){
		$id = post('id');
		(new SkuValidate())->goCheck('scene_find');
		$is_have_son = $this->sku_M->find_cate_son($id);
		!empty($is_have_son) && error('请先删除小类',400);
		$res=$this->sku_M->del($id);
		empty($res) && error('删除失败',400);
		$this->redis_sku();
		admin_log('删除sku',$id);   
		return $res;
	}

	/*按id修改*/
	public function saveedit()
	{	
		$id = post('id');
    	(new SkuValidate())->goCheck('scene_find');
    	$data = post(['title','description','parent_id','show','sort','is_pic']);
		$data['parent_title']=$this->sku_M->find($data['parent_id'],'title');


    	$one = $this->sku_M->find($id);
		$parent_id = post('parent_id');
		if($parent_id!=$one['parent_id']){
			if($parent_id == $id){
    		error('不能移到自已下面',400);
    		}

    		//step_1:该栏目下面没有子级才能移动
			$is_have = $this->sku_M->find_cate_son($id);
	   		if($is_have){
	   			error('该栏目下有子类,暂不能移动',400);
	   		}

	   		//step_2: 栏目只能三级 即post('parent_id') 上面不能超过或等于三级
	   		$ar = $this->sku_M->find_father($parent_id);
	   		$ar_num = count($ar);
	   		if($ar_num>=2){
	   			error('移动到的栏目不能超过三级',400);
	   		}
   		}

		$res=$this->sku_M->up($id,$data);
		$this->redis_sku();
		empty($res) && error('修改失败',404);
		admin_log('修改sku',$id);   
 		return $res; 
	}

	/*查子类*/
	public function lists_son()
	{
		$parent_id = post('parent_id');
		(new SkuValidate())->goCheck('scene_list');		
		$data=$this->sku_M->lists_all($parent_id);
        return $data; 
	}


	/*sku树*/
	public function lists_tree(){
		(new SkuValidate())->goCheck('scene_list');
		$parent_id = post('parent_id',0);
		if($parent_id==0){
			$redis = new \core\lib\redis();
			$key = 'sku_list';
			$is_have = $redis->exists($key);
			if($is_have){
				$data = $redis->get($key); //从redis读取是空值时，也去读下数据库，为空时需弹提示
				if(!$data){
					$data=$this->find_tree($parent_id);
					$redis->set($key,$data);
				}
			}else{
				$data=$this->find_tree($parent_id);
				$redis->set($key,$data);
			}
		}else{
			$data=$this->find_tree($parent_id);
		}
		return $data;
	}


	public function find_tree($parent_id=0){
		$obj = $this->sku_M->tree($parent_id);
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
		$res = $this->sku_M->sort($ar,$parent_id);
		empty($res) && error('排序失败',400);
		admin_log('sku排序');   
		$this->redis_sku();
		return $res;
	}


	/*输入数字排序*/
	public function sort_by_number(){
		$sort = post('sort');
		$id = post('id');	
		$data['sort'] = $sort;
		$res = $this->sku_M->up($id,$data);
		empty($res) && error('排序失败',400);
		admin_log('sku排序',$id);   
		$this->redis_sku();
		return $res;
	}

	public function redis_sku()
	{
		$redis = new \core\lib\redis();
		$key = 'sku_list';
		$redis->del($key);
	}

}