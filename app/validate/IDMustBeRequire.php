<?php
/**
 * Created by yaaaaa_god
 * User: yaaaaa
 * Date: 2018/12/13
 * Desc: 验证类ID验证
 */
namespace app\validate;

class IDMustBeRequire extends BaseValidate
{
    protected $rule = [
        'id' => 'require|isPositiveInteger',
    ];

    protected $message = [
        'id.require'      => 'ID不能为空',
        'id.isPositiveInteger'    => 'ID必须是正整数1',
    ];
}
