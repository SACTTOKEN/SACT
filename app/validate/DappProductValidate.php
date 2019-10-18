<?php
namespace app\validate;

class DappProductValidate extends BaseValidate
{
    protected $rule = [
        'title'     => 'require',
        'price'  => 'require|isMoney',
        'income' => 'require|isMoney',    
        'day' => 'require|isPositiveInteger',    
    ];

    protected $message = [
        'title'    =>   '等级名称不能为空', 
        'price'  =>  '请输入累计投入金额',
        'income'    => '请输入收益',
        'day'    => '请输入天数',
    ];

    protected $scene  = [
        'saveadd'  =>  ['title','day','price','income'],
        'saveedit' =>  ['title','day','price','income'],
    ];

}
