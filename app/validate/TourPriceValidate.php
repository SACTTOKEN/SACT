<?php
namespace app\validate;

class TourPriceValidate extends BaseValidate
{
    protected $rule = [
        'pid'    => 'require', 
        'price'     => 'require|isMoney', 
        'day'     => 'require',
    ];

    protected $message   = [
       'pid'  => '商品ID必须',
       'day'  => '日期必须',
       'price.require' => '价格必须', 
       'price.isMoney' => '价格必须是正整数',
    ];

    protected $scene  = [
        'scene_saveadd'     =>  ['pid','day','price'],
    ];

}
