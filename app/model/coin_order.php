<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-04-27 15:30:00
 * Desc: 币价管理模型
 */
namespace app\model;

class coin_order extends BaseModel
{
    public $title = 'coin_order';
    
	
    /**
     * 根据用户查找数量
     * @param  id 数字
     * @return BOOL
     */
    public function user_count($uid){
        $rs=$this->count($this->title,'id',['uid'=>$uid,'status'=>1]);
        return $rs;
    }



    /**
     * 统计用户今日奖励总额
     * @param id 用户ID
     * @return bool 布尔值
     */
    public function reward_money($uid){
        $where['uid']=$uid;
        $where['status']=1;
        $where['y_time[<]']=time()-3600;
        $where['m_life[>=]']='[column]y_life';
        $where['z_money[>=]']='[column]y_money';
		$data=$this->select($this->title,"*",$where);
        return $data;      
    }

}
