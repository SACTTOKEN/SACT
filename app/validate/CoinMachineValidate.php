<?php
namespace app\validate;

class CoinMachineValidate extends BaseValidate
{
    protected $rule = [
        'm_title' => 'require',
        'm_money' => 'require|isMoney',   
        'z_money' => 'require|isMoney',   
        'purchase_limit' => 'number',
    ];

    protected $message = [
        'm_title'    => '名称必须',
        'm_money.require'    => '金额必须', 
        'z_money.require'    => '总收益金额必须', 
        'm_money.isMoney'    => '金额格式不对', 
        'z_money.isMoney'    => '总收益金额格式不对', 
        'purchase_limit'    => '填写限购数量',
    ];

    protected $scene  = [
        'scene_saveadd'   =>  ['m_title','m_money','z_money','purchase_limit'],
        'scene_saveedit'  =>  ['m_title','m_money','z_money','purchase_limit'],
    ];
}
