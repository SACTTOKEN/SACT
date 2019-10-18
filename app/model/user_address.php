<?php
/**
 * Created by yaaaaa_god
 * User: yaaaaa
 * Date: 2018/12/24
 * Desc: 用户收货地址模型
 */
namespace app\model;

class user_address extends BaseModel
{
    public $title = 'user_address';

    
    /*是否有设置*/
    public function is_have_address($uid){      
        $data=$this->has($this->title,"*",["uid"=>$uid]);        
        return $data;     
    }

}
