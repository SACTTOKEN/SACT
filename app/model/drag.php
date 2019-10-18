<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-07-18 15:51:31
 * Desc: 
 */
namespace app\model;
use core\lib\redis;

class drag extends BaseModel
{
    public $title = 'drag';

    public function lists_drag($page=1,$number=10,$where_base=[]){        
        $startRecord=($page-1)*$number;        
        $where_other = ["LIMIT" => [$startRecord,$number]];
        $where = array_merge($where_base,$where_other);
        $data=$this->select($this->title,'*',$where);        
        return $data;
    }







    	
}
