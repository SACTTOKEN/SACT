<?php

namespace app\validate;

class PluginguessSlaveValidate extends BaseValidate
{
    protected $rule = [
        'stage'       => 'require|unique:plugin_guess_lord',
    ];

    protected $message = [
        'stage.require'    => '期数必须',
        'stage.unique'    => '该期已存在',
    ];

    protected $scene  = [
        'scene_add'   =>  ['stage'],
    ];


}
