<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-29 13:36:27
 * Desc: 图片附件验证
 */

namespace app\validate;

class LessonCateValidate extends BaseValidate
{
    protected $rule = [
        'cate_name'  => 'require|unique:lesson_cate',  
    ];

    protected $message = [
        'cate_name.require'    => '类别名称必须', 
        'cate_name.unique'     => '类别名称重复',
    ];

    protected $scene  = [
        'scene_add'   =>  ['cate_name'],
        'scene_edit'  =>  ['cate_name'=>'require'],
    ];

}
