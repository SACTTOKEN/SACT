<?php
/**
 * Created by yaaaaa_god
 * User: yaaaaa
 * Date: 2018/12/13
 * Desc: 
 */
namespace app\model;

class user_gx extends BaseModel
{
    public $title = 'user_gx';
    
	

    /**
     * 模型列表数据
     * @param page 页码
     * @return data 返回数据集
     */
    public function find_tid($where=[]){      
        $data=$this->get($this->title,'tid', $where);
        return $data;
    }

    /**
     * 模型列表数据
     * @param page 页码
     * @return data 返回数据集
     */
    public function lists_tid($where=[]){    
        $data=$this->select($this->title,'tid', $where);
        return $data;
    }

    /**
     * 模型列表数据
     * @param page 页码
     * @return data 返回数据集
     */
    public function lists_uid($where_base){      
        $where_other = ['ORDER'=>["id"=>"DESC"]];
        $where = array_merge($where_base,$where_other);
        $data=$this->select($this->title,['uid'],$where);
        return $data;
    }


    /**
     * 模型列表数据
     * @param page 页码
     * @return data 返回数据集
     */
    public function lists_plus($where_base=[]){      
        $where_other = ['ORDER'=>["id"=>"ASC"]];
        $where = array_merge($where_base,$where_other);
        $data=$this->select($this->title,['tid','level'], $where);
        return $data;
    }



    /**
     * 模型添加数据规则
     * @param data 数据
     * @return 当前操作ID
     */
    public function save($data){
        $data['created_time'] = time();
        $data['upgrade_time'] = time();
        $this->insert($this->title,$data);            
        return $this->id();  
    }


    /**
     * 模型修改数据规则
     * @param  id 数字 data 数据
     * @return BOOL
     */
    public function up($id,$data){
        $data['upgrade_time'] = time();
		$this->update($this->title,$data,['uid'=>$id]);
		return $this->doo(); 
    }


    /**
     * 模型修改数据规则
     * @param  id 数字 data 数据
     * @return BOOL
     */
    public function up_all($where,$data){
        $data['upgrade_time'] = time();
		$this->update($this->title,$data,$where);
		return $this->doo(); 
    }


}
