<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-04-01 15:10:48
 * Desc: 插件大市场验证
 */

namespace app\validate;

class PluginValidate extends BaseValidate
{
    protected $rule = [
        'id'       => 'require',
        'title'    => 'require|unique:plugin',
  
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
