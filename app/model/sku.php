<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-20 14:50:48
 * Desc: 商品SKU模型
 */
namespace app\model;

class sku extends BaseModel
{
    public $title = 'sku';

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

    public function lists_all_title()
    {
        $data=$this->select($this->title,['id','title']);
        $data=array_column($data,'title','id');        
        return $data;
    }

    /**
     * 栏目树
     * @param data 数据
     * @return 当前操作ID
     */
    public function tree($pid=0){
        $data = $this->select($this->title,['id','title','parent_id','parent_title','show','is_pic','sort','stock'],['ORDER'=>["sort"=>"DESC"],"AND"=>['parent_id'=>$pid]]);
        $is_pic=$this->find($pid,'is_pic');
        foreach($data as &$vo){
            $vo['parent_is_pic']=$is_pic;
        }
        return $data;
    }	



    public function find_tree($parent_id=0){
        $obj = $this->tree($parent_id);
        if(!empty($obj)){
        $ar = [];
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
     * 返回SKU中文名
     */
    public function find_title($id){
        $data=$this->get($this->title,'title',["AND"=>['id'=>$id]]);
        return $data;      
    }


    /**
     * 模型查找id数据
     * @param id （数字，数组）
     * @return data 返回多条数据
     */
    public function find_all($id){
        $data=$this->select($this->title,'*',["AND"=>['id'=>$id]]);
        return $data;      
    }


    public function find_father($cate_id,$ar=[]){
        $parent_id  = $this->get($this->title,'parent_id',['id'=>$cate_id]); 
        if($parent_id==0){
            return array_reverse($ar);
        }else{
            $ar[] = $parent_id;
            return $this->find_father($parent_id,$ar);
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
}
