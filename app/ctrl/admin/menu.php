<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2018-12-13 16:36:22
 * Desc: 栏目控制器
 */

namespace app\ctrl\admin;

use app\model\menu as MenuModel;
use app\validate\MenuValidate;
use app\ctrl\admin\BaseController;
use app\validate\IDMustBeRequire;



class menu extends BaseController{
	public $menuM;
	public function __initialize(){
		$this->menuM = new MenuModel();	
	}
	
	/*栏目列表*/
	public function lists()
	{				
		$lists = $this->menuM->lists_all();		
		return $lists;
	}

	
    /*栏目添加,返回int 添加成功id,0为未添加*/   
    public function saveadd()
	{			
		(new MenuValidate())->goCheck();
		$data = post(['title','control','action','parent_id','desc','link','show','style','url','piclink','url_details']);

		$cate_id = $data['parent_id'];
 		if($cate_id>0){
 			$ar = $this->menuM->find_father($cate_id,[$cate_id]);
 			$num = count($ar);	
 			if($num >2){
 				error('添加级别不能超过三级',400);
 			}
 		}

		$res=$this->menuM->save($data);
		empty($res) && error('添加失败',400);  
		admin_log('添加后台栏目',$res);    
		return $res;
	}

	
    /*栏目修改界面*/
    public function edit()
	{			
		(new IDMustBeRequire())->goCheck();
		
		$id=post("id");
		$res=$this->menuM->is_find($id);
		empty($res) && error('数据不存在',400); 
		
		$res=$this->menuM->find($id);

		//上级栏目ID串
		$menu_id =$id;
		$ar = $this->find_up2($menu_id);
		array_push($ar,$menu_id);
		$str = implode('@',$ar);
		$res['up_id'] = $str;

		empty($res) && error('添加失败',404);  
		return $res;
	}


	/*栏目保存修改*/
	public function saveedit(){
		(new IDMustBeRequire())->goCheck();
		(new MenuValidate())->goCheck();
		
		$id=post("id");
		$one=$this->menuM->find($id);
		empty($one) && error('数据不存在',400); 
		
		$data = post(['title','control','action','parent_id','desc','link','show','url','piclink','url_details']);
		if($id == post('parent_id')){
			$data['parent_id'] = 0; //VUE编辑主栏目时会传一样的
		}

		$parent_id = post('parent_id');
		if($parent_id!=$one['parent_id']){
			if($parent_id == $id){
    		error('不能移到自已下面',400);
    		}

    		//step_1:该栏目下面没有子级才能移动
			$is_have = $this->menuM->find_cate_son($id);
	   		if($is_have){
	   			error('该栏目下有子类,暂不能移动',400);
	   		}

	   		//step_2: 栏目只能三级 即post('parent_id') 上面不能超过或等于三级
	   		$ar = $this->menuM->find_father($parent_id);
	   		$ar_num = count($ar);
	   		if($ar_num>=2){
	   			error('移动到的栏目不能超过三级',400);
	   		}
   		}

		$res=$this->menuM->up($id,$data);
		empty($res) && error('添加失败1',404); 
		admin_log('修改后台栏目',$id);    
 		return $res; 
	}

	/*是否显示*/
	public function show()
	{
		(new IDMustBeRequire())->goCheck();	
		$id=post("id");
		$data['show'] = post('show');
		$res=$this->menuM->up($id,$data);
		empty($res) && error('修改失败',404);
		if($data['show']!=1){
		admin_log('禁用栏目',$id);  
		}else{
		admin_log('启用栏目',$id); 	
		} 
 		return $res; 
	}

	/*栏目删除*/
	public function del(){
		(new IDMustBeRequire())->goCheck();
	
		$id=post("id");
		$res=$this->menuM->is_find($id);
		empty($res) && error('数据不存在',400); 
		
		$res=$this->menuM->del($id);
		empty($res) && error('删除失败',404);

		admin_log('删除栏目',$id);

 		return $res; 
	}


