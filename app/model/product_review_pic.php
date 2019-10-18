<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-03-12 15:31:52
 * Desc: 评论图片模型
 */
namespace app\model;

class product_review_pic extends BaseModel
{
    public $title = 'product_review_pic';
    /**
     * 模型查找id数据
     * @param id 数字
     * @return data 返回某条评论的相关图片
     */
    public function find($id,$field='*'){
        $data=$this->select($this->title,'*',["AND"=>['rid'=>$id]]);
        return $data;      
    }

    /**
     * 模型修改数据规则
     * @param data 图片数据 ar
     * @return BOOL
     */
    public function save($data){
        $this->insert($this->title,$data);            
        return $this->id();  
    }


    
}
