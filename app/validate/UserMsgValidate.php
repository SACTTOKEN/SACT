<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-24 09:34:59
 * Desc: 用户验证
 */


namespace app\validate;

class UserMsgValidate extends BaseValidate
{
    protected $rule = [
    	'id'        => 'require',
        'uid'       => 'require',
        'id_str'    => 'require|checkcartid',
    ];
    
    protected $message = [
        'uid'    => '用户ID必须', 
    ];


    protected $scene  = [
        'scene_find'  =>  ['id'],
        'scene_del'   =>  ['id_str'],
        'scene_add'   =>  ['uid'=>'require'],
        'scene_edit'  =>  ['uid'],
    ];

   
}
