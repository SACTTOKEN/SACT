<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-04-27 15:30:00
 * Desc: 币价管理模型
 */
namespace app\model;

class jddj_rating extends BaseModel
{
    public $title = 'jddj_rating';
    
    /** 
     *判断等级 
    */
    public function judge($where=[]){          
        $where['ORDER']=["id"=>"ASC"];
        $data=$this->get($this->title,"*", $where);        
        return $data;
    }
}
