<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-14 17:19:54
 * Desc: 购物车模型
 */

namespace app\model;

class cart extends BaseModel
{
	public $title = 'cart';

  
    /**
     * 根据uid查购物车商品
     * @param $uid 用户ID
     * @return BOOL
     */
    public function lists_all($uid=[],$field='*'){
       return $this->select($this->title,$field,["uid"=>$uid,'ORDER'=>["Created_time"=>"DESC"]]); 
    }    

  
    /**
     * 根据uid查购物车商品
     * @param $uid 用户ID
     * @return BOOL
     */
    public function lists_have($where,$field='*'){
        $where['ORDER']=['id'=>'DESC'];
       return $this->select($this->title,$field,$where); 
    }    

    /**
     * 下单购物车详情
     * @param $uid 用户ID
     * @return BOOL
     */
    public function lists_order($uid,$id){
        return  $this->select($this->title,'*',['AND'=>["uid"=>$uid,'id'=>$id,'number[>]'=>0]]); 
    }    



//=====================以上为通用基础模型====================  

    /**
     *  聚合函数sum 购物车数量
     *  sum($table, $column, $where)
     */
    public function cart_sum($uid){
        $num = $this->sum($this->title,'number',['uid'=>$uid]);
        return $num ? $num : 0;
    }


    /**
     * 按商品ID 删除数据规则
     * @param $pid 商品ID
     * @return BOOL
     */
    public function del_pid($pid){
        $this->delete($this->title,['pid'=>$pid]);
        return $this->doo();  
    }

  

	
}

