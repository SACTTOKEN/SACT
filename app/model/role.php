<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-14 10:13:40
 * Desc: 管理员角色模型
 */
namespace app\model;

class role extends BaseModel
{

   public $title = 'admin_role';
  

    /**
     * 模型下拉列表数据VUE用
     * @param NULL
     * @return data 数据
     */
    public function option(){
        $data=$this->select('admin_role',['id(value)','role_name(label)'], ['role_con[!]'=>'god']);   
        return $data;
    }
	
}
