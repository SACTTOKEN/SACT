<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-07-02 10:01:01
 * Desc: 自定义excel导入表
 */
namespace app\model;


class excel_auto extends BaseModel
{
    public $title = 'excel_auto';


    public function up($id,$data){
        $this->update($this->title,$data,['id'=>$id]);
        return $this->doo();
    }



    /*获取字段名*/
    public function get_field(){
    	$sql = "SHOW COLUMNS FROM ".$this->title;
		$ar = self::$medoo->query($sql)->fetchAll();
		$field_list = [];
    	foreach($ar as $col){
    		$field_list[] = $col['Field'];
    	}
		return $field_list;
    }

    public function lists($page=1,$number=10,$where_base=[],$field_ar='*'){       
        $startRecord=($page-1)*$number;        
        $where_other = ["LIMIT" => [$startRecord,$number]];
        $where = array_merge($where_base,$where_other);
        $data_ar=$this->select($this->title,'id', $where);
        $where_ar['id']=$data_ar;
        if(isset($where['ORDER'])){
            $where_ar['ORDER']=$where['ORDER'];
        }
        
        $data=$this->select($this->title,$field_ar,$where_ar);
        return $data;
    }

    public function max_stage(){
        $res = date('YmdHis'); //20190708
        return $res;
    }

    public function stage_all(){
        $sql = "select stage from excel_auto group by stage order by id desc ";
        $rs = self::$medoo->query($sql)->fetchAll();
        $ar = [];
        foreach($rs as $one){
            if($one['stage']){
                $ar[] = $one['stage'];
            }
           
        }    
        sort($ar);
        return $ar;
    }

    public function del_excel_auto2(){
        $sql = "truncate table excel_auto2";
        $rs = self::$medoo->query($sql);
        return $rs;
    }

    public function del_excel_auto3(){
        $sql = "truncate table excel_auto3";
        $rs = self::$medoo->query($sql);
        return $rs;
    }







    
}