	/*栏目树,权限限制*/
	public function list_tree(){
		$parent_id = post('parent_id',0);
		return $this->find_tree($parent_id);
	}
	
	public function find_tree($parent_id=0){
		$obj = $this->menuM->tree($parent_id);
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

	/*栏目树 无权限限制*/
	public function list_tree_free(){
		$parent_id = post('parent_id',0);
		return $this->find_tree_free($parent_id);
	}


	public function find_tree_free($parent_id=0){
		$obj = $this->menuM->tree_free($parent_id);
		if(!empty($obj)){

		foreach($obj as $rs){
			$res = $this->find_tree_free($rs['id']);
			if($res){
				$rs['z'] =$res; 
			}
			$ar[] = $rs;
		}
		return $ar;
		}
	}


	/*所有二级栏目 供vue角色添加用*/
	public function second_menu(){
		$ar = $this->list_tree_free();	
		
 		$new_ar = [];

		$i=1;
		$pre_parent_id = 0;
		foreach($ar as $key=>$rs){
			$all_id=[];
		 	if( isset($rs['z']) && is_array($rs['z']) ){
		 		    $num = count($rs['z']);
		 		    foreach($rs['z'] as &$one){
		 		    	$one['sister'] = $num;	    	
		 		    	if($pre_parent_id != $one['parent_id']){
							$one['brother'] = $i;
		 		    		$pre_parent_id = $one['parent_id'];
		 		    	}else{
		 		    		$one['brother'] = 0;
		 		    	}
		 		    	$xiaji_id=[];
		 		    	if( isset($one['z']) && is_array($one['z']) ){
		 		    	foreach($one['z'] as $vo){
		 		    		$xiaji_id[]=$vo['id'];
		 		    		$all_id[]=$vo['id'];
		 		    	}
		 		    	$one['xiaji_id'] = $xiaji_id;	
		 		   		}
		 		   		$all_id[]=$one['id'];
		 		    	$i++; 			 		    	
		 		    }
		 		    foreach($rs['z'] as &$one){
		 		    	$one['all_id'] = $all_id;	
		 		    }

		 		$new_ar = array_merge($new_ar,$rs['z']);

		 	}		
					
		}
		return $new_ar;
	
	
	}


	/*栏目*/
	public function find_tree_one(){
		$parent_id = post('id',0);
		$obj = $this->menuM->tree($parent_id);
		return $obj;
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
		$res = $this->menuM->sort($ar,$parent_id);
		empty($res) && error('排序失败',400);
		admin_log('后台栏目排序',$res);    
		return $res;
	}



	/*栏目上级ID串*/
	public function list_up(){
		(new IDMustBeRequire())->goCheck();
		$menu_id = post('id');
		$ar = $this->find_up($menu_id);
		//array_push($ar,$menu_id); //拼接自身查找的ID
		$str = implode('@',$ar);
		return $str;		
	}

	public function find_up($menu_id=0,$ar=[]){
		$pid = $this->menuM->up_id($menu_id);

		if(!empty($pid)){
				$ar[] = $pid;
				return $this->find_up($pid,$ar);			
		}else{
			
			if(is_array($ar)){
				$ar  = array_reverse($ar); 		
				return $ar;
			}
		}

	}


	public function find_up2($menu_id,$ar=[]){
        $parent_id  = $this->menuM->up_id($menu_id);
        if($parent_id==0){
            return array_reverse($ar);
        }else{
            $ar[] = $parent_id;
            return $this->find_up2($parent_id,$ar);
        }
    }


    /*输入数字排序*/
	public function sort_by_number(){
		$sort = post('sort');
		$id = post('id');	
		$data['sort'] = $sort;
		$res = $this->menuM->up($id,$data);
		empty($res) && error('排序失败',400);		
		admin_log('后台栏目排序',$id);    
		return $res;
	}









}