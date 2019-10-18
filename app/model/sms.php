<?php
/**
 * Created by yaaaaa_god
 * User: yaaaaa
 * Date: 2018/12/25
 * Desc: 短信模型
 */
namespace app\model;


class sms extends BaseModel
{
    public $title = 'sms';

   
    /**
     * 模型添加数据规则
     * @param data 数据
     * @return 当前操作ID
     */
    public function save($data){
        $data['Created_time'] = time();
        $this->insert($this->title,$data);            
        return $this->doo();  
    }



    
    
}
