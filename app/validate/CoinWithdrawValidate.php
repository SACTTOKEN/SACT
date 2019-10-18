<?php
namespace app\validate;

class CoinWithdrawValidate extends BaseValidate
{
    protected $rule = [
        'money'          => 'require|isMoney',
        'uid'            => 'require',       
    ];

    protected $message = [
        'money.require'    => '请填写提币金额',
        'uid.require'      => '请选择用户', 
        'money.isMoney'    => '金额必须是正整数',  
    ];

    protected $scene  = [
        'scene_saveadd'      =>  ['uid','money'],
        'scene_saveedit'     =>  ['uid','money'],
    ];
}
