<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-24 09:34:59
 * Desc: 用户验证
 */


namespace app\validate;

class SmsValidate extends BaseValidate
{
    protected $rule = [
        'uid'    => 'require',
        'tel'    => 'require',
    ];

    protected $message = [
        'uid'    => '用户ID必须',  
        'tel'    => '手机号不能为空',      
    ];

    protected $scene  = [
        'scene_find'  =>  ['id'],
        'scene_del'   =>  ['id'],
        'scene_add'   =>  ['tel','uid'],
        'scene_edit'  =>  ['id'],
    ];

}
