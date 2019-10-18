<?php
namespace app\validate;

class BigWheelValidate extends BaseValidate
{
    protected $rule = [
     	'con'=>'require',
    ];

    protected $message = [
    	'con.require' => '请提交活动场景',
        
        
    ];

    protected $scene  = [
    	'scene_ask' => ['con'],
      
    ];

}
