<?php
namespace app\validate;

class AdminValidate extends BaseValidate
{
    protected $rule = [
        'username' => 'require|alphaDash',
        'password' => 'require',
        //'code' => 'require|verification',
    ];

    protected $message = [
        'username'    => '请填写用户名',
        'password'    => '请填写密码', 
    ];

    protected $scene  = [
        'scene_saveadd'   =>  ['username','password'],
        'scene_login'     =>  ['username','password','code'],
    ];
}
