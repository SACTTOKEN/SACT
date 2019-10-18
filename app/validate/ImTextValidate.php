<?php
namespace app\validate;

class ImTextValidate extends BaseValidate
{
    protected $rule = [
        'keyword' => 'require',
        'content' => 'require',
        'is_like' => 'require|in:0,1',
    ];

    protected $message = [
        'keyword.require'    => '请填写关键词',
        'content'    => '请填写回复内容', 
        'is_like'    => '请选择是否模糊查找', 
    ];

    protected $scene  = [
        'scene_saveedit'   =>  ['keyword','content','is_like'],
    ];
}
