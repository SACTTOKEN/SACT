<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-04-02 15:16:55
 * Desc: 优惠券（红包）模型
 */
namespace app\model;


class coupon extends BaseModel
{
    public $title = 'coupon';

  
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
        $oid = date('Ymdhis').$id;
        $this->update($this->title,['coupon_num'=>$oid],['id'=>$id]); 
        $do = $this->find($id);
        if($do){return $do;}else{return 0;} 
    }

}
