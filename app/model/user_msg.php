<?php
/**
 * Created by yaaaaa_god
 * User: yaaaaa
 * Date: 2018/12/24
 * Desc: 用户留言模型
 */
namespace app\model;


class user_msg extends BaseModel
{
    public $title = 'user_msg';

	

    
    /**
     * 模型分类数据
     * @param  uid  分类
     * @return data 返回数据集
     */
    public function lists_all($uid=[],$field='*'){      
        $data=$this->select($this->title,"*",["uid"=>$uid,'ORDER'=>["sort"=>"DESC","id"=>"DESC"]]);        
        return $data;     
    }

    

    /**
     * 审核留言
     * @param  is_check 1：通过  0：拒绝
     * @return boolean
     */
    public function check_msg($id,$is_check=1){
        $this->update($this->title,['check'=>$is_check],['id'=>$id]);
        return $this->doo();  
    }

	
}
