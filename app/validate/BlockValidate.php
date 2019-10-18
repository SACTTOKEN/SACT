<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-02-25 11:50:38
 * Desc: 供应商验证
 */


namespace app\validate;

class BlockValidate extends BaseValidate
{
    protected $rule = [
        'publickey'    => 'require',
        
    ];

    protected $message = [
        'publickey'    => '签名不能为空',
    ];


    protected $scene  = [
        'is_success'  =>  ['publickey'],
    ];


}
