<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-29 13:36:27
 * Desc: 自提验证
 */

namespace app\validate;

class ExcelBankValidate extends BaseValidate
{
    protected $rule = [
        'bank_name'      => 'require',
        'begin_num'       => 'require',

    ];

    protected $message = [
        'bank_name.require'    => '亲，请填写银行卡名',
        'begin_num.require'         => '亲，请填写银行卡前四位',  
    ];

    protected $scene  = [          
        'scene_add'    =>  ['bank_name','begin_num'],   
    ];

    
}
