<?php
namespace app\validate;

class XqgdjgValidate extends BaseValidate
{
    protected $rule = [
        'cdate' => 'number',
        'jcjg' => 'number',
    ];

    protected $message = [
        'cdate'   => '请选择攻打日期',
        'jcjg'    => '请选择攻打结果',
    ];

    protected $scene  = [
        'scene_saveadd'   =>  ['cdate','jcjg'],
        'scene_saveedit'  =>  ['cdate','jcjg'],
    ];
}
