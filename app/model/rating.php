<?php
/**
 * Created by yaaaaa_god
 * User: yaaaaa
 * Date: 2018/12/25
 * Desc: 等级模型
 */
namespace app\model;


class rating extends BaseModel
{
    public $title = 'rating';

    /*重定义勿删*/
    public function up_all($data,$data_plus=[]){
        $data['update_time'] = time();
        $this->update($this->title,$data);
        return $this->doo();  
    }
    

    //=====================以上为通用基础模型====================

    /**
     * 模型下拉列表数据VUE用
     * @param NULL
     * @return data 数据
     */
    public function option(){
        $data=$this->select($this->title,['id(value)','title(label)']);   
        return $data;
    }

    
    
}
