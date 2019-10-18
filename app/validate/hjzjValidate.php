<?php
namespace app\validate;

class hjzjValidate extends BaseValidate
{
    protected $rule = [
        'money'      => 'require|isMoney',
    ];


    protected $message = [
        'money.require'      =>  '请填写攻打数量',
        'money.isMoney'      =>  '攻打数量必须是正整数',
    ];

    protected $scene  = [
        'saveadd'  =>  ['money'],
    ];

}
