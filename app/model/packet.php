<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-04-02 15:49:10
 * Desc: 红包发布模型
 */
namespace app\model;


class packet extends BaseModel
{
    public $title = 'packet';


    public function find_by_title($title,$field='*'){
        $data=$this->get($this->title, $field, ["AND"=>['title'=>$title]]);
        return $data;    
    }

   

    /*红包选项*/
    public function coupon_option(){
    $data=$this->select($this->title,['id(value)','title(label)']);   
    return $data;
    }
   
    
}
