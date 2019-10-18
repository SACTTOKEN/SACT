<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-19 10:14:07
 * Desc: 文章类别控制器
 */

namespace app\ctrl\admin;

use app\model\news_cate as news_cate_Model;
use app\validate\NewsCateValidate;
use app\validate\IDMustBeRequire;
use app\ctrl\admin\BaseController;

class news_cate extends BaseController{
	
	public $news_cate_M;
	public function __initialize(){
		$this->news_cate_M = new news_cate_Model();
	}


    /*保存*/
	public function saveadd(){
		$data = post(['title','parent_id','piclink','description','show','sort','is_collection','collection_iden','vue_type']);
		if(!plugin_is_open('sjcj')){
			$data['vue_type']=0;
			$data['is_collection']=0;
		}
		(new NewsCateValidate())->goCheck('scene_add');
		$cate_id = $data['parent_id'];
 		if($cate_id>0){
 			$ar = $this->news_cate_M->find_father($cate_id,[$cate_id]);
 			$num = count($ar);	
 			if($num >2){
 				error('添加级别不能超过三级',400);
 			}
 		}
		$res=$this->news_cate_M->save($data);
		empty($res) && error('添加失败',400);	 		
		admin_log('添加新闻栏目',$res);   
		return $res;
	}

	/*按id删除*/
	public function del(){
		$id = post('id');
		(new NewsCateValidate())->goCheck('scene_find');
		$is_have_son = $this->news_cate_M->find_cate_son($id);
		!empty($is_have_son) && error('请先删除小类',400);
		$newsM = new \app\model\news();
		$is_have = $newsM->is_have_cate($id);
		!empty($is_have) && error('请先删除该分类相关文章',400);

		$res=$this->news_cate_M->del($id);
		empty($res) && error('删除失败',400);		
		admin_log('删除新闻栏目',$id);   
		return $res;
	}

	
	/*按id查找*/
    public function edit(){
    	$id = post('id');
    	(new IDMustBeRequire())->goCheck();
    	$data = $this->news_cate_M->find($id);
    	empty($data) && error('数据不存在',404);    	
        return $data;      
	}	
	
	/*按id修改*/
	public function saveedit()
	{	
		$id = post('id');
    	(new NewsCateValidate())->goCheck('scene_find');
    	$data = post(['title','parent_id','piclink','description','show','sort','is_collection','collection_iden','vue_type']);
		if(!plugin_is_open('sjcj')){
			$data['vue_type']=0;
			$data['is_collection']=0;
		}
    	$one = $this->news_cate_M->find($id);
    	$parent_id = post('parent_id');
		if($parent_id!=$one['parent_id']){
			if($parent_id == $id){
    		error('不能移到自已下面',400);
    		}

	   		if($one['parent_id']==0 && $parent_id>0){
    		error('顶级栏目不可接到其它级下面',400);
    		}

			//step_1:该栏目下面没有子级才能移动
	   		$is_have = $this->news_cate_M->find_cate_son($id);
	   		if($is_have){
	   			error('该栏目下有子类,暂不能移动',400);
	   		}

	   		//step_2: 栏目只能三级 即post('parent_id') 上面不能超过或等于三级
	   		$ar = $this->news_cate_M->find_father($parent_id);
	   		$ar_num = count($ar);
	   		if($ar_num>=2){
	   			error('移动到的栏目不能超过三级',400);
	   		}
		}

		$res=$this->news_cate_M->up($id,$data);
		empty($res) && error('修改失败',404);		
		admin_log('修改新闻栏目',$id);   
 		return $res; 
	}

	/*查子类*/
	public function lists_son()
	{
		$parent_id = post('parent_id');
		(new NewsCateValidate())->goCheck('scene_list');		
		$data=$this->news_cate_M->lists_all($parent_id);
        return $data; 
	}


	/*新闻树*/
	public function lists_tree(){
		$parent_id = post('parent_id',0);
		(new NewsCateValidate())->goCheck('scene_list');
		return $this->find_tree($parent_id);
	}


	public function find_tree($parent_id=0){
		$obj = $this->news_cate_M->tree($parent_id);
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
		$res = $this->news_cate_M->sort($ar,$parent_id);
		empty($res) && error('排序失败',400);		
		admin_log('新闻栏目排序');   
		return $res;
	}



	/*栏目上级ID串*/
	public function list_up(){
		(new IDMustBeRequire())->goCheck();
		$menu_id = post('id');
		$ar = $this->find_up($menu_id);
		array_push($ar,$menu_id); //拼接自身查找的ID
		$str = implode('@',$ar);
		$menu_title = $this->news_cate_M->find($menu_id,'title');
		$str = $str."@".$menu_title;//vue要求拼上请求ID的中文名称
		return $str;		
	}

	public function find_up($menu_id=0,$ar=[]){
		$pid = $this->news_cate_M->up_id($menu_id);

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


	/*输入数字排序*/
	public function sort_by_number(){
		$sort = post('sort');
		$id = post('id');	
		$data['sort'] = $sort;
		$res = $this->news_cate_M->up($id,$data);
		empty($res) && error('排序失败',400);		
		admin_log('新闻栏目排序',$id);   
		return $res;
	}



	public function option_collection(){
		$data=  [
                ['value'=>'babite','label'=>'区块链快讯'],    //https://www.8btc.com/flash
                ['value'=>'jiemian','label'=>'界面快讯'], 	 //https://www.jiemian.com/lists/4.html
                ['value'=>'kknews','label'=>'36K快讯'],      //https://36kr.com/newsflashes              
                ];  
        return $data;            

	}






}