<?php
namespace app\validate;

class PluginguesslordValidate extends BaseValidate
{
    protected $rule = [
        'stage'       => 'require|number|unique:plugin_guess_lord',
        'buy_type'    => 'require',
        'buy_up'    => 'isMoney',
        'buy_down'    => 'isMoney',
    
 	];

    protected $message = [
        'stage.require'    => '期数必填',
        'stage.number'     => '期数为数字',
        'stage.unique'    => '该期数已存在',
        'buy_type.require'    => '请选择涨跌',
        'buy_up.isMoney'    => '金额必须是正整数',
        'buy_down.isMoney'    => '金额必须是正整数',
    ];


    protected $scene  = [
        'scene_add'   =>  ['stage'],
        'scene_buy'   =>  ['stage'=>'require','buy_type','buy_up','buy_down'],
    ];



}
