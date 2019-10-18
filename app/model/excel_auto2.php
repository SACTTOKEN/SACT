<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-07-02 10:01:01
 * Desc: 自定义excel导入表 汇总
 */
namespace app\model;


class excel_auto2 extends BaseModel
{
    public $title = 'excel_auto2';

    public function up($id,$data){
        $this->update($this->title,$data,['id'=>$id]);
        return $this->doo();
    }

    public function lists($page=1,$number=10,$where_base=[],$field_ar='*'){       
        $startRecord=($page-1)*$number;        
        $where_other = ['ORDER'=>["id"=>"DESC"],"LIMIT" => [$startRecord,$number]];
        $where = array_merge($where_base,$where_other);
        $data_ar=$this->select($this->title,'id', $where);
        $where_ar['id']=$data_ar;
        if(isset($where['ORDER'])){
            $where_ar['ORDER']=$where['ORDER'];
        }
        
        $data=$this->select($this->title,$field_ar,$where_ar);
        return $data;
    }






    
}
