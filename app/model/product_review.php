<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-04 09:23:00
 * Desc: 商品评论模型
 */
namespace app\model;

class product_review extends BaseModel
{
    public $title = 'product_review';

    /**
     * 模型分类数据
     * @param  cate 分类字符
     * @return data 返回数据集
     */
    public function list_cate($pid){      
        $data=$this->select($this->title,"*",["pid"=>$pid,'ORDER'=>["sort"=>"DESC","Created_time"=>"DESC"]]);        
        return $data;     
    }

    
   
    
}
