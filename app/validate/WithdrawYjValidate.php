<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-04 14:19:55
 * Desc: 提现验证
 */

namespace app\validate;

class WithdrawYjValidate extends BaseValidate
{
    protected $rule = [
        'id'    => 'require',
        'oid'   => 'require',
        'uid'   => 'require',
        'money' => 'require',
        'id_str' =>'require|checkcartid',
        'status' =>'require|number',

    ];


    protected $message = [
        'oid'    => '订单号必须', 
        'uid'    => '用户ID必须', 
    ];

    protected $scene  = [
        'scene_find'  =>  ['id','status'],
        'scene_del'   =>  ['id'],
        'scene_add'   =>  ['oid','uid','money'],
        'scene_edit'  =>  ['id'],
        'scene_allow' =>  ['id_str'],
        'scene_reject' => ['id_str'],
    ];

   
}
