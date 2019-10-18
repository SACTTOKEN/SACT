<?php
/**
 * Created by yaaaaa_god
 * User: yaaaaa
 * Date: 2018/12/13
 * Desc: 验证类ID验证
 */
namespace app\validate;

class pluginImValidate extends BaseValidate
{
    protected $rule = [
        'level' => 'require|in:1,2,3',
    ];

    protected $message = [
        'level'     => '请选择级别',
    ];

    protected $scene  = [
        'zhituiren'  =>  ['level'],
    ];
}
