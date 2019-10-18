<?php
namespace app\validate;

class ReturnGoodValidate extends BaseValidate
{
    protected $rule = [
        'return_reason'            => 'require',       
        'return_mail'            => 'require',       
        'return_oid'            => 'require',       
    ];

    protected $message = [
        'return_reason'    => '请选择退货原因',     
        'return_mail'            => '请填写物流公司',       
        'return_oid'            => '请填写物流单号',    
    ];

    protected $scene  = [
        'return_reason'      =>  ['return_reason'],
        'return_mail'       =>['return_mail','return_oid'],
    ];
}
