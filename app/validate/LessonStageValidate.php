<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-08-26 10:30:05
 * Desc: 课程集数验证
 */

namespace app\validate;

class LessonStageValidate extends BaseValidate
{
    protected $rule = [
        'lesson_id'    => 'require',
        'title'  => 'require',  
    ];

    protected $message = [
        'lesson_id' => '课程ID必须',
        'title'    => '名称必须', 
    ];

    protected $scene  = [
        'scene_add'   =>  ['cid','title'],
        'scene_edit'  =>  ['cid','title'],
    ];

}
