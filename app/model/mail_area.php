<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-03 15:48:35
 * Desc: 物流类模型
 */
namespace app\model;


class mail_area extends BaseModel
{
    public $title = 'mail_area';

 

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
     * 查所有
     * @param  
     * @return data 返回数据集
     */
    public function lists_all($cid=[],$field='*'){
        $where = [];
        if($cid>0){
            $where = ['cid'=>$cid];
        }
        $data=$this->select($this->title,"*",$where);       
        return $data;     
    }


   
    
}
