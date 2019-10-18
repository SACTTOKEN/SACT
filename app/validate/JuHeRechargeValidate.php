<?php
namespace app\validate;

class JuHeRechargeValidate extends BaseValidate
{
    protected $rule = [
        'money'    => 'require|isMoney',
        'types'    => 'require',
        'tel'      => 'require|isMobile',
        'pay_type' => 'require',
    ];

    protected $message = [
        'money.require'      =>  '金额必须',
        'money.isMoney'      =>  '金额必须是正整数',
        'types'              =>  '充值类型必须',
        'tel'                =>  '手机号格式不正确',  
        'pay_type'           =>  '支付类型',
    ];

    
	protected $scene  = [
        'scene_save'     =>  ['types','money','tel'],
        'scene_query'    =>  ['tel'],
        'scene_lists'    =>  ['pay_type'],
        
    ];

}
