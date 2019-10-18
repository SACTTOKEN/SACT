<?php
/**
 * Created by yaaaaa_god
 * User: yaaaaa
 * Date: 2018/12/13
 * Desc: 验证类ID验证
 */
namespace app\validate;

class IDMustBeRequire1 extends BaseValidate
{
    protected $rule = [
        'id' => 'require|isPositiveInteger',
		'xzlx' => 'require|isPositiveInteger',
    ];

    protected $message = [
        'id.require'      => 'ID不能为空',
        'id.isPositiveInteger'    => 'ID必须是正整数1',
		'xzlx.require'      => '请选择入单使用资金类型',
        'xzlx.isPositiveInteger'    => '请正确选择入单使用资金类型',
    ];
}
