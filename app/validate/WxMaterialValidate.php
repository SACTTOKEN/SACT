<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-29 13:36:27
 * Desc: 自动回复验证
 */

namespace app\validate;

class WxMaterialValidate extends BaseValidate
{
    protected $rule = [
        'title'    => 'require',
        'piclink'    => 'require',
        'links'    => 'require',
        'content'    => 'require',
        'del_id' => 'require|checkcartid',
        'id_str' => 'require',
    ];


    protected $message = [
        'title'    => '标题必须', 
        'piclink'    => '图片必须', 
        'links'    => '链接必须', 
        'content'    => '内容必须', 
    ];




    protected $scene  = [
        'scene_find'        => ['id_str'],
        'scene_checkID'     =>  ['del_id'],
        'scene_saveedit'     =>  ['title','piclink','links','content'],
    ];

    

}
