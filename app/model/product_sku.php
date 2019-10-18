<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-12 14:37:05
 * Desc: 商品属性表
 */
namespace app\model;


class product_sku extends BaseModel
{
    public $title = 'product_sku';

     
  
    /**
     * 模型查找id数据
     * @param id 数字 field 查找字段
     * @return data 返回该字段的值
     */
    public function findme($id,$field){
        $data = $this->get($this->title,$field,["AND"=>['id'=>$id]]);
        return $data;      
    }

    /**
     * 模型添加数据规则
     * @param data 数据
     * @return 当前操作ID
     */
    public function save($data){
        $this->insert($this->title,$data);        
        return $this->doo();  
    }

    public function save_back_id($data){
        $this->insert($this->title,$data);        
        return $this->id();  
    } 

    /**
     * 模型修改数据规则
     * @param data 数据
     * @return BOOL
     */
    public function up($id,$data){
        $this->update($this->title,$data,['id'=>$id]);
        return $this->doo();        
    }


    /*按商品ID查集合*/
    public function list_cate($pid){      
        $data=$this->select($this->title,['cost_price','hid','id','iden','pid','price','stock'],["pid"=>$pid]);        
        return $data;     
    }

//=====================以上为通用基础模型====================

    /**
     * 根据选择的sku拼成iden 来查对应的价格等
     * @param  $iden  1:10@2:14 SKU串   $pid 商品ID
     * @return BOOL
     */
    public function find_by_iden($pid,$iden){
        $data = $this->get($this->title,'*',['iden'=>$iden,'pid'=>$pid]);        
        return $data;  
    }

    /**
     * 模型查找iden数据
     * @param iden 数字
     * @return bool
     */
    public function find_iden($iden){
        $data=$this->has($this->title,["AND"=>['iden'=>$iden]]);
        return $data;      
    }


    /**
     * 按商品ID 删除数据规则
     * @param $pid 商品ID
     * @return BOOL
     */
    public function del_pid($pid){
        $this->delete($this->title,['pid'=>$pid]);
        return $this->doo();  
    }

   
    
}
