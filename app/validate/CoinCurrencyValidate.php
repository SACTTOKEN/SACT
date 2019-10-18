<?php
namespace app\validate;

class CoinPriceValidate extends BaseValidate
{
    protected $rule = [
        'iden' => 'require|unique:config',
        'price' => 'require',
        'price_cny' => 'require|isMoney',
        'price_usdt' => 'require|isMoney',
    ];

    protected $message = [
        'iden.require'   => '标识必须',
        'iden.unique'    => '标识已存在', 
        'price_cny.require' => '币价必须',
        'price_usdt.require' => '币价必须',
        'price_cny.isMoney' => '币价必须是正整数',
        'price_usdt.isMoney' => '币价必须是正整数',

    ];
}
