<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-04 17:23:16
 * Desc: 图片附件模型
 */
namespace app\model;


class image extends BaseModel
{
    public $title = 'image';

    /**
     * 模型添加数据规则
     * @param data 数据
     * @return 当前操作ID
     */
    public function save($data){
        $this->insert($this->title,$data);            
        return $this->doo();  
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


    /**
     * 模型分类数据
     * @param  cate 分类字符
     * @return data 返回数据集
     */
    public function list_cate($cate,$aid){      
        $data=$this->select($this->title,"*",["aid"=>$aid,"cate"=>$cate]);        
        return $data;     
    }

}
