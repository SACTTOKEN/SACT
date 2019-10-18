<?php
namespace app\validate;

class TransactionValidate extends BaseValidate
{
    protected $rule = [
        'price'      => 'require|isMoney',
        'number'      => 'require|isMoney',
		'buylx'      => 'require|isMoney',
    ];


    protected $message = [
        'price'     => '请输入购买价格',
        'number'    =>  '请输入购买数量',
		'buylx'    =>  '请选择购买类型',
    ];

    protected $scene  = [
        'buy'  =>  ['price','number','buylx'],
        'sell'  =>  ['price','number'],
    ];

}
