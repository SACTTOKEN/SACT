<?php
namespace app\validate;

class FundsValidate extends BaseValidate
{
    protected $rule = [
        'iden'       => 'require',
        'oid'       => 'require',
        'hash'       => 'require',
        'money'       => 'require|isMoney',
        'pay_id'       => 'require|isPositiveInteger',
        'pay'       => 'require|in:微信,支付宝,网银',
    ];


    protected $message = [
        'iden'     => '币种不存在',
        'money'       => '提交金额错误',
        'pay_id'       => '请选择支付方式',
        'pay'       => '请选择支付方式',
        'hash'       => '请提交hash',
        'oid'       => '请提交订单号',
    ];

    protected $scene  = [
        'water'  =>  ['iden'],
        'recharge'  =>['money','pay_id'],
        'withdraw'  =>['iden','money','pay'],
        'dapp'  =>  ['money'],
        'dapp_pay'=>['oid','hash'],
    ];

}
