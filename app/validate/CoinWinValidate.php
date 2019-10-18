<?php
namespace app\validate;

class CoinWinValidate extends BaseValidate
{
    protected $rule = [
        'stage_type'     => 'require',
        'stake'  => 'require|isMoney', 
    ];

    protected $message = [
        'stage_type.require'  =>  '请提交期类型', 
        'stake.require'  =>  '请提交投入值',    
        'stake.isMoney'  =>  '投入值必须是正整数',
    ];

    protected $scene  = [
        'scene_add'  =>  ['stage_type','stake'], 
    ];

}
