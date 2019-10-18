<?php
namespace app\validate;

class CoinFundsValidate extends BaseValidate
{
    protected $rule = [
        'iden'       => 'require',
        'types'      => 'require',
        'add'      => 'require',
        'money'      => 'require|isMoney',
    ];


    protected $message = [
        'iden'     => '币种不存在',
        'types'    =>  '请选择类型',
        'add'      =>  '请填写提币地址',
        'money.require'      =>  '请填写提币数量',
        'money.isMoney'      =>  '提币数量必须是正整数',
    ];

    protected $scene  = [
        'water'  =>  ['iden'],
        'withdraw'  =>  ['iden','types','money'],
        'exchange'  =>  ['iden','types','money'],
    ];

}
