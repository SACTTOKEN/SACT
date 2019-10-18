<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-04-27 15:30:00
 * Desc: c2c购买
 */
namespace app\model;

class c2c_buy extends BaseModel
{
    public $title = 'c2c_buy';
    
    /**
     * 模型修改数据规则
     * @param  id 数字 data 数据
     * @return BOOL
     */
    public function up_oid($id,$data){
        $data['update_time'] = time();
		$this->update($this->title,$data,['oid'=>$id]);
		return $this->doo(); 
    }


    /**
     * 模型列表数据
     * @param page 页码
     * @return data 返回数据集
     */
    public function lists_all($where_base=[],$field="*"){               
        $where_other = ['ORDER'=>["id"=>"DESC"]];
        $where = array_merge($where_base,$where_other);
        $data=$this->select($this->title,$field,$where);        
        return $data;
    }



}
