<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-29 13:36:27
 * Desc: 模板消息验证
 */

namespace app\validate;

class WxSmsValidate extends BaseValidate
{
    protected $rule = [
        'id'       => 'require',
        'title'    => 'require|unique:wx_sms',
        'tid'      => 'require|number',
        'content'  => 'require',
        'wx_show'  => 'require|number',
        'web_show' => 'require|number',
        'app_show' => 'require|number',
        'bottom'   => 'require',
    ];


    protected $message = [
        'title.unique'    => '标题已存在', 
        'title.require'   => '标题必须', 
        'content'         => '内容必须',
        'bottom.require'  => '底部必须',  
    ];

    protected $scene  = [
        'scene_add'   =>  ['title','tid'],
        'scene_list'  =>  ['tid'],
        'scene_edit'  =>  ['content','bottom'],
        'scene_saveedit_title' => ['title'=>'require'],
    ];

}
