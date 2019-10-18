<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-04-10 11:08:23
 * Desc: 猜拳模型
 */
namespace app\model;

class plugin_finger_lord extends BaseModel
{
    public $title = 'plugin_finger_lord';


    public function find_by_oid($oid,$field='*'){
        $data=$this->get($this->title, $field, ["AND"=>['oid'=>$oid]]);
        return $data;      
    }

}
