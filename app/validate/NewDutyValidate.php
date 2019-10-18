<?php
namespace app\validate;

class NewDutyValidate extends BaseValidate
{
    protected $rule = [  
        'iden' 	   => 'require',
       
    ];

    protected $message = [
        'iden'   => '标识必须',
     
    ];


	protected $scene  = [
        'scene_paid'  =>  ['iden'],
        
       
    ];

   

}
