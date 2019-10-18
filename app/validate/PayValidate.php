<?php
namespace app\validate;

class PayValidate extends BaseValidate
{
    protected $rule = [
        'id'       => 'require|isPositiveInteger',
        'title'    => 'require',
        'pay_id'    => 'require|isPositiveInteger',
    ];

    protected $message = [
        'title'    => '标题必须',
        'pay_id'    => '请选择支付方式',
    ];

    protected $scene  = [
        'scene_find'  =>  ['title'],
        'scene_del'   =>  ['id'],
        'scene_add'   =>  ['title'=>'require|unique:pay'],
        'scene_edit'  =>  ['id','title'=>'require'],
        'pay'         =>  ['pay_id'],
    ];

}
