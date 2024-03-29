<?php
namespace app\validate;

class CoinTeamValidate extends BaseValidate
{
    protected $rule = [
        'zt_num' => 'require|number|unique:coin_team',
        'team_award' => 'require',
    ];

    protected $message = [
        'zt_num.require'   => '直推人数必须',
        'zt_num.number'    => '直推人数必须是数字',
        'zt_num.unique'    => '直推人数数值已存在',
        'team_award.require'    => '奖励金额千分比必须', 
    ];

    protected $scene  = [
        'scene_saveadd'   =>  ['zt_num','team_award'],
        'scene_saveedit'  =>  ['zt_num'=>'require|number','team_award'],
    ];
}
