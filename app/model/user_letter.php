<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-03 13:54:30
 * Desc: 站内信模型
 */
namespace app\model;


class user_letter extends BaseModel
{
    public $title = 'user_letter';

    
    /**
     * 模型添加数据规则
     * @param data 数据
     * @return 当前操作ID
     */
    public function save($data){
        $data['created_time'] = time();
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
     * @param  NULL
     * @return data 返回数据集
     */
    public function lists_all($uid=[],$field='*'){
        $data=$this->select($this->title,"*",['uid'=>$uid,'ORDER'=>["Created_time"=>"DESC"]]);        
        return $data;     
    }

    /**
     * 模型列表数据
     * @param page 页码
     * @return data 返回数据集
     */
    public function lists($page=1,$number=10,$where_base=[]){        
        $startRecord=($page-1)*$number;        
        $where_other = ['ORDER'=>["Created_time"=>"DESC","id"=>"DESC"],"LIMIT" => [$startRecord,$number]];
        $where = array_merge($where_base,$where_other);

        $data_ar=$this->select($this->title,'id', $where);
        $where_ar['id']=$data_ar;
        if(isset($where['ORDER'])){
            $where_ar['ORDER']=$where['ORDER'];
        }
        $data=$this->select($this->title,['id','uid','content','created_time','links'],$where_ar);

        return $data;
    }


}
