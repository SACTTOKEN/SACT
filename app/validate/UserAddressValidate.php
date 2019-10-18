<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-24 09:34:59
 * Desc: 用户验证
 */


namespace app\validate;

class UserAddressValidate extends BaseValidate
{
    protected $rule = [
        'uid'       => 'require',
    ];

    protected $message = [
        'uid'    => '用户ID必须',          
    ];

    protected $scene  = [
        'scene_find'  =>  ['uid'],
        'scene_del'   =>  ['uid'],
        'scene_add'   =>  ['uid'=>'require|unique:user_attach'],
        'scene_edit'  =>  ['uid'],
        'scene_list'  =>  ['uid'],
    ];

}
