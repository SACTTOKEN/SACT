<?php
/**
 * Created by yayue_god
 * User: yayue
 * Date: 2018/12/13
 * Desc: 国家编码(国际短信)
 */
namespace app\model;


class country extends BaseModel
{
    public $title = 'country';

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
