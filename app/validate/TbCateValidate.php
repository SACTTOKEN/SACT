<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-04 14:19:55
 * Desc: 提现验证
 */

namespace app\validate;

class TbCateValidate extends BaseValidate
{
    protected $rule = [
        'f_id' => 'require',
    ];

    protected $message = [
        'f_id'    => '类别ID必须',    
    ];
    

    protected $scene  = [
        'scene_find'  =>  ['id','status'],
    ];


}
