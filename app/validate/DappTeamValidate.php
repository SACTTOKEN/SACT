<?php
namespace app\validate;

class DappTeamValidate extends BaseValidate
{
    protected $rule = [
        'rid'  => 'require|isPositiveInteger',
        'team_level' => 'require|integer',    
        'team_award' => 'require|float',    
    ];

    protected $message = [
        'rid'    =>   '请选择团队奖等级',
        'team_level'    => '团队奖层级正整数',
        'team_award'    => '团队奖比例正数',
    ];

    protected $scene  = [
        'saveadd'  =>  ['rid','team_level','team_award'],
        'saveedit' =>  ['rid','team_level','team_award'],
    ];

}
