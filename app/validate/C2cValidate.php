<?php
namespace app\validate;

class C2cValidate extends BaseValidate
{
    protected $rule = [
        'manner'       => 'require',
        'money'       => 'require|isPositiveInteger',
        'piclink'       => 'require',
        'state_detail'       => 'require',
        'password'       => 'require',
        'content'       => 'require',
        'state'       => 'require|in:0,1,2,3,4',
    ];


    protected $message = [
        'manner'     => '交易方式不能为空',
        'money.require'     => '数量不能为空',
        'money.isPositiveInteger'     => '数量必须是正整数',
        'piclink'     => '请上传交易图片',
        'state_detail'     => '请选择申述原因',
        'password'     => '请输入密码',
        'state'     => '请选择申述状态',
        'content'     => '请提交留言',
    ];

    protected $scene  = [
        'buy'  =>  ['manner','money'],
        'payment' => ['piclink','password'],
        'state' => ['state_detail'],
        'confirm' => ['password'],
        'saveedit'=>['state'],
        'content'=>['content'],
    ];

}
