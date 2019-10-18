<?php
/**
 * Created by yaaaaa_god
 * User: yaaaaa
 * Date: 2018/12/13
 * Desc: DEMO模型
 */
namespace app\model;


class order_product extends BaseModel
{
    public $title = 'iorder_product';

    
    /**
     * 查找同一订单号的数据
     * @param oid 订单号
     * @return data 返回数据集
     */
    public function find_by_oid($oid){
        $data = $this->select($this->title,'*',['oid'=>$oid]);
        return $data;
    }

    /*模糊查找订单oid*/
    public function find_mf_oid($name){
        $pid = $this->select($this->title,'oid',["AND"=>['title[~]'=>$name]]);
        return $pid;
    }



    /**
     * 退货列表
     * $status 0:正常的,1：申请退货 2：退货成功 3是非正常的
     */
    public function return_goods($page=1,$number=10,$where_base=[]){
        $startRecord=($page-1)*$number;   

        $where_other = ['ORDER'=>["status"=>"ASC","created_time"=>"DESC","id"=>"DESC"],"LIMIT" => [$startRecord,$number]];
        $where = array_merge($where_base,$where_other);
        $data_ar=$this->select($this->title,'id', $where);
        $where_ar['id']=$data_ar;
        if(isset($where['ORDER'])){
            $where_ar['ORDER']=$where['ORDER'];
        }
        $data=$this->select($this->title,'*',$where_ar);
        foreach($data as &$rs){
            $users=user_info($rs['uid']);
            $rs['username'] = $users['username'];
            $rs['avatar'] = $users['avatar'];
            $rs['rating_cn'] = $users['rating_cn'];
        }   
        return $data;
    }

    
}
