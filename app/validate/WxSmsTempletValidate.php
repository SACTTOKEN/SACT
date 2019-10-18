<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-29 13:36:27
 * Desc: 自动回复验证
 */

namespace app\validate;

class WxSmsTempletValidate extends BaseValidate
{
    protected $rule = [
        'title'       => 'require|unique:wx_sms_templet',
    ];

    protected $message = [
        'title.unique'    => '标题已存在', 
        'title.require'    => '标题必须', 
    ];

    protected $scene  = [
        'scene_saveadd'      =>  ['title'],
        'scene_saveedit'     =>  ['title'=>'require']
    ];


}
