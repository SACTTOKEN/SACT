<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-04-26 15:54:44
 * Desc: 矿机模型
 */
namespace app\model;

class coin_machine extends BaseModel
{
    public $title = 'coin_machine';

    /**
     * 模型分类数据
     * @param  cate 分类字符
     * @return data 返回数据集
     */
    public function list_cate($category_id){      
        $data=$this->select($this->title,"*",["category_id"=>$category_id,'ORDER'=>["sort"=>"DESC"]]);        
        return $data;     
    }

   
}
