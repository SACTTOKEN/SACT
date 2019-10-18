<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-29 13:36:27
 * Desc: 图片附件验证
 */

namespace app\validate;

class LessonValidate extends BaseValidate
{
    protected $rule = [
        'cid'    => 'require',
        'title'  => 'require',  
    ];

    protected $message = [
        'cid' => '类别ID必须',
        'title'    => '名称必须', 
    ];

    protected $scene  = [
        'scene_add'   =>  ['cid','title'],
        'scene_edit'  =>  ['cid','title'],
    ];

}
