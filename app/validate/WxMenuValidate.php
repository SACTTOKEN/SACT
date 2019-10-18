<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-29 13:36:27
 * Desc: 微信菜单验证
 */

namespace app\validate;

class WxMenuValidate extends BaseValidate
{
    protected $rule = [
        'id'       => 'require',
        'title'    => 'require',
        'keyword'  => 'require|unique:wx_text',
    ];


    protected $message = [
        'title'    => '标题必须',   
    ];



    protected $scene  = [
        'scene_find'  =>  ['id'],
        'scene_del'   =>  ['id'],
        'scene_add'   =>  ['title'],
        'scene_edit'  =>  ['id'],
    ];

}
