<?php
/**
 * Created by yaaaaa_god
 * User: yaaaaa
 * Date: 2018/12/13
 * Desc: 模型公共类
 */

namespace app\model;
use \core\lib\Model;


class BaseModel extends model
{
	/**
     * 模型查找id数据是否存在
     * @param id 数字
     * @return bool 布尔值
     */
    public function is_find($id){
		$data=$this->has($this->title,["AND"=>['id'=>$id]]);
        return $data;      
    }

    /*是否存在满足条件的数据*/
    public function is_have($where){
        $res = $this->has($this->title,$where);
        return $res;
    }
    
    /**
     * 模型查找id数据
     * @param id 数字 field 字段名
     * @return data 返回某个字段的值
     */
    public function find($id,$field='*'){
        $data=$this->get($this->title, $field, ["AND"=>['id'=>$id]]);
        return $data;      
    }


    /**
     * 模型查找id数据
     * @param where 数字 条件 字段名
     * @return data 返回某个字段的值
     */
    public function have($where,$field='*'){
        $data=$this->get($this->title, $field, $where);
        return $data;      
    }


     /**
     * 模型添加数据规则
     * @param data 数据
     * @return 当前操作ID
     */
    public function save($data){
        $data['created_time'] = time();
        $data['update_time'] = time();
        $this->insert($this->title,$data);            
        return $this->id();  
    }

    /*生成带单号的记录,写入的表内必须有oid字段*/
    public function save_by_oid($data){
        $data['created_time'] = time();
        $data['update_time'] = time();
        $this->insert($this->title,$data);   
        $id= $this->id();  
        $oid = date('Ymdhis').rand(10000,99999).$id;
        $this->update($this->title,['oid'=>$oid],['id'=>$id]); 
        $do = $this->find($id);
        if($do){return $do;}else{return 0;}  
    }


    /**
     * 模型删除数据规则
     * @param data 数据
     * @return BOOL
     */
    public function del($id){
        $this->delete($this->title,['id'=>$id]);
        return $this->doo();
    }

    /**
     * 模型删除数据规则
     * @param data 数据
     * @return BOOL
     */
    public function del_all($where){
        $this->delete($this->title,$where);
        return $this->doo();
    }



    /**
     * 模型修改数据规则
     * @param data 数据
     * @return BOOL
     */
    public function up($id,$data){
        $data['update_time'] = time();
        $this->update($this->title,$data,['id'=>$id]);
        return $this->doo(); 
    }

    /**
     * 模型修改数据规则
     * @param data 数据
     * @return BOOL
     */
    public function up_all($where,$data){
        $data['update_time'] = time();
        $this->update($this->title,$data,$where);
        return $this->doo(); 
    }

    /**
     * 无分页列表数据
     * @param  无
     * @return data 返回数据集
     */
    public function lists_all($where=[],$field='*'){     
        $data=$this->select($this->title,$field,$where);   
        return $data;     
    }

    /**
     * 分页列表数据
     * @param page 页码
     * @return data 返回数据集
     */
    public function lists($page=1,$number=10,$where_base=[]){       
        $startRecord=($page-1)*$number;        
        $where_other = ['ORDER'=>["id"=>"DESC"],"LIMIT" => [$startRecord,$number]];
        if(empty($where_base)){
          $where = $where_other;
        }else{
          $where = array_merge($where_base,$where_other);
        }
        $data_ar=$this->select($this->title,'id', $where);
        $where_ar['id']=$data_ar;
        if(isset($where['ORDER'])){
            $where_ar['ORDER']=$where['ORDER'];
        }
        
        $data=$this->select($this->title,'*',$where_ar);
        return $data;
    }

    /**
     * 分页列表数据
     * @param page 页码
     * @return data 返回数据集
     */
    public function lists_sort($page=1,$number=10,$where_base=[],$order=[]){     
        $startRecord=($page-1)*$number;
        if($order){
            $where_other['ORDER'] = $order;
        }else{
            $where_other['ORDER'] = ["id"=>"DESC"];
        }
        $where_other['LIMIT'] = [$startRecord,$number];
        if(empty($where_base)){
            $where = $where_other;
        }else{
            $where = array_merge($where_base,$where_other);
        }
        $data_ar=$this->select($this->title,'id', $where);
        $where_ar['id']=$data_ar;
        if(isset($where['ORDER'])){
            $where_ar['ORDER']=$where['ORDER'];
        }
        $data=$this->select($this->title,'*',$where_ar);
        return $data;
    }

    /**
     * 获取某一类条数，不传值返回总条数
     * @param cate_id 类别id
     * @return int 条数
     */
    public function new_count($where_base=[]){    
        $data=$this->count($this->title,'id',$where_base);
        $data = $data ? $data : 0;
        return $data;
    }

    /*求和*/
    public function find_sum($field,$where=[]){
        $data=$this->sum($this->title,$field,$where);
        $data = $data ? $data : 0;
        return $data;
    }


}