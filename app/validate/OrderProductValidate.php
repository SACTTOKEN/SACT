<?php
namespace app\validate;

class OrderProductValidate extends BaseValidate
{
    protected $rule = [
        'goods_id'       => 'require',
        'status'    => 'require',
        'return_name'    => 'require',
        'return_tel'    => 'require|isMoney',
        'return_address'    => 'require',
    ];

    protected $message = [
        'goods_id'   => '商品ID必须',
        'status' => '状态必须', 
        'return_name'    => '请填写收件人姓名',
        'return_tel.require'    => '请填写收件人电话',
        'return_tel.isMoney'    => '电话格式错误',
        'return_address'    => '请填写收件人地址',
    ];



    protected $scene  = [
        'scene_edit'  =>  ['goods_id','status'],
        'reback_goods'  =>  ['return_name','return_tel','return_address'],
    ];

}
