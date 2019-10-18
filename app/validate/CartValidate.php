<?php
namespace app\validate;

class CartValidate extends BaseValidate
{
    protected $rule = [
        'pid'     => 'require',
        'number'  => 'require|isPositiveInteger',
        'id_str' => 'require|checkcartid',    
    ];

    protected $message = [
        'pid'    =>   '请提交商品ID', 
        'number'  =>  '请提交商品数量',
        'id_str'    => 'ID错误',
    ];

    protected $scene  = [
        'scene_add_cart'  =>  ['pid','number'],
        'scene_checkID' =>  ['id_str'],
        'scene_add_cart' =>  ['number'],
    ];

}
