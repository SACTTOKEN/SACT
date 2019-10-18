<?php
namespace app\validate;

class DappRatingValidate extends BaseValidate
{
    protected $rule = [
        'title'     => 'require',
        'money'  => 'isMoney',
    ];

    protected $message = [
        'title'    =>   '等级名称不能为空', 
        'money'  =>  '请输入累计投入金额',
    ];

    protected $scene  = [
        'saveadd'  =>  ['money'],
        'saveedit' =>  ['title','money'],
    ];

}
