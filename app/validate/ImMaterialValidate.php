<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-29 13:36:27
 * Desc: 自动回复验证
 */

namespace app\validate;

class ImMaterialValidate extends BaseValidate
{
    protected $rule = [
        'types'    => 'require|isPositiveInteger',
        'content'    => 'require',
        'del_id' => 'require|checkcartid',
        'sort' => 'require|number',
    ];


    protected $message = [
        'types'    => '素材类型必须', 
        'content'    => '内容必须', 
        'sort'    => '输入排序值', 
    ];




    protected $scene  = [
        'scene_checkID'     =>  ['del_id'],
        'scene_saveedit'     =>  ['types','content'],
        'sort'     =>  ['sort'],
    ];

    

}
