<?php
namespace app\validate;

class UserdtsfValidate extends BaseValidate
{
    protected $rule = [
        'zt_num' => 'require|number|unique:user_dtsf',
        'dtsf_usdt' => 'require|number',
		'dtsf_ptb' => 'require|number',
    ];

    protected $message = [
        'zt_num.require'   => '星期几必须选择',
        'zt_num.number'    => '直推人数必须是数字',
        'zt_num.unique'    => '该天数值已存在',
        'team_award.require'    => '奖励金额千分比必须', 
    ];

    protected $scene  = [
        'scene_saveadd'   =>  ['zt_num','dtsf_usdt','dtsf_ptb'],
        'scene_saveedit'  =>  ['zt_num'=>'require|number','dtsf_usdt','dtsf_ptb'],
    ];
}
