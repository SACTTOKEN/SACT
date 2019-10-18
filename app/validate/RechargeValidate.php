<?php
namespace app\validate;

class RechargeValidate extends BaseValidate
{
    protected $rule = [
        'id'    => 'require',
        'oid'   => 'require',
        'uid'   => 'require',
        'money' => 'require|isMoney',
        'types' => 'require',
    ];



    protected $message = [
        'oid'    => '定单号必须',  
        'uid'    => '用户ID必须',   
        'oid'    => '标题必须',   
        'types'    => '请选择充值类型',   
        'money.require'    => '金额不能为空',   
        'money.isMoney'    => '金额必须是正整数',        
    ];

    protected $scene  = [
        'scene_find'  =>  ['id'],
        'scene_del'   =>  ['id'],
        'scene_add'   =>  ['uid','money','types','cate','pay'],
        'scene_edit'  =>  ['id'],
    ];

}
