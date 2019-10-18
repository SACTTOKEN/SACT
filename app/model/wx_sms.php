<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-29 10:02:26
 * Desc: 模板消息表
 */
namespace app\model;


class wx_sms extends BaseModel
{
    public $title = 'wx_sms';

    

    /**
     * 模型列表数据
     * @return data 返回数据集
     */
    public function lists_tid($tid){     
        $where = ['tid'=>$tid,'show'=>1];
        $data=$this->select($this->title,['id','title','content','tid','wx_show','app_show','web_show','bottom','op'], $where);
        //{用户昵称}{用户账号} 
        foreach($data as &$one){
            if($one['op']){
                $one['op'] =  explode('@@',$one['op']);
            }
        }     
        return $data;
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

    //=====================以上为通用基础模型====================

    

    
    
}
