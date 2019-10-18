<?php
/**
 * Created by yaaaaa_god
 * User: yaaaaa
 * Date: 2018/12/13
 * Desc: 支付接口模型
 */
namespace app\model;

class pay extends BaseModel
{
    public $title = 'pay';

    /**
     * 模型查找id数据
     * @param id 数字
     * @return data 返回一条数据
     */
    public function find_by_title($title){
        $data=$this->get($this->title,'*',["AND"=>['title'=>$title]]);
        return $data;      
    }

    
    
}
