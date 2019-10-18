<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-14 17:25:08
 * Desc: 订单
 */
namespace app\ctrl\mobile;

use app\validate\IDMustBeRequire;
use app\model\order as OrderModel;

class order_return extends BaseController
{
	public $orderM;
    public $order_pro_M;

	public function __initialize(){
		$this->orderM = new OrderModel();
		$this->order_pro_M = new \app\model\order_product();
    }

    //退货申请
    public function application()
    {
        (new IDMustBeRequire())->goCheck();
        $uid=$GLOBALS['user']['id'];
        $id=post('id');
        $where['id']=$id;
        $where['uid']=$uid;
        $order_pro_ar=$this->order_pro_M->have($where);
        empty($order_pro_ar) && error('订单不存在',10007);
        $where_ar['uid']=$uid;
        $where_ar['oid']=$order_pro_ar['oid'];
        $order_ar=$this->orderM->have($where_ar);
        empty($order_ar) && error('订单不存在',10007);
        if($order_ar['types']==2){
            error('积分兑换订单无法退货',10007);
        }
        if($order_ar['types']==4){
            $groups=(new \app\model\groups())->have(['oid'=>$id]);
            if($groups['status']==0){
            error('拼团中无法退货',10007);
            }
        }
        if($order_ar['status']!='已发货' && $order_ar['status']!='已支付' && $order_ar['status']!='配货中'){
            empty($order_pro_ar) && error('订单'.$order_ar['status'].'无法退单',10007);
        }
        if(($order_ar['types']==1 && c('fksfjfl')==1) || $order_ar['is_settle']>0){
            error('订单已结算无法退单',10007);
        }
        $order_pro_ar['pic']=(new \app\model\image())->list_cate('order_return',$order_pro_ar['id']);
        $order_pro_ar['reason']=['退运费','商品成分描述不符','生产日期/保质期与商品描述不符','图片/产地/批号/规格等描述不符','质量问题'];
        $order_pro_ar['order_ar']=$order_ar;
        return $order_pro_ar;
    }

    //提交申请
    public function save()
    {
        $order_pro_ar=$this->application();
        (new \app\validate\ReturnGoodValidate())->gocheck('return_reason');
        $parameter=post(['return_reason','return_instructions','pic']);
        if($order_pro_ar['status']!=0){
            error('退货申请中',404);
        }

        $id_ar = explode('@',$parameter['pic']);
        if(isset($id_ar)){
            if(count($id_ar)>3){
                error('上次凭证最多3张',404);
            }
            $image_M=new \app\model\image();
            $image_ar['aid']=$order_pro_ar['id'];
            $image_ar['cate']='order_return';
            foreach($id_ar as $vo){
                if($vo){
                    $image_ar['piclink']=$vo;
                    $res=$image_M->save($image_ar);
                    empty($res) && error('删除失败',400);		
                }
            }
        }
        $data['return_reason']=$parameter['return_reason'];
        $data['return_instructions']=$parameter['return_instructions'];
        if($order_pro_ar['order_ar']['status']=='已支付'){
            $data['status']=3;
        }else{
            $data['status']=1;
        }
        $data['return_time']=time();
        $rs=$this->order_pro_M->up($order_pro_ar['id'],$data);
        empty($rs) && error('申请失败',10006);
        $rs=$this->orderM->up($order_pro_ar['order_ar']['id'],['is_return'=>1]);
        empty($rs) && error('申请失败',10006);
        return '申请成功';
    }

    
    //提交快递单
    public function mail()
    {
        $order_pro_ar=$this->application();
        (new \app\validate\ReturnGoodValidate())->gocheck('return_mail');
        $parameter=post(['return_mail','return_oid']);
        if($order_pro_ar['status']!=2){
            error('退货申请中',404);
        }
        $data['return_mail']=$parameter['return_mail'];
        $data['return_oid']=$parameter['return_oid'];
        $data['status']=3;
        $data['return_time']=time();
        $rs=$this->order_pro_M->up($order_pro_ar['id'],$data);
        empty($rs) && error('申请失败',10006);
        return '提交成功';
    }

    //取消退货
    public function cancel_return()
    {
        (new IDMustBeRequire())->goCheck();
        $uid=$GLOBALS['user']['id'];
        $id=post('id');
        $where['id']=$id;
        $where['uid']=$uid;
        $where['status']=[1,2,3];
        $order_pro_ar=$this->order_pro_M->have($where);
        empty($order_pro_ar) && error('订单不在退货状态',404);
        $where_ar['uid']=$uid;
        $where_ar['oid']=$order_pro_ar['oid'];
        $order_ar=$this->orderM->have($where_ar);
        empty($order_ar) && error('订单不存在',404);
        $data['return_reason']='';
        $data['return_instructions']='';
        $data['status']=0;
        $rs=$this->order_pro_M->up($order_pro_ar['id'],$data);
        empty($rs) && error('取消失败',10006);
        $rs=$this->orderM->up($order_ar['id'],['is_return'=>0]);
        empty($rs) && error('取消失败',10006);
        return '取消成功';
    }
}