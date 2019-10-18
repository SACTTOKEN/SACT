<?php
namespace app\validate;

class CloudValidate extends BaseValidate
{
    protected $rule = [
        'msg_tel'     => 'require|isMobile', 
        'ask_tel'     => 'require|isMobile', 
    ];

    protected $message   = [
       'msg_tel' => '电话格式不正确',
       'ask_tel' => '电话格式不正确',
    ];

    protected $scene  = [
        'scene_msg'     =>  ['msg_tel'],
        'scene_ask'     =>  ['ask_tel'],
    ];

}
