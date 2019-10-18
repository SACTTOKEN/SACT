<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-19 10:36:38
 * Desc: 文章类别模型
 */
namespace app\model;


class news_cate extends BaseModel
{
    public $title = 'news_cate';


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
     * 栏目树
     * @param data 数据
     * @return 当前操作ID
     */
    public function tree($pid=0){
        $data = $this->select($this->title,'*',['ORDER'=>["sort"=>"DESC"],"AND"=>['parent_id'=>$pid]]);
        return $data;
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


    /**
     * 栏目根
     * @param  data 数据
     * @return 上级信息
     */
    public function up_id($menu_id=0){
        $parent_id = $this->find($menu_id,'parent_id');
        return $parent_id;
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
