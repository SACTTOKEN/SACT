<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-07-18 15:51:31
 * Desc: 
 */
namespace app\model;
use core\lib\redis;

class drag_day extends BaseModel
{
    public $title = 'drag_day';

    public function lists($page=1,$number=10,$where_base=[]){       
        $startRecord=($page-1)*$number;        
        $where_other = ["LIMIT" => [$startRecord,$number]];
        $where = array_merge($where_base,$where_other);
        $data_ar=$this->select($this->title,'id', $where);
        $where_ar['id']=$data_ar;
        if(isset($where['ORDER'])){
            $where_ar['ORDER']=$where['ORDER'];
        }
        
        $data=$this->select($this->title,'*',$where_ar);
        return $data;
    }
    	
}
