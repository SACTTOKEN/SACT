<?php
/**
 * Created by yaaaaa__god 
 * User: yaaaaa
 * Date: 2018-12-14 11:19:11
 * Desc: 资金加减
 */
namespace app\service;
use app\model\money as MoneyModel;
use app\model\user as UserModel;
use app\model\user_attach as user_attach_Model;

class money{
    /*
    加金额
    $uid 用户id
    $money 金额
    $cate money余额 amount佣金 integral积分
    $iden 奖励类型  英文标识  中文 $style
    $oid 订单
    $ly_id 来源用户
    $remark 备注
    $sy 加收益的字段
    */
	function plus($uid,$money,$cate,$iden,$oid='无',$ly_id='',$remark='',$sy=''){
        empty($uid) && error('用户丢失',10005);
        $reward_M = new \app\model\reward();
        empty($iden) && error('奖励类型标识丢失',10005);
        $style = $reward_M->find_redis($iden);
        empty($style) && error('奖励类型标识丢失!',10005);

        $moneyM = new MoneyModel();
        $userM = new UserModel();
        $res=$userM->up($uid,[$cate.'[+]'=>$money]);
        empty($res) && error('金额错误',10005);
        $balance=$userM->find_money($uid,$cate);
        if($sy!=''){
            $yj[$sy.'[+]']=$money;
            $user_attach_M=new user_attach_Model();
            $user_attach_M->up($uid,$yj);
        }

        $data['uid']=$uid;
        $data['oid']=$oid;
        $data['cate']=$cate;
        $data['money']=$money;
        $data['balance']=$balance;
        $data['types']=1;
        $data['iden'] = $iden;
        $data['style']=$style;
        $data['ly_id']=$ly_id;
        $data['remark']=$remark;
        $res=$moneyM->save($data);
        empty($res) && error('保存流水失败',10005);
        return true;
	}


    /*
    扣金额
    $uid 用户id
    $money 金额
    $cate money余额 amount佣金 integral积分
    $style 奖励类型
    $oid 订单
    $ly_id 来源用户
    $remark 备注
    */
    function minus($uid,$money,$cate,$iden,$oid='无',$ly_id='',$remark=''){

        empty($uid) && error('用户丢失',10005);
        
        $reward_M = new \app\model\reward();
        empty($iden) && error('奖励类型标识丢失',10005);
        $style = $reward_M->find_redis($iden);
        empty($style) && error('奖励类型标识丢失!',10005);

        $moneyM = new MoneyModel();
        $userM = new UserModel();
        $balance=$userM->find_money($uid,$cate);
        $balance=$balance-$money;
        if($balance<0){
            error('金额不足',10003);
        }
        $res=$userM->up($uid,[$cate.'[-]'=>$money]);
        empty($res) && error('金额不足',10003);
        $balance=$userM->find_money($uid,$cate);
        
        
        $data['uid']=$uid;
        $data['oid']=$oid;
        $data['cate']=$cate;
        $data['money']=$money;
        $data['balance']=$balance;
        $data['types']=2;
        $data['iden'] = $iden;
        $data['style']=$style;
        $data['ly_id']=$ly_id;
        $data['remark']=$remark;
        $res=$moneyM->save($data);
        empty($res) && error('保存流水失败',10005);
        return true;
    }

}