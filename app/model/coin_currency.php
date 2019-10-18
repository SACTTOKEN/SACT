<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-04-27 15:30:00
 * Desc: 币价管理模型
 */
namespace app\model;

class coin_currency extends BaseModel
{
    public $title = 'coin_currency';

    /**
     * 模型查找id数据
     * @param id 数字 field 字段名
     * @return data 返回某个字段的值
     */
    public function find($id,$field='*'){
        $data=$this->get($this->title, $field, ["AND"=>['iden'=>$id]]);
        return $data;      
    }
    
    
    /**
     * 模型修改数据规则
     * @param data 数据
     * @return BOOL
     */
    public function up($id,$data){
        $data['update_time'] = time();
        $this->update($this->title,$data,['iden'=>$id]);
        return $this->doo(); 
    }
}
