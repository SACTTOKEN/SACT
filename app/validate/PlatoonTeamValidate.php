<?php
/**
 * Created by yaaaaa_god
 * User: yaaaaa
 * Date: 2018/12/13
 */
namespace app\validate;

class PlatoonTeamValidate extends BaseValidate
{
    protected $rule = [
        'reward' => 'require|float',
        'fee'  => 'require|float',
    ];

    protected $message = [
        'reward'     => '请输入奖励金额',
        'fee' => '请输入手续费',
    ];

}
