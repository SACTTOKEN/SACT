<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-12 14:37:05
 * Desc: 商品属性表
 */
namespace app\model;


class product_attr extends BaseModel
{
    public $title = 'product_attr';

  
    /**
     * 模型父id,sku_id
     * @param id 数字
     * @return data 返回一条数据
     */
    public function findme($pid,$parent_id,$sku_id){
        $data = $this->get($this->title,'*',["AND"=>['parent_id'=>$parent_id,'sku_id'=>$sku_id,'pid'=>$pid]]);
        return $data;   
    }


    /**
     * 根据商品ID查属性
     */
    public function show_attr($pid){
        $data = $this->select($this->title,['parent_title','parent_id','sku_id','sku_title','piclink'],['pid'=>$pid,'ORDER'=>["id"=>"ASC"]]);
        $result = [];
        foreach($data as $info){         
            $result[$info['parent_id']]['title'] = $info['parent_title'];
            unset($info['parent_title']);
            $result[$info['parent_id']]['info'][] = $info;
        } 
        $z_result=[];
      	foreach($result as $vo){
        	$z_result[]=$vo;
        }
        return $z_result;
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
