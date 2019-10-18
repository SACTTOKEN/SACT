<?php
namespace app\validate;

class MadeValidate extends BaseValidate
{
    protected $rule = [
        'cltrlclass' => 'require|alphaDash',
        'action' => 'require|alphaDash',
    ];

    protected $message = [
        'cltrlclass'    => '类名错误',
        'action'    => '方法名错误', 
    ];
}
