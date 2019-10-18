<?php
namespace app\validate;

class PageValidate extends BaseValidate
{
    protected $rule = [
        'page'                  => 'isPositiveInteger',
        'page_size'              => 'isPositiveInteger',  
    ];
    
    protected $message = [
        'page'      => '页数必须是正整数',
        'page_size'    => '每页条数必须是正整数',
    ];
}
