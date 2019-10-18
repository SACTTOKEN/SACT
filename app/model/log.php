<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-14 11:20:52
 * Desc: 管理员日志模型
 */
namespace app\model;

class log extends BaseModel
{
    public $title = 'admin_log';


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
}
