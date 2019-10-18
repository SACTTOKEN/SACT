<?php
namespace app\validate;

class MailCourierValidate extends BaseValidate
{
    protected $rule = [
        'id'       => 'require',
        'title'    => 'require',
    ];

    protected $message = [
         'id'      => 'ID必须',
        'title'    => '题标必须',
    ];


    protected $scene  = [
        'scene_find'  =>  ['id'],
        'scene_del'   =>  ['id'],
        'scene_add'   =>  ['title'],
        'scene_edit'  =>  ['id'],
        'scene_list'  =>  ['mid'],
    ];

}
