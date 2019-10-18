<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-20 12:00:28
 * Desc: 商品类别模型
 */
namespace app\model;


class product_cate extends BaseModel
{
    public $title = 'product_cate';


    /**
     * 栏目根
     * @param  data 数据
     * @return 上级信息
     */
    public function up_id($menu_id=0){
        $parent_id = $this->find($menu_id,'parent_id');
        return $parent_id;
    }

    
    /**
     * 是否有小类
     * @param data 数据
     * @return BOOL
     */
    public function find_cate_son($id){
        $is_have_son = $this->get($this->title,['id'],['parent_id'=>$id]);
        if($is_have_son){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    /**
     * 模型分类数据
     * @param  cate 分类字符
     * @return data 返回数据集
     */
    public function lists_all($parent_id=[],$field='*'){      
        $data=$this->select($this->title,"*",["parent_id"=>$parent_id,'ORDER'=>["sort"=>"DESC"]]);        
        return $data;     
    }



    public function lists_all_old($where=[],$field='*'){     
        $data=$this->select($this->title,$field,$where);   
        return $data;     
    }



    /**
     * 模型列表数据
     * @param page 页码
     * @return data 返回数据集
     */
    public function lists($parent_id=[],$page=1,$number=10){
        $startRecord=($page-1)*$number;
        $data=$this->select($this->title,"*",["parent_id"=>$parent_id,'ORDER'=>["sort"=>"DESC"],"LIMIT" => [$startRecord,$number]]);
        return $data;      
    }


    //=====================以上为通用基础模型====================
    
    /**
     * 栏目树
     * @param data 数据
     * @return 当前操作ID
     */
    public function tree($pid=0,$field='*'){
        $data = $this->select($this->title,$field,['ORDER'=>["sort"=>"DESC"],"AND"=>['parent_id'=>$pid]]);
        return $data;
    }

    public function find_tree($parent_id=0){
        $obj = $this->tree($parent_id);
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

    /**
     * [sort 排序]
     * @param  [array]  $ar        [description]
     * @param  integer $parent_id [description]
     * @return [boolean]             [description]
     */
    public function sort($ar,$parent_id=0){
        $i=1;
        $flag = true;
        foreach($ar as $one){
            $data['sort'] = $i;
            $res = $this->up($one,$data);
            empty($res) && $flag = false;
            $i++;
        }
        return $flag;    
    }


    /*父级串*/
    public function find_father($cate_id,$ar=[]){
        $parent_id  = $this->get($this->title,'parent_id',['id'=>$cate_id]); 
        if($parent_id==0){
            return array_reverse($ar);
        }else{
            $ar[] = $parent_id;
            return $this->find_father($parent_id,$ar);
        }
    }

    

}
