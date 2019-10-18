<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-07-02 10:01:01
 * Desc: 自定义excel导入表 商户号
 */
namespace app\model;


class excel_auto3 extends BaseModel
{
    public $title = 'excel_auto3';

  
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
