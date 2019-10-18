<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-04-11 13:40:31
 * Desc: 拼团
 */
namespace app\service;

class group{
    public $groups_M;
    public function __construct()
    {
        $this->groups_M=new \app\model\groups();
    }
        
    //商品详情拼团列表
    public function info($data)
    {
        $this->groups_M=new \app\model\groups();
        $where['AND']=['group_people[>]now_people'];
        $where['is_pay']=1;
        $where['pid']=$data['id'];
        $where['status']=0;
        $where['end_time[>]']=time();
        $data_ar['group']['number']=$this->groups_M->new_count($where);
        $where['ORDER']=['created_time'=>'DESC'];
        $where['LIMIT']=[0,10];
        $data_ar['group']['data']=$this->groups_M->lists_all($where);
        foreach($data_ar['group']['data'] as &$vo){
            $users=user_info($vo['uid']);
            $vo['nickname']=$users['nickname']?$users['nickname']:$users['username'];
            $vo['avatar']=$users['avatar'];
            $vo['end_time_second']=$vo['end_time']-time();
            $vo['difference']=$vo['group_people']-$vo['now_people'];
        }
        
        $data_ar['group']['group_price']='';
        $price=explode("-", $data['price']);
        if (is_array($price)) {
            foreach ($price as $vos) {
                if ($vos) {
                    $data_ar['group']['group_price'] .= $vos*$data['group_discount']/10 . '-';
                }
            }
        }
        $data_ar['group']['group_price'] = rtrim($data_ar['group']['group_price'], "-");
        return $data_ar;
    }

    //拼团判断是否成功
    public function judge($id)
    {
        $group_res=(new \app\model\groups())->have(['id'=>$id,'status'=>0]);
        if(empty($group_res)){
            return ['status'=>0,'msg'=>'拼团已满'];
        }
        if($group_res['now_people']>=$group_res['group_people']){
            return ['status'=>0,'msg'=>'拼团已满'];
        }
        if($group_res['end_time']<=time()){
            return ['status'=>0,'msg'=>'拼团已结束'];
        }
        if($group_res['is_pay']==0){
            return ['status'=>0,'msg'=>'拼团未开始'];
        }
        return ['status'=>1,'msg'=>'拼团成功'];
    }


    public function payment_successful($oid)
    {
        $groups_M=new \app\model\groups();
        $group_res=$groups_M->have(['oid'=>$oid]);
        $groups_M->up($group_res['id'],['is_pay'=>1]);
        $number=$groups_M->new_count(['head_oid'=>$group_res['head_oid'],'is_pay'=>1]);
        $groups_M->up_all(['head_oid'=>$group_res['head_oid']],['now_people'=>$number]);
        //拼团成功
        if($group_res['group_people']>=$number){
            $groups_M->up_all(['head_oid'=>$group_res['head_oid'],'is_pay'=>1,'status'=>0],['status'=>1]);
            $groups_M->up($group_res['id'],['status'=>1]);
            //关闭未支付的订单
            $order_id_ar=$groups_M->lists_all(['head_oid'=>$group_res['head_oid'],'is_pay'=>0],'oid');
            (new \app\model\order())->up_all(['id'=>$order_id_ar,'is_pay'=>0],['status'=>'已关闭']);
        }
        return true;
    }


}