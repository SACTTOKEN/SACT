<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-29 10:02:26
 * Desc: 微信模板表
 */
namespace app\model;


class wx_sms_templet extends BaseModel
{
    public $title = 'wx_sms_templet';



    /**
     * 模型添加数据规则
     * @param data 数据
     * @return 当前操作ID
     */
    public function save($data){      
        $this->insert($this->title,$data);            
        return $this->doo();  
    }


    /**
     * 模型修改数据规则
     * @param data 数据
     * @return BOOL
     */
    public function up($id,$data){
        $this->update($this->title,$data,['id'=>$id]);
        return $this->doo();  
    }


    /**
     * 排序
     */
    public function sort($ar){
        $i=1;
        $flag = true;
        foreach($ar as $one){
            $data['sort'] = $i;
            $data['update_time'] = time();
            $res = $this->update($this->title,$data,['id'=>$one]);
            $i++;
        }
        return $flag;    
    }
    
}
