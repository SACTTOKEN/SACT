<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-03 14:05:15
 * Desc: 站内信验证
 */

namespace app\validate;

class UserLetterValidate extends BaseValidate
{
    protected $rule = [
        'id'  => 'require',
        'uid' => 'require', 
        'id_str'   => 'require|checkcartid',   
        'username' => 'require',    
    ];

    protected $message = [
        'uid'    => '用户ID必须', 
        'username'  => '用户名必须',   
    ];

    protected $scene  = [
        'scene_find'  =>  ['id'],
        'scene_del'   =>  ['id_str'],
        'scene_add'   =>  ['username'],
        'scene_edit'  =>  ['id'],
    ];


}
