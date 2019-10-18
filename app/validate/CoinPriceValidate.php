<?php
namespace app\validate;

class CoinPriceValidate extends BaseValidate
{
    protected $rule = [
        'effective_time' => 'require|number',
        'price' => 'require',
    ];

    protected $message = [
        'effective_time'   => '生效时间必须',
        'price.require'    => '金额必须', 
    ];

    protected $scene  = [
        'scene_saveadd'   =>  ['effective_time','price'],
        'scene_saveedit'  =>  ['effective_time','price'],
    ];
}
