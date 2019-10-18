<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-29 13:36:27
 * Desc: 自动回复验证
 */

namespace app\validate;

class MailValidate extends BaseValidate
{
    protected $rule = [
        'id'       => 'require',
        'title'    => 'require',
        'sid'      => 'require',
        'first_weight' => 'number',
        'continued_weight' => 'number',
        'free_post' => 'number',
        'sender_mobile' =>'isMobile',

    ];

    protected $message = [
        'first_weight' => '首重价格只能为数字',
        'continued_weight' => '续重价格只能为数字',
        'free_post' => '包邮金额只能为数字',
        'sender_mobile' => '电话格式不正确',
    ];


    protected $scene  = [
        'scene_find'  =>  ['id'],
        'scene_del'   =>  ['id'],
        'scene_add'   =>  ['title'],
        'scene_edit'  =>  ['id'],
        'scene_sid'   =>  ['sid'],
        'scene_saveedit_by_sid' => ['first_weight','continued_weight','free_post','sender_mobile'],
    ];

}
