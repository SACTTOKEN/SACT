<?php
/**
 * Created by yaaaaa_god
 * User: yaaaaa
 * Date: 2018/12/13
 */
namespace app\validate;

class OrderValidate extends BaseValidate
{
    protected $rule = [
        'cartid' => 'checkcartid',
        'addressid'=>'require|isPositiveInteger',
        'mail_oid' => 'require',
        'is_integral'=>'in:0,1',
        'is_invoice'=>'in:0,1',
        'red_id'=>'isPositiveInteger',
        'address_id' => 'isPositiveInteger',
        'reserve_time' => 'require',
    ];

    protected $message = [
        'mail_oid.require'    => '亲，请填写物流单号',
        'reserve_time'    => '请选择预约时间',
    ];


    protected $scene  = [
        'order_ordinary_product'   =>  ['cartid'],
        'order_ordinary_address'   =>  ['addressid'],
        'scene_edit_send'          =>  ['mail_oid'],
        'mobile_order_save'          =>  ['is_integral','red_id','is_invoice','address_id'],
        'types_6'          =>  ['reserve_time'],
    ];

}