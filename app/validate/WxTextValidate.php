<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-29 13:36:27
 * Desc: 自动回复验证
 */

namespace app\validate;

class WxTextValidate extends BaseValidate
{
    protected $rule = [
        'id'       => 'require',
        'keyword'  => 'require|unique:wx_text',
        'types'    => 'require',
        'is_like'    => 'require',
        'content'    => 'require',
    ];
    protected $scene  = [
        'scene_add'   =>  ['keyword','types'],
        'scene_edit'   =>  ['types'],
    ];


}
