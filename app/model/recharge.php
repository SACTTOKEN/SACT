<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-04 14:17:24
 * Desc: 充值模型
 */
namespace app\model;


class recharge extends BaseModel
{
    public $title = 'recharge';

   
    /**
     * 模型分类数据
     * @param  cate 分类字符
     * @return data 返回数据集
     */
    public function list_cate($category_id){      
        $data=$this->select($this->title,"*",["category_id"=>$category_id,'ORDER'=>["sort"=>"DESC"]]);        
        return $data;     
    }

     /*生成带单号的记录,写入的表内必须有oid字段*/
     public function save_by_oid($data){
        $data['created_time'] = time();
        $data['update_time'] = time();
        $this->insert($this->title,$data);   
        $id= $this->id();  
        $oid = 'R'.date('Ymdhis').rand(1000,9999).$id;
        $this->update($this->title,['oid'=>$oid],['id'=>$id]); 
        $do = $this->find($id);
        if($do){return $do;}else{return 0;}  
    }

}
