<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-29 10:02:26
 * Desc: 微信公众号菜单表
 */
namespace app\model;


class wx_menu extends BaseModel
{
    public $title = 'wx_menu';

  

    //=====================以上为通用基础模型====================
    
    /**
     * 模型分类数据
     * @param  cate 分类字符
     * @return data 返回数据集
     */
    public function list_cate($parent_id=0){      
        $data=$this->select($this->title,"*",['parent_id'=>$parent_id]);        
        return $data;     
    }


    /**
     * 是否有下级
     * @param  parent_id 类别id
     * @return bool
     */
    public function is_have_cate($parent_id=''){
        $data=$this->get($this->title,['id'],["AND"=>['parent_id'=>$parent_id]]);
        if($data){
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
        $data = $this->select($this->title,'*',["AND"=>['parent_id'=>$pid],'ORDER'=>["sort"=>"DESC"]]);
        return $data;
    }



    /**
     * 根据control 和 action 查menu 的id
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
